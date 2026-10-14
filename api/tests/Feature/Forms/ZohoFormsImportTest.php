<?php

use App\Service\FormImport\Importers\ZohoFormsImporter;
use Illuminate\Support\Facades\Http;

const ZOHO_TEST_PERMALINK = 'https://forms.zohopublic.com/acme/form/Cadastro/formperma/AbC123';

function zohoImporter(): ZohoFormsImporter
{
    return app(ZohoFormsImporter::class);
}

function findProperty(array $properties, string $name): array
{
    foreach ($properties as $property) {
        if ($property['name'] === $name) {
            return $property;
        }
    }

    throw new RuntimeException("Property {$name} not found");
}

function zohoHtmlFixture(): string
{
    return <<<'HTML'
<html><body>
<ul><li><h2 class="frmTitle" role="heading"><em>Cadastro de Cliente</em></h2><p class="frmDesc"></p></li></ul>
<table><tr class="pTitle"><td>Dados</td><td>Documentos</td></tr></table>
<div><ul elname="formBodyULName" page_no="1">
  <li id="Section1-li" class="section" elname="livefield-elem" compname="Section1" comptype="0" page_no="1"><h2>Dados Pessoais:</h2><p></p></li>
  <li class="tempFrmWrapper large" elname="livefield-elem" comptype="1" compname="SingleLine" mandatory="true" page_no="1">
    <label class="labelName"><span>Nome Completo:</span></label>
    <input type="text" name="SingleLine" placeholder="Seu nome" />
    <p class="instruction" elname="hint" id="hint-SingleLine">Como no <b style="color:red">documento</b>.</p>
  </li>
  <li class="zfradio tempFrmWrapper" elname="livefield-elem" comptype="13" compname="Radio" mandatory="true" page_no="1">
    <label class="labelName"><span>Tem filhos?</span></label>
    <input type="radio" name="Radio" value="Sim" /><input type="radio" name="Radio" value="Não" />
  </li>
  <li class="tempFrmWrapper" elname="livefield-elem" comptype="3" compname="Number1" mandatory="true" page_no="1">
    <label class="labelName"><span>Quantos filhos?</span></label><input type="text" name="Number1" />
  </li>
  <li class="tempFrmWrapper" elname="livefield-elem" comptype="1" compname="SingleLine2" mandatory="true" page_no="1">
    <label class="labelName"><span>Nome do filho</span></label><input type="text" name="SingleLine2" />
  </li>
  <li id="SubForm-li" compname="SubForm" elemtype="subFormField" elname="livefield-elem" max-entry="5" min-entry="0" page_no="1">
    <label class="labelName sflabelName"><span>Dependentes:</span></label>
    <ul>
      <li comptype="1" compname="label_SingleLine38" mandatory="true"><label class="labelName"><span>Nome completo:</span></label></li>
      <li comptype="5" compname="label_Date7" mandatory="false"><label class="labelName"><span>Nascimento:</span></label></li>
    </ul>
    <ul>
      <li elname="livefield-elem" comptype="1" compname="SingleLine38_0" mandatory="true" page_no="1"><input type="text" /></li>
      <li elname="livefield-elem" comptype="5" compname="Date7_0" page_no="1"><input type="text" /></li>
    </ul>
  </li>
</ul>
<ul elname="formBodyULName" page_no="2">
  <li class="tempFrmWrapper dropdown" elname="livefield-elem" comptype="12" compname="Dropdown" mandatory="false" page_no="2">
    <label class="labelName"><span>Estado</span></label>
    <select name="Dropdown"><option value="-Select-">-Select-</option><option value="SP">SP</option><option value="RJ">RJ</option></select>
  </li>
  <li class="tempFrmWrapper decesion" elname="livefield-elem" comptype="18" compname="DecisionBox" mandatory="true" page_no="2">
    <input type="checkbox" name="DecisionBox" />
    <label class="labelName cusChoiceLabel"><span elname="display_name"> Aceito os termos </span></label>
  </li>
  <li class="tempFrmWrapper note" elname="livefield-elem" compname="HtmlText" comptype="0" page_no="2">
    <label class="descFld"><div><span style="font-size:13px">Envie arquivos <script>alert(1)</script>legíveis.</span></div></label>
  </li>
</ul></div>
</body></html>
HTML;
}

function zohoRulesFixture(): array
{
    return ['form' => ['field_rules' => [
        [
            'rule_disp_name' => 'Filhos',
            'logical_oper' => 'matchall',
            'rule_status' => true,
            'conditions' => [['logical_oper' => 'and', 'conditions' => [
                ['field_link_name' => 'Radio', 'comparison_oper' => 'EQUALS', 'value' => 'Sim'],
            ]]],
            'actions' => [
                ['field_link_name' => 'Number1', 'action' => 'show'],
                ['field_link_name' => 'SubForm', 'action' => 'show'],
            ],
        ],
        [
            'rule_disp_name' => 'Filho 1',
            'logical_oper' => 'matchall',
            'rule_status' => true,
            'conditions' => [['logical_oper' => 'and', 'conditions' => [
                ['field_link_name' => 'Number1', 'comparison_oper' => 'GREATER_THAN', 'value' => '0'],
            ]]],
            'actions' => [['field_link_name' => 'SingleLine2', 'action' => 'show']],
        ],
        [
            'rule_disp_name' => 'Sem filhos',
            'logical_oper' => 'matchall',
            'rule_status' => true,
            'conditions' => [['logical_oper' => 'and', 'conditions' => [
                ['field_link_name' => 'Radio', 'comparison_oper' => 'EQUALS', 'value' => 'Não'],
            ]]],
            'actions' => [['field_link_name' => 'Dropdown', 'action' => 'hide']],
        ],
    ]]];
}

describe('ZohoFormsImporter', function () {
    it('maps Zoho fields, pages, options and help text', function () {
        $result = zohoImporter()->parse(zohoHtmlFixture(), null);
        $properties = $result['properties'];

        expect($result['title'])->toBe('Cadastro de Cliente');
        expect($properties[0]['type'])->toBe('nf-text')
            ->and($properties[0]['content'])->toBe('<h1>Cadastro de Cliente</h1>');
        expect($result['presentation_style'])->toBe('classic');

        $name = findProperty($properties, 'Nome Completo');
        expect($name['type'])->toBe('text')
            ->and($name['required'])->toBeTrue()
            ->and($name['placeholder'])->toBe('Seu nome')
            ->and($name['help'])->toBe('Como no <b>documento</b>.');

        $radio = findProperty($properties, 'Tem filhos?');
        expect($radio['type'])->toBe('select')
            ->and($radio['without_dropdown'])->toBeTrue()
            ->and(array_column($radio['select']['options'], 'name'))->toBe(['Sim', 'Não']);

        $dropdown = findProperty($properties, 'Estado');
        expect(array_column($dropdown['select']['options'], 'name'))->toBe(['SP', 'RJ'])
            ->and($dropdown)->not->toHaveKey('without_dropdown');

        expect(findProperty($properties, 'Quantos filhos?')['type'])->toBe('number');
        expect(findProperty($properties, 'Aceito os termos')['type'])->toBe('checkbox');

        $pageBreak = findProperty($properties, 'Documentos');
        expect($pageBreak['type'])->toBe('nf-page-break');

        $note = collect($properties)->firstWhere('name', 'Texto');
        expect($note['type'])->toBe('nf-text')
            ->and($note['content'])->not->toContain('script')
            ->and($note['content'])->not->toContain('style')
            ->and($note['content'])->toContain('legíveis');

        // Subform row cells are not imported as loose fields
        expect(collect($properties)->pluck('name'))->not->toContain('Nome completo');
    });

    it('turns subforms into a count question and fixed copies', function () {
        config(['opnform.zoho_import.subform_copies' => 2]);

        $properties = zohoImporter()->parse(zohoHtmlFixture(), null)['properties'];

        $count = findProperty($properties, 'Quantos você vai informar?');
        expect(array_column($count['select']['options'], 'name'))->toBe(['1', '2'])
            ->and($count['required'])->toBeTrue();

        expect(collect($properties)->pluck('name'))
            ->toContain('Nome completo (1)', 'Nascimento (2)')
            ->not->toContain('Nome completo (3)');

        $second = findProperty($properties, 'Nome completo (2)');
        expect($second['hidden'])->toBeTrue()
            ->and($second['required'])->toBeFalse()
            ->and($second['logic']['actions'])->toBe(['show-block', 'require-answer'])
            ->and($second['logic']['conditions']['value'])->toMatchArray([
                'operator' => 'equals',
                'value' => '2',
            ]);

        // Copy 1 shows for any count >= 1
        $first = findProperty($properties, 'Nome completo (1)');
        expect($first['logic']['conditions']['operatorIdentifier'])->toBe('or')
            ->and($first['logic']['conditions']['children'])->toHaveCount(2);
    });

    it('converts Zoho rules into Forms4U logic', function () {
        config(['opnform.zoho_import.subform_copies' => 3]);

        $properties = zohoImporter()->parse(zohoHtmlFixture(), zohoRulesFixture()['form'])['properties'];
        $radio = findProperty($properties, 'Tem filhos?');
        $number = findProperty($properties, 'Quantos filhos?');

        expect($number['hidden'])->toBeTrue()
            ->and($number['required'])->toBeFalse()
            ->and($number['logic']['actions'])->toBe(['show-block', 'require-answer'])
            ->and($number['logic']['conditions'])->toBe([
                'identifier' => $radio['id'],
                'value' => [
                    'operator' => 'equals',
                    'property_meta' => ['id' => $radio['id'], 'type' => 'select'],
                    'value' => 'Sim',
                ],
            ]);

        $child = findProperty($properties, 'Nome do filho');
        expect($child['logic']['conditions']['value']['operator'])->toBe('greater_than')
            ->and($child['logic']['conditions']['value']['value'])->toBe(0);

        $dropdown = findProperty($properties, 'Estado');
        expect($dropdown['hidden'])->toBeFalse()
            ->and($dropdown['logic']['actions'])->toBe(['hide-block']);

        // The subform's own rule gates the count question and every copy
        $count = findProperty($properties, 'Quantos você vai informar?');
        expect($count['hidden'])->toBeTrue()
            ->and($count['logic']['conditions']['identifier'])->toBe($radio['id']);

        $copy = findProperty($properties, 'Nome completo (3)');
        expect($copy['logic']['conditions']['operatorIdentifier'])->toBe('and')
            ->and($copy['logic']['conditions']['children'][0]['identifier'])->toBe($radio['id'])
            ->and($copy['logic']['conditions']['children'][1]['value']['value'])->toBe('3');
    });

    it('only accepts Zoho permalinks on Zoho or configured domains', function () {
        expect(zohoImporter()->validate(['url' => ZOHO_TEST_PERMALINK]))->toBeTrue();
        expect(zohoImporter()->validate(['url' => 'https://forms.zohopublic.com/acme/form/Cadastro']))->toBeFalse();
        expect(zohoImporter()->validate(['url' => 'https://evil.example.com/acme/form/Cadastro/formperma/AbC123']))->toBeFalse();

        config(['opnform.zoho_import.custom_domains' => 'forms.cidadania4u.com.br']);
        expect(zohoImporter()->validate(['url' => 'https://forms.cidadania4u.com.br/c4u/form/X/formperma/Y1']))->toBeTrue();
    });

    it('imports through the API, fetching the page and its public rules', function () {
        Http::fake([
            'forms.zohopublic.com/acme/form/Cadastro/formperma/AbC123/liverules' => Http::response(zohoRulesFixture(), 200),
            'forms.zohopublic.com/acme/form/Cadastro/formperma/AbC123' => Http::response(zohoHtmlFixture(), 200),
        ]);

        $this->postJson(route('open.forms.import'), [
            'source' => 'zoho_forms',
            'import_data' => ['url' => ZOHO_TEST_PERMALINK . '?zf_rszfm=1'],
        ])
            ->assertSuccessful()
            ->assertJsonPath('source', 'zoho_forms')
            ->assertJsonPath('form.title', 'Cadastro de Cliente');

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/formperma/AbC123/liverules'));
    });

    it('still imports the fields when the rules endpoint fails', function () {
        Http::fake([
            '*/liverules' => Http::response('nope', 500),
            '*' => Http::response(zohoHtmlFixture(), 200),
        ]);

        $result = zohoImporter()->import(['url' => ZOHO_TEST_PERMALINK]);

        expect(findProperty($result['properties'], 'Quantos filhos?'))->not->toHaveKey('logic');
    });
});
