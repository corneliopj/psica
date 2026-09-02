# Current State

**Última atualização:** 2026-09-01

## Estado geral

O projeto está estruturado como uma aplicação Laravel de gestão de clínica com módulos de pacientes, prontuários, agendamentos e disponibilidade por slots. A arquitetura principal já está implementada no código. Como o projeto rodará em um servidor externo, a validação executável e os testes automatizados dependem de deploy nesse ambiente remoto, não sendo prioritário corrigir o ambiente de execução PHP local no container de desenvolvimento.

---

## Implementado

- modelos e migrations para `Paciente`, `Prontuario`, `Agendamento` e `Slot`;
- rotas públicas e protegidas para cadastro de pacientes, agendamentos e prontuários;
- formulário público de solicitação de sessão em `resources/views/solicitar.blade.php`;
- dashboard administrativo com calendário FullCalendar em `resources/js/calendar.js`;
- API pública para listagem de agendamentos (`/api/agendamentos`) e slots (`/api/slots`);
- API de criação de agendamento via calendário (`/api/solicitar`).

---

## Em desenvolvimento

- validação real da consistência dos nomes de tabela/coluna em inglês e em português;
- verificação das regras de conflito de horários em agendamento e slots;
- confirmação do comportamento end-to-end da criação de solicitações públicas.

---

## Planejado

- corrigir o ambiente PHP/openssl para permitir execução de testes e app;
- rodar suíte de testes e ajustar bugs reais de integração;
- revisar e unificar convenções de nomenclatura de banco de dados;
- melhorar a documentação de fluxo e regras de negócio.

---

## Problemas conhecidos

- há um padrão de compatibilidade dual entre nomes em inglês e em português, que pode causar bugs em runtime;
- o `ProntuarioController` usa `with('patient')` enquanto o relacionamento do modelo é `paciente`;
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

- Testes e execução são direcionados ao servidor externo através do pipeline de deploy.

### Ausentes

- testes de integração para solicitação pública de sessão;
- testes de sobreposição de slots e agendamentos;
- testes para migração de nomes de tabela em inglês/português.

### Falhando

- Execução local de `php artisan test` é inviável no dev container devido à falta de `libcrypto.so.1.1` (OpenSSL 1.1.1), sendo delegada ao pipeline de CI/CD externo.

---

## Últimas alterações importantes

- 2026-09-01 — documentação do projeto foi atualizada para refletir o contexto real e o estado atual do repositório;
- 2026-09-01 — avaliação inicial do código revelou o fluxo de agendamento, slots e compatibilidade de banco de dados.

---

## Decisões recentes

- ADR-001 — compatibilidade com tabelas em português e inglês durante transição de nomenclatura;
- ADR-002 — uso de um formulário público e de um calendário administrativo para gestão de disponibilidade e agendamento.

---

## Próximo passo recomendado

1. validar e corrigir inconsistências de nomenclatura no banco e nos relacionamentos de modelos e controllers (ex: `ProntuarioController` x modelo `Prontuario`);
2. analisar a lógica de conflitos de horários em solicitações e slots;
3. validar o fluxo de deploy e verificar os logs de execução no servidor externo se disponíveis.

---

## Bloqueios

- documentação do projeto ainda em template incompleto e não refletindo a realidade do código;
- risco de regressão por nomes de tabela e colunas divergentes.

---

## Observações para o próximo agente

O projeto tem a estrutura principal do domínio já implementada, mas o ambiente atual impede a validação real. O próximo agente deve priorizar a correção do ambiente e a execução de testes antes de fazer qualquer mudança funcional mais ampla.