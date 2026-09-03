# Work Log

Este arquivo registra acontecimentos relevantes do desenvolvimento.

O objetivo não é armazenar uma transcrição das conversas, mas preservar informações úteis para continuidade.

---

## 2026-09-03

**Tarefa:**

Correção da exibição de horários livres e disponibilização do formulário no dashboard do paciente.

### Alterações

- criado o partial `resources/views/solicitar/_form.blade.php` e reutilizado em `resources/views/solicitar.blade.php` e no dashboard do paciente;
- a API `GET /api/slots` passou a serializar `start` e `end` em formato local (`Y-m-d\TH:i:s`) em vez de UTC com `toISOString()`, reduzindo deslocamentos de horário por fuso;
- o calendário público passou a ordenar slots livres, atualizar eventos após carregamento e manter pré-seleção estável do primeiro horário disponível.

### Validação

- `docker compose run --rm --no-deps php php artisan test tests/Feature/SlotCreationTest.php tests/Feature/PermissoesPorPerfilTest.php tests/Feature/DashboardPorPerfilTest.php --no-ansi` aprovou 7 testes e 22 asserções;
- `npm run build` concluído com sucesso.

## 2026-09-03

**Tarefa:**

Correção do formulário público de solicitação e do cadastro de horários livres.

### Diagnóstico

- o campo oculto `scheduled_at` dependia de seleção manual no calendário público e podia permanecer vazio, gerando erro de validação na submissão;
- o cadastro de horário livre aceitava apenas `start`, embora a regra de negócio pedisse início e fim explícitos.

### Alterações

- o calendário público passou a exibir os horários livres como eventos verdes e a pré-selecionar automaticamente o primeiro horário disponível do dia ativo;
- o modal de criação de horário livre passou a coletar data, horário inicial e horário final;
- `SlotController` passou a exigir `end` e a respeitar a duração informada também nas repetições semanais;
- o calendário administrativo continua se atualizando automaticamente após a criação do slot, preservando o padrão de cores.

### Validação

- `docker compose run --rm --no-deps php php artisan test tests/Feature/SlotCreationTest.php tests/Feature/PermissoesPorPerfilTest.php --no-ansi` aprovou 4 testes e 15 asserções;
- `npm run build` concluiu com sucesso após as mudanças em `resources/js/calendar.js` e `resources/js/public-calendar.js`.

### Observação

- a correção do preenchimento automático de `scheduled_at` foi validada por inspeção do fluxo e pelo build dos assets; não há teste E2E de navegador neste repositório.

## 2026-09-03

**Tarefa:**

Prevenção de perda de dados reais durante a execução dos testes.

### Diagnóstico

- `phpunit.xml` não definia banco próprio para testes;
- a suíte com `RefreshDatabase` estava usando o banco configurado pela aplicação, o que recriava o schema remoto e apagava usuários reais.

### Alterações

- `phpunit.xml` passou a definir `DB_CONNECTION=sqlite` e `DB_DATABASE=:memory:` para o ambiente de testes;
- os usuários `admin@psi.cpetersenjr.com`, `paciente@psi.cpetersenjr.com` e `doutor@psi.cpetersenjr.com` foram recriados no banco remoto após a correção.

### Validação

- `docker compose run --rm --no-deps php php artisan test tests/Feature/PermissoesPorPerfilTest.php tests/Feature/DashboardPorPerfilTest.php tests/Feature/SlotCreationTest.php --no-ansi` aprovou 6 testes em 0.34s, confirmando o uso do banco isolado;
- após nova execução de `tests/Feature/PermissoesPorPerfilTest.php`, a checagem no banco remoto confirmou a permanência dos três usuários operacionais.

## 2026-09-03

**Tarefa:**

Separação de experiência e permissões por perfil no sistema.

### Alterações

- criado `DashboardController` para montar o conteúdo específico de `admin`, `profissional` e `paciente`;
- adicionada gestão básica de usuários para o administrador com `UsuarioController` e views dedicadas;
- `SlotController`, `AgendamentoController`, `PacienteController` e `ProntuarioController` passaram a restringir ações internas por perfil;
- o profissional passou a confirmar sessões pelo endpoint `PATCH /agendamentos/{agendamento}/confirmar`;
- o calendário passou a marcar solicitações pendentes em amarelo, sessões confirmadas em azul e horários livres em verde;
- a navegação autenticada passou a exibir o link de solicitação pública apenas para o paciente.

### Validação

- `docker compose run --rm --no-deps php php artisan test tests/Feature/PermissoesPorPerfilTest.php tests/Feature/DashboardPorPerfilTest.php tests/Feature/SlotCreationTest.php --no-ansi` aprovou 6 testes e 20 asserções.

### Pendência

- ainda falta revisar outras telas legadas para alinhar completamente terminologia e restrições fora do fluxo principal do dashboard.

## 2026-09-03

**Tarefa:**

Correção do erro ao criar horário livre via fluxo de slots.

### Diagnóstico

- o controller `SlotController` e o front-end do calendário utilizavam a tabela `slots`, mas o schema ativo não a criava;
- verificação no banco configurado confirmou `Schema::hasTable('slots') === false` antes da correção.

### Alterações

- adicionada a migration ativa `2026_09_03_130000_create_slots_table.php`, com vínculo opcional a `usuarios` por `usuario_id`;
- criado o teste `tests/Feature/SlotCreationTest.php` para validar a criação autenticada de um horário livre via `POST /slots`.

### Validação

- `docker compose run --rm --no-deps php php artisan test tests/Feature/SlotCreationTest.php --no-ansi` aprovou 1 teste e 4 asserções;
- `php artisan migrate --force --no-ansi` não encontrou pendências após a inclusão da migration;
- verificação via `tinker` confirmou `Schema::hasTable('slots') === true` no banco configurado.

## 2026-09-03

**Tarefa:**

Provisionamento direto de usuários no banco remoto para o domínio `psi.cpetersenjr.com`.

### Alterações

- criados ou atualizados diretamente na tabela `usuarios` os registros `admin@psi.cpetersenjr.com`, `paciente@psi.cpetersenjr.com` e `doutor@psi.cpetersenjr.com`;
- os perfis aplicados foram `admin`, `paciente` e `profissional`, respectivamente;
- os três registros permaneceram com status `ativo`.

### Validação

- consulta direta no banco confirmou os três e-mails com os perfis esperados;
- verificação por hash confirmou a senha definida para os três usuários.

### Observação

- a senha foi aplicada no ambiente remoto, mas não foi registrada neste documento.

## 2026-09-03

**Tarefa:**

Execução e verificação das migrations no banco remoto para remover a tabela legada `users`.

### Resultado

- `php artisan migrate --force --no-ansi` informou que não havia migrations pendentes;
- `php artisan migrate:status --no-ansi` confirmou a migration `2026_09_03_120000_drop_legacy_users_table` como aplicada;
- verificação via `tinker` confirmou `Schema::hasTable('users') === false` e `Schema::hasTable('usuarios') === true`.

### Observação

- a remoção da tabela `users` já estava efetivamente aplicada no banco configurado pelo projeto antes desta execução.

## 2026-09-03

**Tarefa:**

Remoção da tabela legada `users` do projeto.

### Alterações

- a migration inicial `0001_01_01_000000_create_users_table.php` deixou de criar a tabela `users`, preservando apenas `password_reset_tokens` e `sessions`;
- foi adicionada a migration `2026_09_03_120000_drop_legacy_users_table.php` para remover `users` em bases já existentes;
- foram removidos os artefatos legados `app/Models/User.php` e `database/factories/UserFactory.php`;
- foi criado o teste `tests/Feature/UsuariosSchemaTest.php` para garantir que o schema ativo usa `usuarios` e não `users`.

### Validação

- `docker compose run --rm --no-deps php php artisan test tests/Feature/UsuariosSchemaTest.php tests/Unit/AutenticacaoUsuariosConfigTest.php tests/Feature/Auth/AuthenticationTest.php tests/Feature/Auth/PasswordResetTest.php --no-ansi` aprovou 11 testes e 24 asserções.

### Observação

- permanece apenas uma referência histórica a `users` em migration desativada dentro de `database/migrations/disabled/`, sem efeito no runtime.

## 2026-09-03

**Tarefa:**

Garantia de que o processo de autenticação usa a tabela `usuarios`.

### Alterações

- `config/auth.php` passou a usar `usuarios` como nome explícito do provider Eloquent e do broker de reset de senha;
- o provider deixou de depender de `AUTH_MODEL` e passou a apontar diretamente para `App\Models\Usuario`;
- foi criado o teste `tests/Unit/AutenticacaoUsuariosConfigTest.php` para verificar a configuração e a resolução do modelo pelo guard `web`.

### Validação

- `docker compose run --rm --no-deps php php artisan test tests/Unit/AutenticacaoUsuariosConfigTest.php tests/Feature/Auth/AuthenticationTest.php tests/Feature/Auth/PasswordResetTest.php --no-ansi` aprovou 10 testes e 22 asserções.

## 2026-09-03

**Tarefa:**

Correção da mistura entre `User`/`users` e `Usuario`/`usuarios` no código de aplicação.

### Alterações

- `Prontuario` passou a usar a relação `criador()` com o modelo `Usuario`, mantendo `creator()` apenas como alias de compatibilidade;
- `Slot` passou a expor a relação `usuario()` com fallback para a coluna legada `user_id`;
- `SlotController` passou a gravar o vínculo do slot pela coluna resolvida dinamicamente entre `usuario_id` e `user_id`;
- `ProntuarioRequest` deixou de validar `created_by` contra a tabela `users` e passou a resolver a tabela de `Paciente` e `Usuario` dinamicamente;
- removida importação residual de `User` no `DatabaseSeeder`.

### Validação

- diagnósticos do editor sem erros nos arquivos alterados;
- `docker compose run --rm --no-deps php sh -lc "php -l database/seeders/DatabaseSeeder.php && php -l app/Models/Prontuario.php && php -l app/Models/Slot.php && php -l app/Http/Controllers/SlotController.php && php -l app/Http/Requests/ProntuarioRequest.php"` concluiu sem erros de sintaxe.

### Pendência

Revisar, em uma etapa separada, os campos legados ainda presentes nas views e requests de prontuários que misturam `patient_id`/`patients` com o domínio atual em português.

## 2026-09-03

**Tarefa:**

Correção da compatibilidade entre o fluxo público/CRUD de agendamentos e o esquema atual da tabela `agendamentos`.

### Alterações

- o modelo `Agendamento` passou a centralizar a escolha entre colunas legadas (`scheduled_at`, `duration_minutes`, `notes`) e atuais (`data_hora_inicio`, `data_hora_fim`, `observacoes_cancelamento`);
- `SolicitacaoController` e `AgendamentoController` deixaram de gravar e consultar `scheduled_at` diretamente quando o esquema ativo usa `data_hora_inicio`/`data_hora_fim`;
- o dashboard passou a buscar próximos agendamentos pela coluna de início resolvida dinamicamente;
- `AgendamentoRequest` passou a validar `paciente_id` contra a tabela real do modelo `Paciente`;
- foi criado o teste unitário `tests/Unit/AgendamentoCompatibilityTest.php` para cobrir payload, accessors e serialização JSON compatíveis.

### Validação

- diagnósticos do editor sem erros nos arquivos alterados;
- `docker compose run --rm --no-deps php php artisan test tests/Unit/AgendamentoCompatibilityTest.php --no-ansi` aprovou 3 testes e 18 asserções.

### Próximo passo

Validar o fluxo HTTP completo de solicitação pública e o CRUD de agendamentos com testes de feature cobrindo slots e conflito de horários.

## 2026-09-02

**Tarefa:**

Criação de usuários iniciais no banco MariaDB remoto para o domínio `psi.cpetersenjr.com`.

### Alterações

- criada a conta `admin@psi.cpetersenjr.com` com perfil `admin`;
- criada a conta `paciente@psi.cpetersenjr.com` com perfil `paciente`;
- criada a conta `doutor@psi.cpetersenjr.com` com perfil `profissional`;
- credenciais temporárias não foram registradas neste documento.

### Validação

- os três registros foram encontrados na tabela `usuarios`;
- status confirmado como `ativo`;
- autenticação das três credenciais temporárias validada via CLI.

## 2026-09-02

**Tarefa:**

Encerramento do incidente HTTP 500 no servidor Plesk.

### Resultado

- o erro deixou de ocorrer em produção e foi considerado corrigido;
- a causa raiz não foi confirmada porque os logs disponíveis não continham o incidente do servidor.

### Pendência

Manter a verificação dos logs do Plesk caso o erro reapareça, para identificar a causa técnica com precisão.

## 2026-09-02

**Tarefa:**

Provisionamento de usuários demo no MariaDB remoto para testes de autenticação.

### Alterações

- criadas/atualizadas diretamente na tabela `users` as contas `admin@psica.dev`, `helena@psica.dev` e `paciente@psica.dev`;
- operação executada com `updateOrCreate`, evitando duplicação por e-mail;
- senha de teste definida somente no ambiente remoto e não registrada neste documento.

### Validação

- três contas demo encontradas no banco remoto;
- verificação CLI confirmou a validade das senhas.

## 2026-09-02

**Tarefa:**

Seleção de horários disponíveis no formulário público de solicitação.

### Alterações

- substituído o input manual de data por calendário mensal com horários carregados de `/api/slots`;
- somente slots com status `free` podem ser selecionados;
- o backend valida que o horário enviado pertence a um slot livre com duração de uma hora;
- corrigido o fallback de headers de proxy no Nginx para evitar erro 500 quando os headers chegam vazios.

### Validação

- build do Vite concluído;
- `/solicitar` respondeu HTTP 200 e renderizou o calendário;
- suíte completa: 25 testes e 61 asserções aprovados.

## 2026-09-02

**Tarefa:**

Compatibilidade com servidor externo que usa a raiz do Laravel como document root.

### Alterações

- adicionado `index.php` na raiz como front controller compatível com Laravel;
- adicionado `.htaccess` para servir assets de `public/` com URLs sem `/public` e encaminhar rotas ao Laravel;
- bloqueado acesso direto a código-fonte, dependências e arquivos de configuração.

### Validação

- `php -l index.php` sem erros;
- suíte completa: 25 testes e 61 asserções aprovados.

## 2026-09-02

**Tarefa:**

Correção das URLs de assets quando a aplicação é acessada pelo domínio encaminhado do Codespaces.

### Alterações

- Laravel passou a confiar nos proxies reversos configurados pelo ambiente;
- Nginx passou a encaminhar o host e o protocolo originais nos requests FastCGI.

### Validação

- a home passou a gerar URLs `https://...app.github.dev` para imagens e assets quando testada com os headers do proxy;
- suíte completa validada após a alteração.

## 2026-09-02

**Tarefa:**

Correção de inconsistência no carregamento de relações de prontuários.

### Alterações

- `ProntuarioController::index()` passou a carregar a relação `paciente`, definida no modelo `Prontuario`, no lugar da relação inexistente `patient`.

### Validação

- diagnóstico do editor não encontrou erros no controller;
- `php -l app/Http/Controllers/ProntuarioController.php` não executou porque o binário PHP falhou ao carregar `libcrypto.so.1.1` com a versão `OPENSSL_1_1_1` requerida.

### Próximo passo

Analisar a lógica de conflitos entre solicitações, agendamentos e slots.

---

## 2026-09-02

**Tarefa:**

Preparação e validação do acesso ao MariaDB remoto.

### Alterações

- adicionada a imagem PHP em `docker/php/Dockerfile`, com a extensão `pdo_mysql`;
- `docker-compose.yml` passou a construir a imagem PHP do projeto e a tratar o MariaDB local como perfil opcional `local-db`;
- `.env` passou a selecionar o driver `mariadb`; `.env.example` foi alinhado com a configuração remota;
- `.env` foi removido do índice Git e voltou a ser ignorado, preservando o arquivo local.

### Validação

- a imagem foi construída com sucesso e carregou `pdo_mysql`;
- `docker compose run --rm --no-deps php php artisan migrate:status --no-ansi` confirmou conexão com o MariaDB remoto e listou todas as migrations como aplicadas, sem executar alterações.

---

## 2026-09-01

**Agente:** MAI-Code-1.1-Flash

**Tarefa:**

Inicialização de continuidade do projeto e reconstrução do contexto do repositório antes de qualquer alteração funcional.

### Investigação

- o projeto é uma aplicação Laravel para gestão de pacientes, prontuários e agendamentos de sessões clínicas;
- há rotas públicas e administrativas, além de calendário FullCalendar e slots de disponibilidade;
- a base ainda contém compatibilidade com nomes em inglês e em português, o que exige atenção em runtime;
- a documentação inicial estava em template genérico e não refletia o estado real do código.

### Alterações

- `docs/Project Context.md` — preenchido com o contexto real do produto e do domínio;
- `docs/Current State.md` — preenchido com estado atual, bloqueios, riscos e próximos passos;
- `docs/Architectural and Project Decisions.md` — registradas decisões estruturantes do projeto;
- `docs/Work Log.md` — registrado o início da continuidade e a análise da situação atual.

### Testes

- Nota: Esclarecido que o projeto rodará em um servidor externo e, por isso, os testes dependem de deploy lá. Não é necessário sanar o problema de OpenSSL localmente para rodar testes no dev container.

### Decisões

- ADR-001 — compatibilidade com nomenclatura em português e inglês;
- ADR-002 — fluxo público + calendário administrativo;
- ADR-003 — conexão com MariaDB remoto.

### Problemas

- documentação do projeto estava desatualizada;
- há risco de inconsistência em nomes de tabelas e relações Eloquent.

### Pendências

- validar e corrigir inconsistências de relacionamentos e nomenclaturas (ex: `ProntuarioController` chamando `with('patient')` em vez de `paciente`);
- validar os fluxos lógicos de agendamento e slots.

### Próximo passo

Identificar e corrigir inconsistências conhecidas no código PHP (como no `ProntuarioController`) e validar a lógica estática de relacionamentos e regras de negócios no repositório.

---