{{-- Botão de ajuda flutuante + painel lateral: resumo desta tela, tours e dúvidas comuns. --}}
@props(['page' => null])

<div x-data="{ open: false }" x-on:open-help.window="open = true" x-on:keydown.escape.window="open = false">
    <button type="button" @click="open = true"
        data-tour="help-button"
        class="fixed bottom-5 right-5 z-30 flex items-center gap-2 rounded-full bg-ink py-3 pl-4 pr-5 text-sm font-bold text-white shadow-lift transition-colors hover:bg-ink-soft"
        aria-label="Abrir ajuda">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/></svg>
        Ajuda
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-labelledby="help-title">
        <div class="overlay" style="z-index: 0" x-show="open" x-transition.opacity @click="open = false"></div>

        <aside class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-white shadow-lift"
            x-show="open"
            x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">

            <div class="flex items-start justify-between gap-4 border-b border-rule p-6">
                <div>
                    <h2 id="help-title" class="text-xl font-extrabold tracking-tight">Como usar a conferência</h2>
                    <p class="mt-1 text-sm text-ink-mute">O caminho de um mês, do começo ao fechamento.</p>
                </div>
                <button type="button" class="rounded-lg p-1.5 text-ink-mute hover:bg-ink/5 hover:text-ink" @click="open = false" aria-label="Fechar ajuda">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>

            <div class="flex-1 space-y-6 overflow-y-auto p-6">
                @if ($page)
                    <section class="rounded-2xl bg-paper p-5">
                        <h3 class="text-base font-extrabold tracking-tight">Nesta tela: {{ $page['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-ink-mute">{{ $page['overview'] }}</p>
                        <ul class="mt-3 space-y-1.5 text-sm">
                            @foreach ($page['points'] as $point)
                                <li class="flex gap-2.5">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-ok" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                                    <span>{{ $point }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-4 space-y-2">
                            @foreach ($page['tours'] as $tour)
                                <button type="button" class="{{ $loop->first ? 'btn-primary' : 'btn-secondary' }} w-full !justify-start"
                                    @click="open = false; $nextTick(() => $dispatch('start-tour', { id: @js($tour['id']) }))">
                                    <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.14v13.72a1 1 0 001.5.86l11-6.86a1 1 0 000-1.72l-11-6.86A1 1 0 008 5.14z"/></svg>
                                    Tour: {{ $tour['title'] }}
                                </button>
                            @endforeach
                        </div>
                    </section>
                @endif

                <button type="button" class="btn-secondary w-full" @click="open = false; $nextTick(() => $dispatch('start-tour', { id: 'welcome' }))">Rever o tour de boas-vindas</button>

                <section>
                    <h3 class="mb-3 text-sm font-bold">Passo a passo do mês</h3>
                    <ol class="space-y-3 text-sm">
                        @foreach ([
                            ['Cadastros prontos', 'Impostos, tabelas de alíquota, perfis de retenção e clientes, nesta ordem. Os impostos e perfis mais comuns já vêm prontos. Só mexa quando algo mudar.'],
                            ['Abrir o mês', 'Em Conferência mensal, escolha o mês e carregue os clientes ativos, copie do mês anterior ou comece do zero.'],
                            ['Conferir', 'Use Simular para calcular os impostos e marque o círculo do cliente quando estiver certo.'],
                            ['Fechar', 'Quando tudo estiver conferido, clique em Fechar mês.'],
                        ] as $i => [$t, $d])
                            <li class="flex gap-3">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-ok-soft text-xs font-bold text-ok-ink">{{ $i + 1 }}</span>
                                <p><span class="font-bold">{{ $t }}.</span> <span class="text-ink-mute">{{ $d }}</span></p>
                            </li>
                        @endforeach
                    </ol>
                </section>

                <section>
                    <h3 class="mb-3 text-sm font-bold">Dúvidas comuns</h3>
                    <div class="divide-y divide-rule rounded-xl border border-rule" x-data="{ faq: null }">
                        @foreach ([
                            ['O círculo não deixa marcar.', 'O mês provavelmente está fechado. Clique em Reabrir e informe o motivo; ele fica no histórico.'],
                            ['Simular não mostra resultado.', 'Escolha a tabela de alíquota e o perfil de retenção e informe o valor. Os três são necessários.'],
                            ['Faltou um cliente na lista.', 'Use Adicionar item para um cliente específico ou Carregar mais para trazer os ativos que ainda não estão no mês.'],
                            ['Como desfazer uma conferência?', 'Clique de novo no círculo do cliente para desmarcar, desde que o mês esteja aberto.'],
                            ['Onde vejo quem conferiu?', 'Na coluna Conferido em, com nome e horário. O botão Histórico mostra os fechamentos e reaberturas.'],
                        ] as $i => [$q, $a])
                            <div>
                                <button type="button" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold hover:bg-paper"
                                    @click="faq = faq === {{ $i }} ? null : {{ $i }}" :aria-expanded="faq === {{ $i }}">
                                    {{ $q }}
                                    <svg class="h-4 w-4 shrink-0 text-ink-mute transition-transform" :class="faq === {{ $i }} && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                                </button>
                                <p x-show="faq === {{ $i }}" x-cloak class="px-4 pb-4 text-sm leading-relaxed text-ink-mute">{{ $a }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </aside>
    </div>
</div>
