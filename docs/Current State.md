# Current State

**Última atualização:** 2026-09-03

## Estado geral

O projeto está estruturado como uma aplicação Laravel de gestão de clínica com módulos de pacientes, prontuários, agendamentos e disponibilidade por slots. A arquitetura principal já está implementada no código. Como o projeto rodará em um servidor externo, a validação executável e os testes automatizados dependem de deploy nesse ambiente remoto, não sendo prioritário corrigir o ambiente de execução PHP local no container de desenvolvimento.

---

## Implementado

- fallback de hospedagem com a raiz do projeto como document root, usando `index.php` e `.htaccess` para encaminhar assets para `public/` sem expor esse prefixo na URL;
- autenticação e schema principal consolidados sobre a tabela `usuarios`, com remoção da criação da tabela legada `users` em instalações novas;
- tabela `slots` disponível no schema ativo para sustentar o calendário administrativo e a criação de horários livres;
- dashboard e permissões diferenciados por perfil (`admin`, `profissional`, `paciente`);
- confirmação de sessões solicitadas pelo profissional diretamente no calendário, com codificação visual por status;
- formulário de criação de horários livres com data, horário inicial e horário final, atualizado automaticamente no calendário após o cadastro;
- calendário público de solicitação exibindo horários livres e pré-selecionando automaticamente um horário disponível para preencher `scheduled_at`.
- formulário de solicitação de sessão disponível diretamente no dashboard do paciente, reutilizando o mesmo fluxo da tela pública.
- modelos e migrations para `Paciente`, `Prontuario`, `Agendamento` e `Slot`;
- rotas públicas e protegidas para cadastro de pacientes, agendamentos e prontuários;
- formulário público de solicitação de sessão em `resources/views/solicitar.blade.php`;
- dashboard administrativo com calendário FullCalendar em `resources/js/calendar.js`;
- API pública para listagem de agendamentos (`/api/agendamentos`) e slots (`/api/slots`);
- API de criação de agendamento via calendário (`/api/solicitar`).
- conexão com o MariaDB remoto validada por `docker compose run --rm --no-deps php php artisan migrate:status --no-ansi`;
- imagem Docker PHP com a extensão `pdo_mysql` para acesso a MySQL/MariaDB.

---

## Em desenvolvimento

- validação real da consistência dos nomes de tabela/coluna em inglês e em português;
- verificação das regras de conflito de horários em agendamento e slots;
- confirmação do comportamento end-to-end da criação de solicitações públicas.
- redução das consultas fixas em `scheduled_at` para concentrar a compatibilidade de esquema no modelo `Agendamento`.
- refinamento das telas legadas fora do dashboard para refletirem integralmente o novo modelo de perfis e nomenclatura em português.
- deploy da correção de compatibilidade de `Paciente` no fluxo `/solicitar` para o domínio `psi.cpetersenjr.com`.

---

## Planejado

- avaliar a necessidade de corrigir o binário PHP do dev container, pois os comandos da aplicação podem ser executados pela imagem Docker;
- rodar suíte de testes e ajustar bugs reais de integração;
- revisar e unificar convenções de nomenclatura de banco de dados;
- melhorar a documentação de fluxo e regras de negócio.

---

## Problemas conhecidos

- a publicação da raiz do projeto depende de Apache/servidor compatível com `mod_rewrite` e `AllowOverride`; Nginx deve usar a configuração equivalente em vez de `.htaccess`;
- há um padrão de compatibilidade dual entre nomes em inglês e em português, que pode causar bugs em runtime;
- ainda existe uma migration histórica desativada em `database/migrations/disabled/` com referência a `users`, mas ela não participa do schema ativo;
- alguns arquivos continuam em template genérico do Laravel em vez de documentação específica do projeto;
- o ambiente de dados foi definido como MariaDB remoto em infraestrutura externa, o que exige configuração explícita do `.env` e revisão de conexão em todo o ambiente de execução.

---

## Dívida técnica prioritária

1. validar e unificar nomes de tabelas e colunas do banco;
2. testar e corrigir os caminhos de relacionamento Eloquent inconsistentes;
3. confirmar a lógica de conflitos entre slots e agendamentos em produção real;
4. preparar a estrutura de deploy/CI para rodar os testes automatizados no servidor externo.

---

## Testes

### Funcionando

- Os testes automatizados locais agora usam SQLite em memória via `phpunit.xml`, isolando `RefreshDatabase` do banco remoto real.
- A conexão somente leitura ao MariaDB remoto foi confirmada via `migrate:status` na imagem Docker PHP.
- teste unitário `AgendamentoCompatibilityTest` validado no container PHP para garantir payload e accessors compatíveis entre esquemas legado e atual.

### Ausentes

- testes de integração para solicitação pública de sessão;
- testes de sobreposição de slots e agendamentos;
- testes para migração de nomes de tabela em inglês/português.

### Falhando

- Execução local de comandos PHP, inclusive `php -l`, é inviável no dev container devido à incompatibilidade com `libcrypto.so.1.1` (OpenSSL 1.1.1); a validação executável permanece delegada ao pipeline de CI/CD externo.

### Produção

- o erro HTTP 500 observado no servidor Plesk foi considerado resolvido após deixar de ocorrer;
- a causa raiz do incidente não foi confirmada nos logs disponíveis.

---

## Últimas alterações importantes

- 2026-09-03 — formulário de solicitação passou a ser renderizado também no dashboard do paciente com o mesmo calendário de horários livres;
- 2026-09-03 — API de slots deixou de usar `toISOString()` e passou a enviar data/hora local serializada, reduzindo problemas de fuso na visualização de horários livres;
- 2026-09-03 — corrigido no código do projeto o erro de compatibilidade de colunas de `Paciente` no fluxo de solicitação (`nome/telefone` vs `name/phone`), incluindo reaproveitamento por `usuario_id` quando o paciente está logado;
- 2026-09-03 — corrigido o erro de `scheduled_at` obrigatório na solicitação pública com pré-seleção automática de horário livre e exibição dos slots verdes no calendário do formulário;
- 2026-09-03 — o registro de horários livres passou a exigir início e fim explícitos, com atualização automática do calendário após criação;
- 2026-09-03 — corrigida a configuração de testes para usar SQLite em memória; a suíte deixou de recriar o banco remoto e os usuários operacionais permaneceram intactos após nova execução de testes;
- 2026-09-03 — implementada a separação do dashboard por perfil: administrador gerencia usuários, doutor gerencia agenda e confirma sessões, paciente vê histórico, recibos e anotações;
- 2026-09-03 — calendário do profissional passou a diferenciar sessões solicitadas em amarelo e confirmadas em azul, mantendo horários livres em verde e horários indisponíveis não selecionáveis;
- 2026-09-03 — corrigido o erro de criação de horários livres com a adição da tabela `slots` ao schema ativo; criação autenticada de slot validada por teste de feature e verificação da tabela no banco configurado;
- 2026-09-03 — removida a tabela legada `users` do schema ativo; instalações novas passam a criar apenas `usuarios`, e uma migration adicional remove `users` de bases existentes;
- 2026-09-03 — o processo de autenticação foi explicitamente configurado para usar o provider `usuarios` e o modelo `Usuario`; login, logout e reset de senha foram validados com a suíte de autenticação no container PHP;
- 2026-09-03 — removidas referências ativas de aplicação a `User`/`users` em prontuários e slots; o código passou a usar `Usuario` como modelo de domínio e autenticação nesses pontos;
- 2026-09-03 — centralizada a compatibilidade de colunas de agendamento no modelo `Agendamento`; controllers e dashboard deixaram de depender diretamente de `scheduled_at` para leitura e escrita no esquema atual;
- 2026-09-03 — adicionado teste unitário `tests/Unit/AgendamentoCompatibilityTest.php` e validado com 3 testes e 18 asserções no container PHP;
- 2026-09-02 — erro HTTP 500 no servidor Plesk considerado resolvido após desaparecer em produção; causa raiz não confirmada;
- 2026-09-02 — adicionada imagem PHP Docker com `pdo_mysql`; o ambiente passou a selecionar o driver `mariadb`, e o `.env` deixou de ser rastreado pelo Git;
- 2026-09-02 — corrigida a listagem de prontuários para carregar a relação Eloquent `paciente`, em vez da relação inexistente `patient`;
- 2026-09-01 — documentação do projeto foi atualizada para refletir o contexto real e o estado atual do repositório;
- 2026-09-01 — avaliação inicial do código revelou o fluxo de agendamento, slots e compatibilidade de banco de dados.

---

## Decisões recentes

- ADR-001 — compatibilidade com tabelas em português e inglês durante transição de nomenclatura;
- ADR-002 — uso de um formulário público e de um calendário administrativo para gestão de disponibilidade e agendamento.

---

## Próximo passo recomendado

1. analisar a lógica de conflitos de horários em solicitações e slots;
2. validar e corrigir demais inconsistências de nomenclatura no banco e nos relacionamentos de modelos e controllers;
3. validar o fluxo de deploy e verificar os logs de execução no servidor externo se disponíveis.

---

## Bloqueios

- documentação do projeto ainda em template incompleto e não refletindo a realidade do código;
- risco de regressão por nomes de tabela e colunas divergentes.

---

## Observações para o próximo agente

O binário PHP do dev container continua incompatível com OpenSSL, mas a imagem Docker do projeto possui `pdo_mysql` e acesso comprovado ao MariaDB remoto. Use `docker compose run --rm --no-deps php php artisan <comando>` para validações PHP enquanto o ambiente local não for corrigido.