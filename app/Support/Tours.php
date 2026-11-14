<?php

namespace App\Support;

/**
 * Conteúdo dos tours guiados e do resumo de cada tela (botão Ajuda).
 *
 * Cada passo aponta para um elemento da tela:
 *  - 'nome'            -> elemento com data-tour="nome"
 *  - '[css]' / '#id'   -> qualquer seletor CSS (começa com [, # ou .)
 *  - null              -> cartão centralizado, sem destaque
 * Chaves opcionais: optional (pula o passo se o elemento não existir), wrap (destaca o
 * elemento pai, útil para campo + rótulo), sidebar (abre o menu no celular),
 * before/detail (evento disparado antes do passo), cta (botão de ação no cartão).
 */
class Tours
{
    public static function welcome(): array
    {
        return [
            'id' => 'welcome',
            'title' => 'Boas-vindas',
            'steps' => [
                self::step(null, 'Bem-vindo à conferência financeira', 'Aqui você confere, mês a mês, os valores e os impostos de cada cliente e fecha o período com tudo registrado. Vamos passear pelo menu para você saber onde fica cada coisa.'),
                self::step('nav-conferencia', 'Conferência mensal', 'É aqui que você trabalha todo mês: abre o mês, simula os impostos de cada cliente, marca como conferido e fecha o período.', ['sidebar' => true]),
                self::step('nav-cadastros', 'Cadastros: configure uma vez', 'Estes quatro cadastros alimentam a simulação de impostos. Faça de cima para baixo e só volte aqui quando algo mudar.', ['sidebar' => true]),
                self::step('nav-impostos', '1. Impostos', 'A lista de impostos que existem (ISS, PIS, COFINS, IRRF...). Os principais já vêm cadastrados. Cadastre só o que estiver faltando.', ['sidebar' => true]),
                self::step('nav-tabelas', '2. Tabelas de alíquota', 'Dizem quanto cada imposto cobra, em %. Por exemplo, uma tabela "Consultoria" com ISS 5% e PIS 0,65%. Você escolhe a tabela ao simular.', ['sidebar' => true]),
                self::step('nav-perfis', '3. Perfis de retenção', 'Dizem, para cada tipo de cliente (Lucro Real, Simples, pessoa física...), quais impostos incidem e quais o cliente retém. Os mais comuns já vêm prontos.', ['sidebar' => true]),
                self::step('nav-clientes', '4. Clientes', 'Cada cliente recebe um perfil de retenção. Os clientes ativos entram na conferência do mês.', ['sidebar' => true]),
                self::step('nav-usuarios', 'Usuários (só administradores)', 'Você é administrador: aqui vê quem tem acesso, edita dados e perfil, troca senhas e remove contas.', ['sidebar' => true, 'optional' => true]),
                self::step('help-button', 'Ajuda em qualquer tela', 'Este botão mostra um resumo da tela em que você está e tours específicos, como "Como simular impostos". Dá para refazer este passeio por ele também.'),
                self::step(null, 'Tudo pronto para começar', 'O caminho é: cadastros prontos, abrir o mês, conferir cliente por cliente e fechar. Se quiser, comece pelo tour da própria Conferência mensal.', [
                    'cta' => ['label' => 'Ir para a conferência mensal', 'url' => route('reconciliations.index')],
                ]),
            ],
        ];
    }

    /** Resumo + tours da tela atual, pelo nome da rota. */
    public static function forRoute(?string $routeName): ?array
    {
        if (! $routeName) {
            return null;
        }

        // create/edit/store/update dos cadastros compartilham o mesmo formulário e o mesmo tour
        $key = preg_replace('/^(clients|levies|rate-cards|withholding-profiles|users)\.(create|edit|store|update|password)$/', '$1.form', $routeName);

        $page = self::pages()[$key] ?? null;
        if (! $page) {
            return null;
        }

        foreach ($page['tours'] as $i => $tour) {
            $page['tours'][$i]['id'] = $key.':'.$tour['id'];
        }

        return $page;
    }

    private static function pages(): array
    {
        return [
            'reconciliations.index' => [
                'title' => 'Conferência mensal',
                'overview' => 'Aqui você confere, mês a mês, o valor e os impostos de cada cliente. Cada linha é um cliente: simule os impostos, marque como conferido e, quando tudo estiver certo, feche o mês.',
                'points' => [
                    'Troque de mês nas setas ou no seletor.',
                    'Abra o mês carregando os clientes ativos, copiando o mês anterior ou começando do zero.',
                    'O círculo marca o cliente como conferido, com seu nome e a hora.',
                    'Mês fechado fica protegido. Para corrigir, reabra informando o motivo.',
                ],
                'tours' => [
                    ['id' => 'overview', 'title' => 'Conhecer esta tela', 'steps' => [
                        self::step('month', 'Escolha o mês', 'Use as setas ou o seletor para ir de um mês a outro. Cada mês tem a sua própria conferência.'),
                        self::step('populate', 'Abra o mês', 'Este mês ainda não foi aberto. Carregue todos os clientes ativos, copie os itens do mês anterior ou comece do zero e adicione um por um.', ['optional' => true]),
                        self::step('progress', 'Acompanhe o progresso', 'Mostra quantos clientes já foram conferidos. Quando chega a 100%, o sistema avisa que dá para fechar.', ['optional' => true]),
                        self::step('month-actions', 'Situação do mês', 'Mostra se o mês está aberto ou fechado. Daqui você fecha ou reabre o mês e vê o Histórico de quem fechou e reabriu.', ['optional' => true]),
                        self::step('filters', 'Encontre clientes', 'Busque pelo nome ou mostre só os pendentes ou só os conferidos.', ['optional' => true]),
                        self::step('add-actions', 'Faltou alguém?', 'Adicionar item inclui um cliente específico. Carregar mais traz os clientes ativos que ainda não estão neste mês.', ['optional' => true]),
                        self::step('items-table', 'Os clientes do mês', 'Cada linha mostra o perfil do cliente e, depois de simular, a base, os impostos, o valor retido e o líquido.', ['optional' => true]),
                        self::step('tick', 'Marque como conferido', 'Clique no círculo quando o cliente estiver certo. Clique de novo para desmarcar, desde que o mês esteja aberto.', ['optional' => true]),
                        self::step('simulate', 'Simule os impostos', 'Abre o simulador para este cliente. Para ver o passo a passo, use Ajuda > Como simular impostos.', ['optional' => true]),
                    ]],
                    ['id' => 'simulate', 'title' => 'Como simular impostos', 'after' => 'tour-estimate-close', 'steps' => [
                        self::step(null, 'Simulando os impostos de um cliente', 'Vou abrir o simulador com um exemplo para você ver cada campo. Na prática, você clica em Simular na linha do cliente. Os valores do exemplo são fictícios.', ['before' => 'tour-estimate-open']),
                        self::step('[x-model="estimate.rateCardId"]', 'Escolha a tabela de alíquota', 'A tabela define quanto cada imposto cobra. Se a lista estiver vazia, cadastre uma em Tabelas de alíquota.', ['wrap' => true]),
                        self::step('[x-model="estimate.profileId"]', 'Escolha o perfil de retenção', 'O perfil diz o que incide e o que o cliente retém. Se o cliente já tem perfil, ele vem preenchido.', ['wrap' => true]),
                        self::step('[x-model="estimate.amount"]', 'Informe o valor', 'O valor bruto da nota ou do serviço, em reais. O cálculo roda sozinho assim que a tabela, o perfil e o valor estão preenchidos.', ['wrap' => true]),
                        self::step('est-result', 'Leia o resultado', 'Total de impostos, quanto o cliente retém e o valor líquido que você recebe. A tabela abaixo mostra cada imposto, a alíquota e se ele foi retido.'),
                        self::step('est-save', 'Salve a simulação', 'Salvar guarda o resultado no item do cliente e no histórico dele. O botão Histórico, na linha do cliente, lista as simulações anteriores.'),
                    ]],
                    ['id' => 'close', 'title' => 'Como fechar o mês', 'steps' => [
                        self::step(null, 'Fechando o mês', 'Quando todos os clientes estiverem conferidos, você fecha o mês. A partir daí ninguém confirma, adiciona, edita ou remove itens por engano.'),
                        self::step('progress', '1. Confira o progresso', 'O ideal é chegar a 100% antes de fechar.', ['optional' => true]),
                        self::step('tick', '2. Marque os que faltam', 'Clique no círculo de cada cliente já verificado.', ['optional' => true]),
                        self::step('month-actions', '3. Feche o mês', 'Clique em Fechar mês e confirme. Se precisar corrigir algo depois, use Reabrir e informe o motivo. Tudo fica no Histórico.', ['optional' => true]),
                        self::step('populate', 'Este mês ainda não foi aberto', 'Primeiro abra o mês, carregando os clientes. Depois volte a este tour.', ['optional' => true]),
                    ]],
                ],
            ],

            'clients.index' => [
                'title' => 'Clientes',
                'overview' => 'A lista de quem você atende. Os clientes ativos entram na conferência do mês, e o perfil de retenção de cada um define como os impostos são calculados.',
                'points' => [
                    'Busque pelo nome.',
                    'Para tirar um cliente das próximas conferências, edite e desmarque Ativo.',
                    'Remover apaga o cadastro.',
                ],
                'tours' => [
                    ['id' => 'overview', 'title' => 'Conhecer esta tela', 'steps' => [
                        self::step('search', 'Busque pelo nome', 'Digite parte do nome e tecle Enter.'),
                        self::step('add-button', 'Cadastre um cliente', 'Abre o formulário com nome, CPF/CNPJ e perfil de retenção.'),
                        self::step('table', 'Sua lista de clientes', 'Mostra o documento, o perfil de retenção e se o cliente está ativo.'),
                        self::step('row-actions', 'Editar ou remover', 'Editar muda os dados, inclusive o perfil. Remover pede confirmação antes de apagar.', ['optional' => true]),
                    ]],
                ],
            ],

            'clients.form' => [
                'title' => 'Cadastro de cliente',
                'overview' => 'Preencha os dados do cliente e escolha o perfil de retenção dele. Esse perfil vem preenchido ao simular os impostos.',
                'points' => ['Só o nome é obrigatório.', 'Sem perfil, você escolhe um na hora de simular.', 'Desmarque Ativo para tirar o cliente das próximas conferências.'],
                'tours' => [
                    ['id' => 'overview', 'title' => 'Como preencher', 'steps' => [
                        self::step('#name', 'Nome do cliente', 'É assim que ele aparece na conferência.', ['wrap' => true]),
                        self::step('#document', 'CPF ou CNPJ', 'Opcional, mas ajuda a identificar clientes com nomes parecidos.', ['wrap' => true]),
                        self::step('#withholding_profile_id', 'Perfil de retenção', 'Escolha o tipo do cliente (Lucro Real, Simples, pessoa física...). Ele define quais impostos o cliente retém na simulação.', ['wrap' => true]),
                        self::step('[name="active"]', 'Cliente ativo', 'Clientes ativos são carregados na conferência do mês.', ['wrap' => true]),
                    ]],
                ],
            ],

            'levies.index' => [
                'title' => 'Impostos',
                'overview' => 'O catálogo de impostos que podem entrar em uma tabela de alíquota ou em um perfil de retenção. Os principais já vêm cadastrados.',
                'points' => ['Cadastre só o que estiver faltando.', 'Grupo e piso de retenção são opcionais.', 'Remover apaga o imposto e as regras que usam ele.'],
                'tours' => [
                    ['id' => 'overview', 'title' => 'Conhecer esta tela', 'steps' => [
                        self::step('add-button', 'Faltou algum imposto?', 'Cadastre aqui. Só precisa de um código e um nome.'),
                        self::step('table', 'O catálogo', 'Além do código e do nome, a lista mostra o grupo e o piso de retenção, quando houver.'),
                        self::step('row-actions', 'Editar ou remover', 'Editar ajusta nome, grupo e piso. Remover pede confirmação.', ['optional' => true]),
                    ]],
                ],
            ],

            'levies.form' => [
                'title' => 'Cadastro de imposto',
                'overview' => 'Informe o código e o nome. Grupo e piso de retenção só são necessários para impostos que são retidos juntos, a partir de um valor mínimo.',
                'points' => ['Exemplo: PIS, COFINS e CSLL no grupo "federais" com piso de R$ 10 só são retidos se somarem esse valor.'],
                'tours' => [
                    ['id' => 'overview', 'title' => 'Como preencher', 'steps' => [
                        self::step('#code', 'Código', 'A sigla do imposto, como ISS ou PIS. Precisa ser única.', ['wrap' => true]),
                        self::step('#name', 'Nome', 'O nome por extenso, como aparece nas telas.', ['wrap' => true]),
                        self::step('#withholding_group', 'Grupo de retenção', 'Opcional. Impostos do mesmo grupo têm os valores somados antes de verificar o piso.', ['wrap' => true]),
                        self::step('#withholding_floor', 'Piso de retenção', 'Opcional. Se a soma do grupo ficar abaixo deste valor, nada do grupo é retido.', ['wrap' => true]),
                        self::step('[name="active"]', 'Ativo', 'Impostos inativos não entram nas simulações.', ['wrap' => true]),
                    ]],
                ],
            ],

            'rate-cards.index' => [
                'title' => 'Tabelas de alíquota',
                'overview' => 'Cada tabela reúne os impostos e as alíquotas (%) de um tipo de serviço. Você escolhe a tabela ao simular os impostos de um cliente.',
                'points' => ['Crie uma tabela por tipo de serviço.', 'Depois de salvar, adicione os impostos e as alíquotas dentro da tabela.'],
                'tours' => [
                    ['id' => 'overview', 'title' => 'Conhecer esta tela', 'steps' => [
                        self::step('add-button', 'Crie uma tabela', 'Dê um nome, como "Consultoria". Os impostos entram na tela seguinte.'),
                        self::step('table', 'Suas tabelas', 'Mostra quantos impostos cada tabela tem e se ela está ativa.'),
                        self::step('row-actions', 'Editar', 'É dentro de Editar que você adiciona os impostos e as alíquotas da tabela.', ['optional' => true]),
                    ]],
                ],
            ],

            'rate-cards.form' => [
                'title' => 'Tabela de alíquota',
                'overview' => 'Primeiro salve o nome da tabela. Depois, nesta mesma tela, adicione cada imposto com a sua alíquota e, se quiser, o período em que ela vale.',
                'points' => ['Vigência é opcional: use quando a alíquota muda em uma data.', 'Cada imposto aparece uma vez por tabela.'],
                'tours' => [
                    ['id' => 'overview', 'title' => 'Como montar a tabela', 'steps' => [
                        self::step('#name', 'Nome da tabela', 'Algo que identifique o serviço, como "Consultoria" ou "Obras".', ['wrap' => true]),
                        self::step('[name="active"]', 'Ativa', 'Só tabelas ativas aparecem no simulador.', ['wrap' => true]),
                        self::step('rc-items', 'Impostos da tabela', 'Depois de salvar, os impostos desta tabela aparecem aqui, com alíquota e vigência.', ['optional' => true]),
                        self::step('rc-add-item', 'Adicione um imposto', 'Escolha o imposto, informe a alíquota em % e, se precisar, a vigência. Clique em Adicionar.', ['optional' => true]),
                    ]],
                ],
            ],

            'withholding-profiles.index' => [
                'title' => 'Perfis de retenção',
                'overview' => 'Um perfil descreve um tipo de cliente: quais impostos incidem e quais ele retém na hora de pagar. Os mais comuns já vêm prontos.',
                'points' => ['Revise as regras dos perfis prontos antes de usar com clientes reais.', 'Cada cliente usa um perfil.'],
                'tours' => [
                    ['id' => 'overview', 'title' => 'Conhecer esta tela', 'steps' => [
                        self::step('add-button', 'Crie outro perfil', 'Útil para um tipo de cliente que não está na lista.'),
                        self::step('table', 'Os perfis', 'Mostra quantos clientes usam cada perfil.'),
                        self::step('row-actions', 'Editar as regras', 'É em Editar que você define, imposto por imposto, o que incide e o que é retido.', ['optional' => true]),
                    ]],
                ],
            ],

            'withholding-profiles.form' => [
                'title' => 'Perfil de retenção',
                'overview' => 'Dê um nome ao perfil e, depois de salvar, marque para cada imposto se ele se aplica e se o cliente o retém.',
                'points' => ['Se aplica: o imposto incide nas notas desse tipo de cliente.', 'É retido: o cliente desconta o imposto antes de pagar você.'],
                'tours' => [
                    ['id' => 'overview', 'title' => 'Como montar o perfil', 'steps' => [
                        self::step('#name', 'Nome do perfil', 'O tipo de cliente, como "Lucro Real" ou "Pessoa física".', ['wrap' => true]),
                        self::step('rules', 'Regras por imposto', 'Marque Se aplica nos impostos que incidem. Marque É retido nos que o cliente desconta do pagamento.', ['optional' => true]),
                        self::step('[name="active"]', 'Ativo', 'Perfis inativos não aparecem no simulador.', ['wrap' => true]),
                    ]],
                ],
            ],

            'users.index' => [
                'title' => 'Usuários',
                'overview' => 'A lista de quem tem acesso ao sistema. Só administradores veem esta tela. Administrador gerencia as contas; usuário só usa a conferência e os cadastros.',
                'points' => [
                    'Busque por nome ou e-mail.',
                    'Em Editar você muda nome, e-mail, perfil de acesso e a senha.',
                    'Remover apaga a conta e tira o acesso na hora. O histórico de conferências fica, sem o nome da pessoa.',
                    'O sistema sempre mantém pelo menos um administrador.',
                ],
                'tours' => [
                    ['id' => 'overview', 'title' => 'Conhecer esta tela', 'steps' => [
                        self::step('search', 'Encontre alguém', 'Digite parte do nome ou do e-mail e tecle Enter.'),
                        self::step('table', 'Quem tem acesso', 'Cada linha mostra o e-mail, o perfil (Administrador ou Usuário) e a data de cadastro. A sua própria linha tem a marca "Você".'),
                        self::step('row-actions', 'Editar ou remover', 'Editar abre os dados e a troca de senha. Remover pede confirmação. Você não vê Remover na sua própria linha.', ['optional' => true]),
                    ]],
                ],
            ],

            'users.form' => [
                'title' => 'Editar usuário',
                'overview' => 'Ajuste os dados e o perfil de acesso da pessoa, ou defina uma senha nova para ela.',
                'points' => ['Ao trocar a senha de outra pessoa, ela precisa entrar de novo.', 'Você não muda o seu próprio perfil de acesso.'],
                'tours' => [
                    ['id' => 'overview', 'title' => 'Como editar', 'steps' => [
                        self::step('#name', 'Nome e e-mail', 'São os dados que aparecem no histórico de conferências.', ['wrap' => true]),
                        self::step('#role', 'Perfil de acesso', 'Administrador gerencia usuários. Usuário só usa a conferência e os cadastros.', ['wrap' => true]),
                        self::step('user-password', 'Alterar a senha', 'Defina uma senha nova e confirme. Combine com a pessoa como ela vai recebê-la.'),
                    ]],
                ],
            ],

            'profile.edit' => [
                'title' => 'Meu perfil',
                'overview' => 'Seus dados de acesso: nome, e-mail, senha e exclusão da conta.',
                'points' => ['A exclusão da conta pede a sua senha.'],
                'tours' => [
                    ['id' => 'overview', 'title' => 'Conhecer esta tela', 'steps' => [
                        self::step('profile-info', 'Nome e e-mail', 'Atualize os dados que aparecem no sistema e no histórico de conferências.'),
                        self::step('profile-password', 'Senha', 'Troque a senha informando a atual e a nova.'),
                        self::step('profile-delete', 'Excluir conta', 'Apaga a sua conta. Pede a senha para confirmar.'),
                    ]],
                ],
            ],
        ];
    }

    private static function step(?string $target, string $title, string $body, array $extra = []): array
    {
        return ['target' => $target, 'title' => $title, 'body' => $body] + $extra;
    }
}
