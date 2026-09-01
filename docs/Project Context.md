# Project Context

## 1. Visão geral

### Nome

Psica

### Objetivo

Gerenciar pacientes, prontuários e agendamentos de sessões clínicas em uma aplicação web Laravel com calendário de disponibilidade.

### Problema

O projeto resolve a necessidade de registrar pacientes, acompanhar prontuários e receber/organizar solicitações de sessão com validação de disponibilidade e visualização em calendário.

### Usuários

- pacientes que solicitam uma sessão pelo formulário público;
- profissionais/analistas que gerenciam horários, pacientes e prontuários;
- administradores que revisam o dashboard principal.

---

## 2. Contexto

Este é um projeto Laravel 11 com autenticação Breeze, estruturado para uso clínico e agendamento. O código indica uma evolução de um protótipo inicial em inglês para uma nomenclatura mais consistente em português, mas ainda há compatibilidade com tabelas e colunas em inglês em migrações e modelos.

A aplicação possui duas frentes: uma pública para pedido de sessão e outra autenticada para gestão do backoffice.

---

## 3. Objetivos

### Objetivo principal

Centralizar a gestão de agendamentos clínicos e o histórico de pacientes/prontuários em uma única aplicação web.

### Objetivos secundários

- permitir que pacientes solicitem sessões sem login;
- exibir a disponibilidade em calendário para o profissional;
- manter registros de pacientes e prontuários;
- evitar conflitos de horários na criação de agendamentos;
- preservar compatibilidade com nomes de tabela antigos em inglês.

---

## 4. Requisitos

### Funcionais

- cadastro e listagem de pacientes;
- cadastro e edição de prontuários vinculados a pacientes;
- criação, edição e visualização de agendamentos;
- criação de slots de disponibilidade por profissional;
- solicitação pública de sessão com base em nome, telefone e data/hora;
- exibição de agendamentos e slots em calendário FullCalendar;
- API pública para criação de agendamentos vindos do calendário.

### Não funcionais

- aplicação em Laravel com arquitetura MVC simples;
- interface em Blade + Tailwind;
- uso de banco relacional com migrations;
- suporte a múltiplos nomes de tabela/coluna para migração gradual.

---

## 5. Restrições

- a base de código mantém compatibilidade com nomenclatura em inglês e em português, o que aumenta o risco de inconsistências em runtime;
- o ambiente local atual falha ao rodar `php artisan test` porque o binário PHP instalado não consegue carregar a biblioteca OpenSSL 1.1.1 exigida;
- a solução de slots e agendamentos depende de regras de conflito em timestamps e de compatibilidade com `DateTime` / `datetime-local` do navegador.

---

## 6. Premissas

O projeto assume que:

- há um profissional ou analista autenticado para gerenciar slots e registros internos;
- os pacientes podem ser identificados por telefone em solicitações públicas;
- o calendário representa disponibilidade em horários diários limitados (entre 14:00 e 21:00);
- o Banco de Dados será utilizado com diferentes convenções de nomes em transição.

---

## 7. Conceitos importantes

- Paciente: registro do indivíduo atendido, com dados pessoais e contato;
- Prontuário: documento clínico relacionado a um paciente;
- Agendamento: marcação de sessão em datetime específico;
- Slot: intervalo de disponibilidade do profissional, podendo estar livre ou ocupado;
- Solicitação pública: fluxo de inscrição sem autenticação, often by phone matching.

---

## 8. Decisões de produto

- a aplicação oferece um formulário público para agendamento sem login;
- o painel administrativo usa calendário para visualizar agendamentos/slots;
- a nomenclatura de domínio foi escolhida em português, mas a compatibilidade com termos em inglês foi preservada.

---

## 9. O que NÃO deve ser feito

- não remover a compatibilidade de migração em massa sem analisar impactos aos dados existentes;
- não assumir que os nomes de tabela já estão unificados em português;
- não ignorar a validação de conflito de horários em agendamentos.

---

## 10. Questões em aberto

- o ambiente de execução local precisa de um PHP compatível com a biblioteca OpenSSL do container;
- a estratégia de compatibilidade de banco de dados ainda deve ser validada em execução real, especialmente para `patient_id` vs `paciente_id`;
- a consistência entre `ProntuarioController` e o relacionamento `paciente` deve ser conferida em testes de integração.

---

## 11. Fonte deste contexto

- código do projeto em Laravel;
- rotas em `routes/web.php`;
- modelos em `app/Models`;
- migrations em `database/migrations`;
- templates e JS em `resources/`;
- tentativa de execução `php artisan test` no ambiente atual.

---

## Regra de manutenção

Este documento deve conter conhecimento relativamente estável. Informações temporárias devem preferencialmente ficar em `CURRENT_STATE.md` ou `WORK_LOG.md`.