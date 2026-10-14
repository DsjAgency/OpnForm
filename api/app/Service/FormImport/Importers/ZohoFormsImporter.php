<?php

namespace App\Service\FormImport\Importers;

use App\Service\FormImport\FormImportException;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Imports a public Zoho Forms form from its permalink
 * (…/{portal}/form/{FormLinkName}/formperma/{key}).
 *
 * Structure comes from the rendered HTML; conditional rules come from the
 * public JSON endpoint the live form itself loads ({permalink}/liverules).
 * Subforms (repeatable groups) have no Forms4U equivalent, so they become a
 * "how many?" question followed by a configurable number of fixed copies.
 */
class ZohoFormsImporter extends AbstractImporter
{
    private const PATH_PATTERN = '#^(/[^/]+/form/[^/]+/formperma/[A-Za-z0-9_-]+)#';

    private const SUBFORM_COUNT_QUESTION = 'Quantos você vai informar?';

    private const FIELD_TYPES = [
        '1' => 'text',
        '2' => 'text',
        '3' => 'number',
        '4' => 'number',
        '5' => 'date',
        '9' => 'email',
        '11' => 'phone_number',
        '12' => 'select',
        '13' => 'select',
        '14' => 'multi_select',
        '18' => 'checkbox',
        '19' => 'files',
    ];

    private const OPERATORS = [
        'EQUALS' => 'equals',
        'NOT_EQUALS' => 'does_not_equal',
        'GREATER_THAN' => 'greater_than',
        'LESS_THAN' => 'less_than',
        'GREATER_THAN_OR_EQUALS' => 'greater_than_or_equal_to',
        'LESS_THAN_OR_EQUALS' => 'less_than_or_equal_to',
        'CONTAINS' => 'contains',
        'NOT_CONTAINS' => 'does_not_contain',
        'STARTS_WITH' => 'starts_with',
        'ENDS_WITH' => 'ends_with',
        'IS_EMPTY' => 'is_empty',
        'IS_NOT_EMPTY' => 'is_not_empty',
    ];

    /** @var array<string, array> Zoho link name => imported property (by reference via index) */
    private array $linkNameIndex = [];

    /** @var array<int, array> */
    private array $properties = [];

    /** @var array<string, array> Zoho subform link name => ['count_index' => int, 'copies' => [[index...]]] */
    private array $subforms = [];

    private array $warnings = [];

    public function allowedDomains(): array
    {
        $zohoDomains = [
            '*.zohopublic.com', '*.zohopublic.eu', '*.zohopublic.in', '*.zohopublic.com.au',
            '*.zohopublic.jp', '*.zohopublic.ca', '*.zohopublic.sa', '*.zohopublic.com.cn',
            'forms.zoho.com', 'forms.zoho.eu', 'forms.zoho.in', 'forms.zoho.com.au',
            'forms.zoho.jp', 'forms.zohocloud.ca', 'forms.zoho.sa', 'forms.zoho.com.cn',
        ];

        // Custom domains configured in Zoho (e.g. forms.cidadania4u.com.br)
        $custom = array_filter(array_map(
            fn ($domain) => strtolower(trim($domain)),
            explode(',', (string) config('opnform.zoho_import.custom_domains', ''))
        ));

        return array_values(array_unique([...$zohoDomains, ...$custom]));
    }

    public function validate(array $importData): bool
    {
        if (! parent::validate($importData)) {
            return false;
        }

        return $this->permalinkPath($importData['url']) !== null;
    }

    public function import(array $importData): array
    {
        $url = $importData['url'];
        $path = $this->permalinkPath($url);
        if (! $path) {
            throw new FormImportException('Use o link público do formulário Zoho (…/form/NomeDoFormulario/formperma/…).');
        }

        $parts = parse_url($url);
        $permalink = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '') . $path;

        $html = $this->fetchHtml($permalink);
        $rules = $this->fetchRules($permalink . '/liverules');

        return $this->parse($html, $rules);
    }

    /**
     * Parse already-fetched HTML and rules JSON (also used by tests).
     */
    public function parse(string $html, ?array $rules): array
    {
        $this->linkNameIndex = [];
        $this->properties = [];
        $this->subforms = [];
        $this->warnings = [];

        $xpath = $this->loadHtml($html);

        $title = $this->sanitizeText($this->textOf($xpath->query("//h2[contains(@class,'frmTitle')]")->item(0)), 255);
        if ($title === '') {
            throw new FormImportException('Não encontramos um formulário Zoho nesta página. Confira se o link é público.');
        }

        $description = $this->richText($xpath->query("//p[contains(@class,'frmDesc')]")->item(0));
        $this->addProperty($this->textBlock(
            'Título',
            '<h1>' . e($title) . '</h1>' . ($description !== '' ? '<p>' . $description . '</p>' : '')
        ));

        $pageTitles = [];
        foreach ($xpath->query("//tr[contains(@class,'pTitle')]/td") as $td) {
            $pageTitles[] = $this->sanitizeText($this->textOf($td), 255);
        }

        // Zoho's markup nests fields unevenly, so walk them in document order and
        // use each field's page_no to place the page breaks.
        $fields = $xpath->query("//li[@elname='livefield-elem'][not(ancestor::li[@elemtype='subFormField'])]");
        if ($fields->length === 0) {
            throw new FormImportException('Não encontramos os campos deste formulário Zoho.');
        }

        $currentPage = null;
        foreach ($fields as $li) {
            // Cells of a subform row (SingleLine38_0, Date7_1…) belong to the subform
            if (preg_match('/_\d+$/', $li->getAttribute('compname'))) {
                continue;
            }

            $page = max(1, (int) ($li->getAttribute('page_no') ?: 1));
            if ($currentPage !== null && $page > $currentPage) {
                for ($p = $currentPage + 1; $p <= $page; $p++) {
                    $this->addProperty([
                        'id' => $this->generateFieldId(),
                        'name' => $pageTitles[$p - 1] ?? 'Página ' . $p,
                        'type' => 'nf-page-break',
                    ]);
                }
            }
            $currentPage = max($currentPage ?? $page, $page);

            $this->mapElement($xpath, $li);
        }

        if ($rules) {
            $this->applyRules($rules);
        }

        $this->applySubformCopyLogic();
        $this->disambiguateNames();

        if ($this->warnings !== []) {
            Log::info('Zoho Forms import warnings', ['title' => $title, 'warnings' => $this->warnings]);
        }

        return [
            'title' => $title,
            'properties' => array_values($this->properties),
            'presentation_style' => 'classic',
        ];
    }

    public function warnings(): array
    {
        return $this->warnings;
    }

    // ---------------------------------------------------------------------
    // Fetching
    // ---------------------------------------------------------------------

    private function permalinkPath(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';

        return preg_match(self::PATH_PATTERN, $path, $matches) ? $matches[1] : null;
    }

    private function fetchRules(string $url): ?array
    {
        try {
            $response = Http::timeout(15)
                ->maxRedirects(3)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; OpnFormImporter/1.0)',
                    'Accept' => 'application/json',
                ])
                ->get($url);
        } catch (\Throwable $e) {
            Log::warning('Zoho Forms rules could not be fetched', ['error' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return is_array($data) ? ($data['form'] ?? null) : null;
    }

    // ---------------------------------------------------------------------
    // Structure
    // ---------------------------------------------------------------------

    private function mapElement(DOMXPath $xpath, DOMElement $li): void
    {
        $compType = $li->getAttribute('comptype');
        $linkName = $li->getAttribute('compname');
        $class = ' ' . $li->getAttribute('class') . ' ';

        if ($li->getAttribute('elemtype') === 'subFormField') {
            $this->mapSubform($xpath, $li, $linkName);

            return;
        }

        if ($compType === '0') {
            $block = str_contains($class, ' section ')
                ? $this->sectionBlock($xpath, $li, $linkName)
                : $this->noteBlock($xpath, $li, $linkName);
            if ($block) {
                $this->addProperty($block, $linkName);
            }

            return;
        }

        $property = $this->fieldProperty($xpath, $li, $compType, $linkName);
        if ($property) {
            $this->addProperty($property, $linkName);
        }
    }

    private function fieldProperty(DOMXPath $xpath, DOMElement $li, string $compType, string $linkName): ?array
    {
        $type = self::FIELD_TYPES[$compType] ?? null;

        if ($type === 'checkbox') {
            $name = $this->textOf($xpath->query(".//*[@elname='display_name']", $li)->item(0));
        } else {
            $name = $this->textOf($xpath->query(".//label[contains(@class,'labelName')]/span", $li)->item(0));
        }
        $name = $this->cleanLabel($name);

        if ($type === null) {
            if ($name === '') {
                return null;
            }
            // Unknown Zoho field (name, address, signature…): keep it as text
            $this->warnings[] = "Campo \"{$name}\" (tipo Zoho {$compType}) importado como texto.";
            $type = 'text';
        }

        $property = [
            'id' => $this->generateFieldId(),
            'name' => $name !== '' ? $name : 'Campo sem título',
            'type' => $type,
            'required' => $li->getAttribute('mandatory') === 'true',
            'hidden' => false,
        ];

        $help = $this->richText($xpath->query(".//p[@elname='hint']", $li)->item(0));
        if ($help !== '') {
            $property['help'] = $help;
        }

        $placeholder = $xpath->query(".//input[@placeholder!=''] | .//textarea[@placeholder!='']", $li)->item(0);
        if ($placeholder instanceof DOMElement) {
            $property['placeholder'] = $this->sanitizeText($placeholder->getAttribute('placeholder'), 255);
        }

        if ($compType === '2') {
            $property['multi_lines'] = true;
        }

        if ($type === 'date') {
            $property['date_format'] = 'dd/MM/yyyy';
        }

        if (in_array($type, ['select', 'multi_select'], true)) {
            $options = $this->optionsOf($xpath, $li);
            $property[$type] = ['options' => array_map(
                fn ($label) => ['id' => $this->generateFieldId(), 'name' => $label],
                $options
            )];
            if ($compType !== '12') {
                $property['without_dropdown'] = true;
            }
        }

        return $property;
    }

    private function optionsOf(DOMXPath $xpath, DOMElement $li): array
    {
        $options = [];
        foreach ($xpath->query(".//select/option", $li) as $option) {
            $value = trim($option->getAttribute('value'));
            if ($value !== '' && $value !== '-Select-') {
                $options[] = $this->sanitizeText($value, 255);
            }
        }
        foreach ($xpath->query(".//input[@type='radio' or @type='checkbox']", $li) as $input) {
            $value = trim($input->getAttribute('value'));
            if ($value !== '' && $value !== 'on') {
                $options[] = $this->sanitizeText($value, 255);
            }
        }

        return array_values(array_unique($options));
    }

    private function sectionBlock(DOMXPath $xpath, DOMElement $li, string $linkName): ?array
    {
        $heading = $this->cleanLabel($this->textOf($xpath->query('.//h2', $li)->item(0)));
        $text = $this->richText($xpath->query('.//p', $li)->item(0));

        if ($heading === '' && $text === '') {
            return null;
        }

        $content = ($heading !== '' ? '<h2>' . e($heading) . '</h2>' : '') . ($text !== '' ? '<p>' . $text . '</p>' : '');

        return $this->textBlock($heading !== '' ? $heading : 'Seção', $content);
    }

    private function noteBlock(DOMXPath $xpath, DOMElement $li, string $linkName): ?array
    {
        $content = $this->richText($xpath->query(".//label[contains(@class,'descFld')]", $li)->item(0), true);
        if ($content === '') {
            return null;
        }

        return $this->textBlock('Texto', $content);
    }

    private function mapSubform(DOMXPath $xpath, DOMElement $li, string $linkName): void
    {
        $label = $this->cleanLabel($this->textOf($xpath->query(".//label[contains(@class,'sflabelName')]/span", $li)->item(0)));
        if ($label === '') {
            $label = 'Itens';
        }

        $columns = [];
        foreach ($xpath->query(".//li[starts-with(@compname,'label_')]", $li) as $column) {
            $compType = $column->getAttribute('comptype');
            $columns[] = [
                'name' => $this->cleanLabel($this->textOf($xpath->query(".//label[contains(@class,'labelName')]/span", $column)->item(0))),
                'type' => self::FIELD_TYPES[$compType] ?? 'text',
                'multi_lines' => $compType === '2',
                'required' => $column->getAttribute('mandatory') === 'true',
            ];
        }

        $maxEntries = (int) $li->getAttribute('max-entry');
        $configured = max(1, (int) config('opnform.zoho_import.subform_copies', 3));
        $copies = $maxEntries > 0 ? min($configured, $maxEntries) : $configured;
        $minEntries = (int) $li->getAttribute('min-entry');

        $this->addProperty($this->textBlock($label, '<h2>' . e($label) . '</h2>'), $linkName . '__heading');

        $countOptions = array_map(fn ($n) => ['id' => $this->generateFieldId(), 'name' => (string) $n], range(1, $copies));
        $countIndex = $this->addProperty([
            'id' => $this->generateFieldId(),
            'name' => count($this->subforms) > 0
                ? self::SUBFORM_COUNT_QUESTION . ' (' . $label . ')'
                : self::SUBFORM_COUNT_QUESTION,
            'type' => 'select',
            'select' => ['options' => $countOptions],
            'without_dropdown' => true,
            'required' => $minEntries > 0 || in_array(true, array_column($columns, 'required'), true),
            'hidden' => false,
        ], $linkName);

        $copyIndexes = [];
        for ($n = 1; $n <= $copies; $n++) {
            $indexes = [$this->addProperty($this->textBlock("{$label} {$n}", '<h3>' . e("{$label} {$n}") . '</h3>'))];
            foreach ($columns as $column) {
                $property = [
                    'id' => $this->generateFieldId(),
                    'name' => ($column['name'] !== '' ? $column['name'] : 'Campo') . " ({$n})",
                    'type' => $column['type'],
                    'required' => $column['required'],
                    'hidden' => false,
                ];
                if ($column['multi_lines']) {
                    $property['multi_lines'] = true;
                }
                if ($column['type'] === 'date') {
                    $property['date_format'] = 'dd/MM/yyyy';
                }
                $indexes[] = $this->addProperty($property);
            }
            $copyIndexes[$n] = $indexes;
        }

        $this->subforms[$linkName] = [
            'heading' => $linkName . '__heading',
            'count_index' => $countIndex,
            'copies' => $copyIndexes,
            'show_conditions' => null,
        ];
    }

    // ---------------------------------------------------------------------
    // Conditional logic
    // ---------------------------------------------------------------------

    private function applyRules(array $rules): void
    {
        $show = [];
        $hide = [];

        foreach ($rules['field_rules'] ?? [] as $rule) {
            if (! ($rule['rule_status'] ?? true) || ! empty($rule['issubform_rule'])) {
                continue;
            }

            $conditions = $this->convertGroup($rule['conditions'] ?? [], ($rule['logical_oper'] ?? 'matchall') === 'matchany' ? 'or' : 'and');
            if ($conditions === null) {
                $this->warnings[] = 'Regra "' . ($rule['rule_disp_name'] ?? '?') . '" ignorada: condição sem equivalente.';
                continue;
            }

            foreach ($rule['actions'] ?? [] as $action) {
                $target = $action['field_link_name'] ?? null;
                $kind = $action['action'] ?? null;
                if (! $target || ! in_array($kind, ['show', 'hide'], true)) {
                    if ($kind) {
                        $this->warnings[] = "Ação \"{$kind}\" da regra \"" . ($rule['rule_disp_name'] ?? '?') . '" não foi importada.';
                    }
                    continue;
                }
                if ($kind === 'show') {
                    $show[$target][] = $conditions;
                } else {
                    $hide[$target][] = $conditions;
                }
            }
        }

        foreach ($show as $target => $list) {
            if (isset($hide[$target])) {
                $this->warnings[] = "Campo {$target} tinha regras de mostrar e ocultar; só \"mostrar\" foi importada.";
                unset($hide[$target]);
            }

            $conditions = $this->combine('or', $list);

            if (isset($this->subforms[$target])) {
                $this->subforms[$target]['show_conditions'] = $conditions;
                continue;
            }

            foreach ($this->indexesFor($target) as $index) {
                $this->setShowLogic($index, $conditions);
            }
        }

        foreach ($hide as $target => $list) {
            $conditions = $this->combine('or', $list);
            foreach ($this->indexesFor($target) as $index) {
                $this->properties[$index]['logic'] = [
                    'conditions' => $conditions,
                    'actions' => ['hide-block'],
                ];
            }
        }
    }

    /**
     * Each copy N is shown when the count answer is >= N (and the subform
     * itself would be shown in Zoho, if it had a rule).
     */
    private function applySubformCopyLogic(): void
    {
        foreach ($this->subforms as $subform) {
            $countProperty = $this->properties[$subform['count_index']];
            $parentConditions = $subform['show_conditions'];

            if ($parentConditions) {
                $this->setShowLogic($subform['count_index'], $parentConditions);
                foreach ($this->indexesFor($subform['heading']) as $index) {
                    $this->setShowLogic($index, $parentConditions);
                }
            }

            $copies = count($subform['copies']);
            foreach ($subform['copies'] as $n => $indexes) {
                $countConditions = $this->combine('or', array_map(
                    fn ($value) => $this->leaf($countProperty, 'equals', (string) $value),
                    range($n, $copies)
                ));
                $conditions = $parentConditions
                    ? $this->combine('and', [$parentConditions, $countConditions])
                    : $countConditions;

                foreach ($indexes as $index) {
                    $this->setShowLogic($index, $conditions);
                }
            }
        }
    }

    /**
     * Hidden by default and revealed by the conditions. Forms4U only accepts
     * show-block on hidden blocks, so "required" becomes require-answer.
     */
    private function setShowLogic(int $index, array $conditions): void
    {
        $property = &$this->properties[$index];
        $actions = ['show-block'];

        if (! str_starts_with($property['type'], 'nf-') && ! empty($property['required'])) {
            $actions[] = 'require-answer';
        }

        $property['hidden'] = true;
        $property['required'] = false;
        $property['logic'] = [
            'conditions' => $conditions,
            'actions' => $actions,
        ];
    }

    /**
     * Zoho forms often repeat labels (one "Nome completo" per child). Add the
     * section heading, or a counter, so responses stay readable.
     */
    private function disambiguateNames(): void
    {
        $counts = array_count_values(array_map(
            fn ($p) => $p['name'],
            array_filter($this->properties, fn ($p) => ! str_starts_with($p['type'], 'nf-'))
        ));

        $heading = null;
        $used = [];
        foreach ($this->properties as $index => $property) {
            if ($property['type'] === 'nf-text' && ! str_contains($property['content'] ?? '', '<p>')) {
                $heading = $property['name'];
                continue;
            }
            if ($property['type'] === 'nf-page-break') {
                $heading = null;
                continue;
            }
            if (str_starts_with($property['type'], 'nf-') || ($counts[$property['name']] ?? 0) < 2) {
                continue;
            }

            $name = $property['name'];
            // The first occurrence keeps its original label
            if (! isset($used[$name])) {
                $used[$name] = true;
                continue;
            }
            if ($heading && ! isset($used[$name . ' — ' . $heading])) {
                $name .= ' — ' . $heading;
            }
            $candidate = $name;
            for ($n = 2; isset($used[$candidate]); $n++) {
                $candidate = $name . " ({$n})";
            }
            $used[$candidate] = true;
            $this->properties[$index]['name'] = mb_substr($candidate, 0, 255);
        }
    }

    private function convertGroup(array $conditions, string $operator): ?array
    {
        $children = [];
        foreach ($conditions as $condition) {
            if (isset($condition['conditions'])) {
                $child = $this->convertGroup($condition['conditions'], ($condition['logical_oper'] ?? 'and') === 'or' ? 'or' : 'and');
            } else {
                $child = $this->convertCondition($condition);
            }
            if ($child === null) {
                return null;
            }
            $children[] = $child;
        }

        return $children === [] ? null : $this->combine($operator, $children);
    }

    private function convertCondition(array $condition): ?array
    {
        $indexes = $this->indexesFor($condition['field_link_name'] ?? '');
        if ($indexes === []) {
            return null;
        }

        $property = $this->properties[$indexes[0]];
        $zohoOperator = $condition['comparison_oper'] ?? '';
        $value = $condition['value'] ?? null;

        switch ($property['type']) {
            case 'checkbox':
                $checked = ! in_array(strtolower((string) $value), ['false', '0', 'no', 'não', ''], true);
                if ($zohoOperator === 'NOT_EQUALS') {
                    $checked = ! $checked;
                }

                return $this->leaf($property, $checked ? 'is_checked' : 'is_not_checked', null);
            case 'multi_select':
                $operator = match ($zohoOperator) {
                    'EQUALS', 'CONTAINS' => 'contains',
                    'NOT_EQUALS', 'NOT_CONTAINS' => 'does_not_contain',
                    default => self::OPERATORS[$zohoOperator] ?? null,
                };
                break;
            default:
                $operator = self::OPERATORS[$zohoOperator] ?? null;
        }

        if ($operator === null) {
            return null;
        }

        if (in_array($operator, ['is_empty', 'is_not_empty'], true)) {
            return $this->leaf($property, $operator, true);
        }

        if ($property['type'] === 'number') {
            if (! is_numeric($value)) {
                return null;
            }
            $value = $value + 0;
        }

        return $this->leaf($property, $operator, $value);
    }

    private function leaf(array $property, string $operator, mixed $value): array
    {
        $condition = [
            'operator' => $operator,
            'property_meta' => ['id' => $property['id'], 'type' => $property['type']],
        ];
        if ($value !== null) {
            $condition['value'] = $value;
        }

        return [
            'identifier' => $property['id'],
            'value' => $condition,
        ];
    }

    private function combine(string $operator, array $children): array
    {
        if (count($children) === 1) {
            return $children[0];
        }

        return [
            'operatorIdentifier' => $operator,
            'children' => array_values($children),
        ];
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function addProperty(array $property, ?string $linkName = null): int
    {
        $this->properties[] = $property;
        $index = array_key_last($this->properties);
        if ($linkName) {
            $this->linkNameIndex[$linkName][] = $index;
        }

        return $index;
    }

    private function indexesFor(string $linkName): array
    {
        return $this->linkNameIndex[$linkName] ?? [];
    }

    private function textBlock(string $name, string $content): array
    {
        return [
            'id' => $this->generateFieldId(),
            'name' => $this->sanitizeText($name, 255) ?: 'Texto',
            'type' => 'nf-text',
            'content' => $content,
            'hidden' => false,
        ];
    }

    private function loadHtml(string $html): DOMXPath
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    private function textOf(?\DOMNode $node): string
    {
        if (! $node) {
            return '';
        }

        return trim(preg_replace('/\s+/u', ' ', html_entity_decode($node->textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function cleanLabel(string $label): string
    {
        return $this->sanitizeText(rtrim(trim($label), " :\u{00A0}"), 255);
    }

    /**
     * Keep simple formatting (paragraphs, bold, lists, links) and drop Zoho's
     * inline styles.
     */
    private function richText(?\DOMNode $node, bool $allowBlocks = false): string
    {
        if (! $node) {
            return '';
        }

        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }

        $allowed = $allowBlocks
            ? '<p><br><b><strong><i><em><u><ul><ol><li><a><div><h2><h3>'
            : '<br><b><strong><i><em><u><a>';
        $html = strip_tags($html, $allowed);
        // Drop every attribute except href on links
        $html = preg_replace_callback('/<(\w+)\b[^>]*>/u', function ($match) {
            $tag = strtolower($match[1]);
            if ($tag === 'a' && preg_match('/href="(https?:[^"]+)"/i', $match[0], $href)) {
                return '<a href="' . e($href[1]) . '" target="_blank" rel="noopener">';
            }

            return '<' . $tag . '>';
        }, $html);
        $html = preg_replace(['#<div>#i', '#</div>#i'], ['<p>', '</p>'], $html);
        $html = preg_replace('#<p>(\s|&nbsp;|<br>)*</p>#iu', '', $html);
        $html = trim(preg_replace('/\s+/u', ' ', $html));

        return mb_substr($html, 0, 8000);
    }
}
