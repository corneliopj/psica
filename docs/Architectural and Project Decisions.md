# Architectural and Project Decisions

Este arquivo registra decisões importantes que devem sobreviver à troca de agentes.

Não registre todas as pequenas escolhas de implementação.

---

# ADR-001 — Compatibilidade com nomenclatura em português e em inglês no banco de dados

**Status:** aceita

**Data:** 2026-09-01

## Contexto

A aplicação foi estruturada em português (`pacientes`, `prontuarios`, `agendamentos`), porém há evidências de migrações e scripts antigos que trabalhavam com nomes em inglês (`patients`, `appointments`, `patient_id`).

## Problema

Precisava-se manter compatibilidade com dados e estrutura pré-existentes sem quebrar a nova convenção em português.

## Alternativas consideradas

### Alternativa A

Migrar tudo para português e remover suporte ao inglês de uma vez.

### Alternativa B

Manter uma camada de compatibilidade dinâmica em modelos e migrações, permitindo transição gradual.

## Decisão

Foi adotada a compatibilidade dinâmica: modelos verificam a presença de tabelas/colunas em português antes de usar a convenção alternativa; migrações e código guardam essa bifurcação.

## Justificativa

Essa abordagem reduz o risco de quebrar dados antigos e preserva o trabalho em progresso de transição de nomenclatura enquanto o projeto ainda está em desenvolvimento.

## Consequências

### Benefícios

- reduz risco de regressão em dados legados;
- permite evolução gradual da base;
- preserva API do domínio em português.

### Custos / riscos

- aumenta a complexidade do código;
- pode mascarar inconsistências reais de banco;
- exige validação constante em execução real.

## Impacto

Componentes afetados: `app/Models/Paciente.php`, `app/Models/Prontuario.php`, `app/Models/Agendamento.php`, migrações em `database/migrations/` e scripts legados em `database/migrations/disabled/`.

---

# ADR-002 — Fluxo público de solicitação de sessão e gestão interna com calendário

**Status:** aceita

**Data:** 2026-09-01

## Contexto

A aplicação precisa receber solicitações de sessão sem autenticação e, ao mesmo tempo, permitir que o profissional administre horários e disponibilidade internamente.

## Problema

A organização do processo de agendamento exigia um fluxo público simples para o paciente e uma interface administrativa para controle do calendário.

## Alternativas consideradas

### Alternativa A

Usar apenas CRUD interno sem entrada pública.

### Alternativa B

Combinar formulário público com calendário administrativo e API de integração.

## Decisão

A solução adotada combina:

- formulário público em Blade para solicitar sessão;
- rota `/api/agendamentos` e `/api/slots` para exibição no calendário;
- dashboard autenticado com FullCalendar para gerenciamento de slots e consultas;
- criação via API do calendário com conflito de horários.

## Justificativa

Essa estrutura resolve simultaneamente a conveniência do paciente e a necessidade de controle do analista/profissional, mantendo um único modelo de dados para agenda.

## Consequências

### Benefícios

- melhora UX para o paciente;
- centraliza disponibilidade em calendário;
- facilita visualização do estado da agenda.

### Custos / riscos

- aumenta a complexidade de validação de sobreposição de horários;
- exige consistência entre front-end e back-end;
- depende de regras de negócio bem definidas para status de slots.

## Impacto

Componentes afetados: `routes/web.php`, `app/Http/Controllers/SolicitacaoController.php`, `app/Http/Controllers/SlotController.php`, `resources/js/calendar.js`, `resources/views/solicitar.blade.php`, `resources/views/dashboard.blade.php`.

---

## Status possíveis

- `proposta`
- `aceita`
- `substituída`
- `rejeitada`
- `obsoleta`

## Regra

Uma decisão substituída não deve ser simplesmente apagada. Registre a nova decisão e indique qual decisão anterior ela substitui. Isso preserva a memória arquitetural do projeto.