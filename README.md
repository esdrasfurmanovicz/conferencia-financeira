# Conferência Financeira

Checklist mensal para o time financeiro conferir recebimentos de clientes, pensado para
substituir a planilha que normalmente faz esse trabalho. Feito em Laravel do zero, como peça de
portfólio.

## O problema que resolve

Times financeiros costumam manter uma planilha mensal com uma linha por cliente/fatura a conferir:
alguém marca "ok" na coluna, mas não há histórico de quem conferiu, quando, nem trava contra edição
depois de fechado o mês. Este projeto resolve isso com:

- **Conferência mensal**: abra o mês, carregue todos os clientes ativos (ou escolha alguns, ou copie
  as linhas do mês anterior), e vá marcando cada um como conferido.
- **Fechamento auditável**: fechar o mês trava edição; reabrir exige um motivo. Tudo fica num
  histórico de eventos (quem, quando, quantos itens estavam conferidos).
- **Simulador de retenção de impostos**: cada cliente tem um perfil de retenção; cada simulação usa
  uma tabela de alíquotas. O cálculo aplica uma regra comum na prática: alguns impostos só são
  retidos se o valor (ou a soma de um grupo de impostos pequenos) passar de um piso mínimo.

## Domínio modelado

| Conceito | O que é |
|---|---|
| `Levy` (imposto) | Catálogo de impostos, com grupo e piso de retenção opcionais |
| `RateCard` (tabela de alíquota) | Preset de % por imposto, o que hoje seria um "tipo de serviço" |
| `WithholdingProfile` (perfil de retenção) | Por imposto: se aplica ao cliente e se é retido |
| `Client` (cliente) | Vinculado a um perfil de retenção |
| `LevyEstimate` (simulação) | Resultado congelado de um cálculo; histórico, não pertence a um mês |
| `Reconciliation` / `ReconciliationItem` / `ReconciliationEvent` | A conferência mensal, seus itens e a trilha de fechamentos/reaberturas |

A regra de cálculo está isolada em `app/Services/LevyCalculator.php`.

## Stack

Laravel 12 + Blade + Tailwind (Breeze), SQLite por padrão (zero configuração para rodar local).
Login simples (sem verificação de e-mail), é uma ferramenta para uso interno de um time pequeno,
não um produto multiusuário com papéis e permissões.

## Rodando localmente

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Login de demonstração (criado pelo seeder): `demo@conferencia.app` / `demo1234`.

## Fora do escopo (de propósito)

Multi-tenancy, permissões granulares e contratos com vigência/versionamento ficaram de fora para
manter o projeto do tamanho de uma peça de portfólio, focada na ideia central: um checklist
auditável no lugar de planilha.
