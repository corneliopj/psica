# Work Log

Este arquivo registra acontecimentos relevantes do desenvolvimento.

O objetivo não é armazenar uma transcrição das conversas, mas preservar informações úteis para continuidade.

---

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