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

Laravel 13 + Blade + Tailwind + Alpine.js (base do Breeze), SQLite por padrão (zero configuração para
rodar local). Interface toda em português do Brasil.

## Acesso e perfis

Login simples (sem verificação de e-mail), pensado para uso interno de um time pequeno. Há dois
perfis: **Administrador**, que vê a lista de usuários e pode editar, trocar a senha e remover contas,
e **Usuário**, que usa a conferência e os cadastros. A primeira conta cadastrada vira administradora,
e o sistema nunca fica sem um. O cadastro aberto vem ligado (`REGISTRATION_ENABLED`) e a recuperação
de senha por e-mail vem desligada (`PASSWORD_RESET_ENABLED`) até o envio de e-mail ser configurado.

## Primeiros passos dentro do app

Os principais impostos e os perfis de retenção mais comuns já vêm cadastrados por migration. No
primeiro acesso, um tour guiado destaca na tela onde fica cada coisa. O botão Ajuda resume a tela
atual e abre tours específicos, como "Como simular impostos". Os textos ficam em `app/Support/Tours.php`.

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

Multi-tenancy, permissões além dos dois perfis e contratos com vigência/versionamento ficaram de fora para
manter o projeto do tamanho de uma peça de portfólio, focada na ideia central: um checklist
auditável no lugar de planilha.

## Licença

MIT. Veja o arquivo [LICENSE](LICENSE).
