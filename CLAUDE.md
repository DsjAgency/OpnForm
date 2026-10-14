# CLAUDE.md

Contexto para o Claude Code neste repositório. Leia também o `AGENTS.md` (convenções gerais do projeto).

## Contexto

- **O nome oficial do sistema é Forms4U.** Em todo texto visível ao usuário (títulos, abas do navegador, menus, mensagens, modais, rodapé, formulários públicos, e-mails, PDFs) use "Forms4U", nunca "OpnForm".
  - Não renomeie código interno: variáveis, classes (ex.: `OpnFormServer`), pastas, pacotes, cookies, ícones `opnform:*`, a API do SDK (`window.opnform`), nem os arquivos `LICENSE` e avisos de licença.
  - Exceção: o e-mail de chave de licença (`LicenseKeyNotification`) fala do produto OpnForm Enterprise e mantém esse nome.
- O código-base é o **OpnForm** (construtor de formulários open-source), sendo customizado para uso **interno da Cidadania4U**, em substituição ao Zoho Forms.
- Ambiente de desenvolvimento: `docker-compose.dev.yml`. Frontend em http://127.0.0.1:3000, API via Nginx em http://localhost.
- **Responda sempre em português.**

## Regra obrigatória

- **NUNCA alterar nada dentro de `api/app/Enterprise/`.** Esse código está sob licença proprietária (OpnForm Enterprise Edition — ver `api/app/Enterprise/LICENSE`). Não editar, mover, apagar nem copiar código de lá. Se uma tarefa parecer exigir mudança nessa pasta, pare e avise o usuário.
- Também não alterar arquivos de licença (`LICENSE`).

## Estrutura do projeto

- `api/` — backend Laravel (PHP). Regras de negócio, banco de dados, e-mails, API REST.
  - `api/app/` — código da aplicação (controllers, models, services). `api/app/Enterprise/` é proibido (ver acima).
  - `api/resources/views/mail/` — templates dos e-mails enviados (formulários e usuários).
  - `api/resources/lang/` — traduções de mensagens do backend (validação, erros).
- `client/` — frontend Nuxt 3 / Vue (JavaScript). Tudo que o usuário vê.
  - `client/pages/` — páginas/rotas (dashboard, login, formulários etc.). Contém também páginas de marketing do OpnForm (`pricing.vue`, `opnform-vs-*.vue`, `home.vue`...) que não interessam ao uso interno.
  - `client/components/` — componentes Vue (`global/Navbar.vue`, `dashboard/`, `forms/`, `open/` = renderização pública dos formulários, `pages/` = componentes de marketing/rodapé).
  - `client/layouts/` — layouts gerais (`default.vue`, `dashboard.vue`, `empty.vue`).
- `docs/` — documentação Mintlify.
- `docker/`, `docker-compose*.yml`, `scripts/` — infraestrutura.

## Onde fica cada parte do visual

| O quê | Onde |
|---|---|
| Logo | `client/public/img/logo.svg` e `client/public/img/logo.png` (usados em `client/components/global/Navbar.vue`, `client/components/pages/OpenFormFooter.vue`, `client/pages/setup.vue`, `client/pages/mcp/authorize.vue`) |
| Favicon | `client/public/favicon.ico` |
| Imagem de compartilhamento (redes sociais) | `client/public/img/social-preview.jpg` |
| Cores do app (Nuxt UI) | `client/app.config.ts` → `ui.colors` (`primary`, `secondary` etc., hoje `blue`) |
| Cores/variáveis CSS globais, Tailwind v4 | `client/css/app.css` (bloco `@theme`, ex.: `--form-color`) |
| Fontes | `client/css/fonts.css` e `client/public/fonts/` |
| Temas visuais dos campos dos formulários | `client/lib/forms/themes/` |
| Nome do app e links externos (help, GitHub, Discord...) | `client/opnform.config.js` |
| Título e descrição padrão das páginas (SEO) | `client/app.vue` (sufixo "- OpnForm") |
| Textos traduzidos dos formulários públicos | `client/i18n/lang/*.json` (português: `pt.json`; config em `client/nuxt.config.ts` → `i18n`) |
| Textos do painel/dashboard | Em geral escritos direto (em inglês) dentro dos `.vue` em `client/pages/` e `client/components/` — não passam pelo i18n |
| Textos de e-mails | `api/resources/views/mail/` |

## Idioma (português)

- Idioma padrão do sistema: português (`pt`).
  - Frontend: `defaultLocale: 'pt'` em `client/nuxt.config.ts`; `lang="pt-BR"` em `client/app.vue`.
  - Formulários novos nascem com `language: 'pt'` (`client/composables/forms/initForm.js`).
  - Backend: `config/app.php` lê `APP_LOCALE` (padrão `en`, para os testes); o `docker-compose.dev.yml` define `APP_LOCALE: pt`. Em produção, defina `APP_LOCALE=pt` também.
- Os textos do painel foram traduzidos **direto no código** (decisão do usuário), sem chaves de i18n. Ao criar ou alterar telas do painel, escreva os textos em português (pt-BR) direto nos `.vue`/`.js`.
- Os textos do formulário público continuam via i18n (`client/i18n/lang/pt.json`), porque seguem o idioma escolhido em cada formulário.
- Vocabulário: Form → Formulário, Submission → Resposta, Workspace → Espaço de trabalho, Template → Modelo, Share → Compartilhar, Analytics → Análises, Settings → Configurações, Field → Campo, Block → Bloco.
- Não traduza strings comparadas com mensagens/valores vindos da API (ex.: `errorMessage.includes(...)`, valores `'Yes'`/`'No'` vindos do backend, `AGGREGATE_MESSAGES`).
- Ficaram em inglês, de propósito, as páginas de marketing do OpnForm (landing, preços, comparativos, catálogo de integrações, MCP), cobrança/assinaturas e AppSumo.
- Testes Vitest e Playwright esperam os textos em português; ao mudar um texto, atualize os testes em `client/test/` e `client/tests/`.

## Marca e itens removidos do OpnForm

- Logo do Forms4U: `client/public/img/forms4u-logo.png` (horizontal, prancheta + "forms 4U") e `forms4u-icon.png` (só a prancheta, 512px). `logo.svg`/`logo.png` também contêm o ícone novo. Favicon em `client/public/favicon.ico`.
- Visual do painel (reformulação Forms4U):
  - Início (`client/pages/home.vue`): saudação, cartões de resumo (`components/pages/home/HomeStatCard.vue`, com contagem animada) e formulários em grade ou lista (`FormCard.vue`, escolha salva no navegador).
  - Login em duas colunas (`client/pages/login.vue`, layout `empty`).
  - Menu lateral com item ativo em tom vinho e o botão "Criar formulário" sólido.
  - Avatar de usuário sempre via `UserAvatar` (`components/global/UserAvatar.vue`).
  - Animações globais no `app.css`: `f4u-fade-up` e `f4u-float` (respeitam "reduzir movimento").
  - Tempo relativo em pt-BR no navegador com `timeAgo()` (`client/lib/utils.js`), porque o `last_edited_human` da API segue o idioma do navegador.
- Formulário público: formulários clássicos com largura "centralizada" aparecem como cartão sobre fundo suave tingido pela cor do formulário (`useCardLayout` em `components/open/forms/OpenForm.vue`; classes `f4u-form-backdrop` / `f4u-form-card` no `app.css`). Vale na página pública e na pré-visualização do editor, nunca em iframes/embeds. O selo "Feito com Forms4U" é `components/pages/forms/show/PoweredBy.vue`.
  - Formato "uma pergunta por vez" (`OpenFormFocused.vue`): usa o mesmo fundo (`f4u-form-backdrop`) quando não há imagem de capa; setas com contraste calculado pela cor do formulário.
  - Modo escuro só existe nos formulários públicos (configuração de cada formulário); o painel é sempre claro (`colorMode` fixo em `light`).
- Sem azul do OpnForm no painel/editor: use `primary-*` (vinho). As páginas de marketing (que redirecionam) e as cores de ícone de espaço de trabalho podem manter outras cores.
- Cor primária: vinho `#640e2c` (paleta `cidadania` 50–950 em `@theme static` no `client/css/app.css`); `--ui-primary` usa o tom 800 no claro e 400 no escuro.
- Menu lateral sem as seções "Produto" e "Ajuda" (`useSharedNavigation` retorna lista vazia).
- Sem banners/modais de upgrade: `openSubscriptionModal` (em `useAppModals`) só mostra um aviso neutro; `PlanTag` nunca aparece; `UpgradeBanner` e `GitHubStar` foram removidos.
- Recursos bloqueados por plano/licença Enterprise continuam bloqueados (não contornar a licença); apenas não há propaganda.
- Sem links para Discord/GitHub/site/docs/central de ajuda do OpnForm.
- Páginas de marketing redirecionam para `/home` (logado) ou `/login`: `client/middleware/marketing-redirect.global.js`. Não use `defineRouteRules({ swr })` nessas páginas: o cache do servidor ignoraria o login e serviria o mesmo redirecionamento para todos.
- Modelos: só os criados pela equipe (banco local). Os modelos oficiais do opnform.com ficam desligados (`SHOW_OFFICIAL_TEMPLATES=false`, padrão em `api/config/opnform.php`). A tela "Modelos" é `/templates/my-templates`; a galeria `/templates` e as páginas por tipo/setor redirecionam para ela.

## Validação

- Frontend alterado: `npm run lint` dentro de `client/`.
- Backend alterado: `php artisan test --filter=...` dentro de `api/` (ou no container `opnform-api`).
