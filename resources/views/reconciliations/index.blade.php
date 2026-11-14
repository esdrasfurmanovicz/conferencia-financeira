<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Conferência mensal</h1>
    </x-slot>

    @php
        $monthValue = $period->format('Y-m');
        $percent = $total ? (int) round($confirmed / $total * 100) : 0;
        $closed = (bool) $reconciliation?->isClosed();
    @endphp

    <div class="" x-data="reconciliationPage()" @tour-estimate-open.window="openEstimateDemo()" @tour-estimate-close.window="estimate.open = false">
        <div class="space-y-5">
            <x-flash />

            <div class="panel p-6 sm:p-8">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-4">
                    <div class="flex items-center gap-1.5" data-tour="month">
                        <a class="btn-secondary btn-sm !px-2.5" aria-label="Mês anterior" href="{{ route('reconciliations.index', ['month' => $period->copy()->subMonth()->format('Y-m')]) }}">&lsaquo;</a>
                        <form method="GET" class="relative">
                            <label for="month-picker" class="sr-only">Mês da conferência</label>
                            <input id="month-picker" type="month" name="month" value="{{ $monthValue }}" onchange="this.form.submit()" class="w-56 text-sm font-semibold">
                        </form>
                        <a class="btn-secondary btn-sm !px-2.5" aria-label="Próximo mês" href="{{ route('reconciliations.index', ['month' => $period->copy()->addMonth()->format('Y-m')]) }}">&rsaquo;</a>
                    </div>

                    @if ($reconciliation)
                        <div class="ml-auto flex flex-wrap items-center gap-2" data-tour="month-actions">
                            @if ($closed)
                                <span class="chip-bad">
                                    Fechado em {{ $reconciliation->closed_at?->format('d/m/Y H:i') }}@if ($reconciliation->closedBy) por {{ $reconciliation->closedBy->name }}@endif
                                </span>
                                <button type="button" class="btn-secondary btn-sm" @click="openReopen = true">Reabrir</button>
                            @else
                                <span class="chip-ok">Aberto</span>
                                <button type="button" class="btn-primary btn-sm" @click="closeMonth()">Fechar mês</button>
                            @endif
                            <button type="button" class="btn-secondary btn-sm" @click="loadEvents()">Histórico</button>
                        </div>
                    @endif
                </div>

                @if ($reconciliation)
                    <div class="mt-6" data-tour="progress">
                        <div class="flex items-end justify-between gap-4">
                            <p class="text-sm text-ink-mute">
                                <span class="text-4xl font-extrabold tabular-nums tracking-tight text-ink">{{ $confirmed }}</span>
                                de {{ $total }} conferidos
                            </p>
                            <p class="text-2xl font-bold tabular-nums {{ $percent === 100 ? 'text-ok' : 'text-ink-mute' }}">{{ $percent }}%</p>
                        </div>
                        <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-ink/8" style="background:rgb(18 38 43 / .08)" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100" aria-label="Progresso da conferência">
                            <div class="h-full rounded-full bg-ok transition-[width] duration-500" style="width: {{ $percent }}%"></div>
                        </div>
                        @if ($percent === 100 && ! $closed)
                            <p class="mt-3 text-sm font-semibold text-ok-ink">Tudo conferido. Se estiver certo, é só fechar o mês.</p>
                        @endif
                    </div>
                @endif
            </div>

            @if ($closed)
                <div class="rounded-xl border border-warn/30 bg-warn-soft px-4 py-3 text-sm font-medium text-warn-ink">
                    Este mês está fechado: confirmar, adicionar, editar ou remover itens fica bloqueado até reabrir.
                </div>
            @endif

            @unless ($reconciliation)
                <div class="panel mx-auto max-w-2xl space-y-4 p-8 text-center sm:p-12" data-tour="populate">
                    <h2 class="text-2xl font-extrabold tracking-tight">A conferência de {{ $period->format('m/Y') }} ainda não foi aberta</h2>
                    <p class="mx-auto max-w-md text-ink-mute">Escolha como começar. Você pode adicionar ou remover clientes depois.</p>
                    <div class="flex flex-wrap justify-center gap-2 pt-2">
                        <form method="POST" action="{{ route('reconciliations.populate') }}">
                            @csrf
                            <input type="hidden" name="month" value="{{ $monthValue }}">
                            <input type="hidden" name="mode" value="all">
                            <button class="btn-primary">
                                Carregar todos os clientes ativos ({{ $missingClients }})
                            </button>
                        </form>
                        @if ($previous)
                            <form method="POST" action="{{ route('reconciliations.populate') }}">
                                @csrf
                                <input type="hidden" name="month" value="{{ $monthValue }}">
                                <input type="hidden" name="mode" value="copy">
                                <button class="btn-secondary">Copiar de {{ $previous->period->format('m/Y') }}</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('reconciliations.populate') }}">
                            @csrf
                            <input type="hidden" name="month" value="{{ $monthValue }}">
                            <input type="hidden" name="mode" value="empty">
                            <button class="btn-secondary">Começar do zero</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <form method="GET" class="flex flex-wrap gap-2 items-center" data-tour="filters">
                        <input type="hidden" name="month" value="{{ $monthValue }}">
                        <input type="text" name="name" value="{{ request('name') }}" placeholder="Nome do cliente" class="rounded-lg border-rule text-sm">
                        <select name="status" class="rounded-lg border-rule text-sm">
                            <option value="">Todos</option>
                            <option value="pending" @selected(request('status') === 'pending')>Pendentes</option>
                            <option value="confirmed" @selected(request('status') === 'confirmed')>Conferidos</option>
                        </select>
                        <button class="btn-secondary btn-sm">Filtrar</button>
                    </form>

                    @unless ($closed)
                        <div class="flex gap-2" data-tour="add-actions">
                            <button type="button" class="btn-secondary btn-sm" @click="openAddItem()">Adicionar item</button>
                            <button type="button" class="btn-secondary btn-sm" @click="openLoad = true">Carregar mais</button>
                        </div>
                    @endunless
                </div>

                <div class="table-wrap" data-tour="items-table">
                    <table class="data-table">
                        <thead class="bg-paper/70 text-left text-xs text-ink-mute">
                            <tr>
                                <th class="w-12 py-3 pl-4 pr-0"><span class="sr-only">Conferido</span></th>
                                <th class="px-4 py-5">Cliente</th>
                                <th class="px-4 py-5">Perfil</th>
                                <th class="px-4 py-5 text-right">Base</th>
                                <th class="px-4 py-5 text-right">Impostos</th>
                                <th class="px-4 py-5 text-right">Retido</th>
                                <th class="px-4 py-5 text-right">Líquido</th>
                                <th class="px-4 py-5">Conferido em</th>
                                <th class="px-4 py-5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rule/70">
                            @forelse ($items as $item)
                                @php($estimate = $item->estimate)
                                <tr class="{{ $item->isConfirmed() ? 'bg-ok-soft/50' : '' }}">
                                    <td class="w-12 py-3 pl-4 pr-0">
                                        <button type="button" class="tick" data-tour="tick" role="switch"
                                            aria-checked="{{ $item->isConfirmed() ? 'true' : 'false' }}"
                                            aria-label="{{ $item->isConfirmed() ? 'Desmarcar' : 'Marcar' }} {{ $item->client->name }} como conferido"
                                            title="{{ $item->isConfirmed() ? 'Conferido. Clique para desmarcar' : 'Marcar como conferido' }}"
                                            {{ $closed ? 'disabled' : '' }}
                                            @click="toggleConfirm({{ $item->id }}, {{ $item->isConfirmed() ? 'false' : 'true' }})">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                                        </button>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-5 font-semibold">
                                        {{ $item->client->name }}
                                        @if ($item->label)<div class="text-xs font-normal text-ink-mute">{{ $item->label }}</div>@endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-5">{{ $item->client->withholdingProfile?->name ?? 'Sem perfil' }}</td>
                                    <td class="num px-4 py-5">@if ($estimate){{ 'R$ ' . number_format($estimate->base_amount, 2, ',', '.') }}@else<span class="text-xs text-ink-mute">Sem simulação</span>@endif</td>
                                    <td class="num px-4 py-5">{{ $estimate ? 'R$ ' . number_format($estimate->levies_total, 2, ',', '.') : '' }}</td>
                                    <td class="num px-4 py-5">{{ $estimate ? 'R$ ' . number_format($estimate->withheld_total, 2, ',', '.') : '' }}</td>
                                    <td class="num px-4 py-5 font-bold">{{ $estimate ? 'R$ ' . number_format($estimate->net_amount, 2, ',', '.') : '' }}</td>
                                    <td class="whitespace-nowrap px-4 py-5 text-xs text-ink-mute">
                                        {{ $item->confirmed_at?->format('d/m/Y H:i') }}
                                        @if ($item->confirmedByUser)<div>{{ $item->confirmedByUser->name }}</div>@endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-5 text-right">
                                        <button type="button" class="link-action"
                                            data-tour="simulate" @click="openEstimate({{ $item->id }}, {{ $item->client_id }}, '{{ addslashes($item->client->name) }}', {{ $item->client->withholding_profile_id ?? 'null' }})">Simular</button>
                                        <button type="button" class="link-action" @click="openClientHistory({{ $item->client_id }}, '{{ addslashes($item->client->name) }}')">Histórico</button>
                                        @unless ($closed)
                                            <button type="button" class="link-danger" @click="removeItem({{ $item->id }})">Remover</button>
                                        @endunless
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="px-3 py-12 text-center text-ink-mute">Nenhum item com esses filtros. Limpe a busca ou escolha &ldquo;Todos&rdquo;.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $items->links() }}
            @endunless
        </div>

        {{-- Painel de simulação --}}
        <div x-show="estimate.open" x-cloak class="overlay flex justify-end" @click.self="estimate.open = false">
            <div class="h-full w-full max-w-lg space-y-4 overflow-y-auto bg-white p-6 shadow-lift" data-tour="est-panel">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-bold">Simular impostos</h3>
                        <p class="text-sm text-ink-mute" x-text="estimate.clientName || 'Simulação avulsa'"></p>
                    </div>
                    <button @click="estimate.open = false" class="rounded-lg px-2 text-2xl leading-none text-ink-mute hover:bg-ink/5 hover:text-ink" aria-label="Fechar">&times;</button>
                </div>

                <template x-if="estimate.demo">
                    <div class="rounded-xl border border-warn/30 bg-warn-soft px-3 py-2 text-sm font-medium text-warn-ink">Exemplo do tour: os valores abaixo são fictícios e nada é salvo.</div>
                </template>

                <div>
                    <x-input-label value="Tabela de alíquota" />
                    <select x-model="estimate.rateCardId" @change="calculate()" class="mt-1 block w-full rounded-lg border-rule text-sm">
                        <option value="">Selecione…</option>
                        @foreach (\App\Models\RateCard::where('active', true)->orderBy('name')->get() as $rc)
                            <option value="{{ $rc->id }}">{{ $rc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label value="Perfil de retenção" />
                    <select x-model="estimate.profileId" @change="calculate()" class="mt-1 block w-full rounded-lg border-rule text-sm">
                        <option value="">Selecione…</option>
                        @foreach (\App\Models\WithholdingProfile::where('active', true)->orderBy('name')->get() as $wp)
                            <option value="{{ $wp->id }}">{{ $wp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label value="Valor (R$)" />
                    <input type="number" step="0.01" min="0" x-model="estimate.amount" @change="calculate()" class="mt-1 block w-full rounded-lg border-rule text-sm">
                </div>
                <div>
                    <x-input-label value="Observação (opcional)" />
                    <input type="text" x-model="estimate.notes" class="mt-1 block w-full rounded-lg border-rule text-sm">
                </div>

                <template x-if="estimate.error">
                    <div class="rounded-xl border border-bad/25 bg-bad-soft px-3 py-2 text-sm font-medium text-bad-ink" x-text="estimate.error"></div>
                </template>

                <template x-if="estimate.result">
                    <div class="space-y-3" data-tour="est-result">
                        <div class="grid grid-cols-2 gap-2">
                            <div class="rounded-xl bg-paper p-3"><div class="text-xs text-ink-mute">Total impostos</div><div class="text-lg font-bold" x-text="brl(estimate.result.levies_total)"></div></div>
                            <div class="rounded-xl bg-paper p-3"><div class="text-xs text-ink-mute">Total retido</div><div class="text-lg font-bold text-bad" x-text="brl(estimate.result.withheld_total)"></div></div>
                            <div class="col-span-2 rounded-xl bg-paper p-3"><div class="text-xs text-ink-mute">Valor líquido</div><div class="text-lg font-bold text-ok-ink" x-text="brl(estimate.result.net_amount)"></div></div>
                        </div>
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-xs text-ink-mute">
                                <tr><th class="py-1">Imposto</th><th class="py-1 text-right">Alíquota</th><th class="py-1 text-right">Valor</th><th class="py-1 text-center">Retido?</th></tr>
                            </thead>
                            <tbody>
                                <template x-for="line in estimate.result.breakdown" :key="line.levy_id">
                                    <tr class="border-t border-rule">
                                        <td class="py-1" x-text="line.name"></td>
                                        <td class="py-1 text-right" x-text="line.percentage + '%'"></td>
                                        <td class="py-1 text-right" x-text="brl(line.amount)"></td>
                                        <td class="py-1 text-center" x-text="line.withheld ? 'Sim' : 'Não'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>

                <div class="flex justify-end gap-2 pt-2 border-t border-rule" data-tour="est-save">
                    <button @click="estimate.open = false" class="btn-ghost">Fechar</button>
                    <button @click="saveEstimate()" :disabled="!estimate.result" class="btn-primary">Salvar simulação</button>
                </div>
            </div>
        </div>

        {{-- Histórico de simulações do cliente --}}
        <div x-show="history.open" x-cloak class="overlay flex items-center justify-center" @click.self="history.open = false">
            <div class="dialog max-w-3xl max-h-[80vh] overflow-y-auto space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold">Histórico de simulações: <span x-text="history.clientName"></span></h3>
                    <button @click="history.open = false" class="rounded-lg px-2 text-2xl leading-none text-ink-mute hover:bg-ink/5 hover:text-ink" aria-label="Fechar">&times;</button>
                </div>
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs text-ink-mute">
                        <tr><th class="py-1">Quando</th><th class="py-1">Tabela</th><th class="py-1">Perfil</th><th class="py-1 text-right">Base</th><th class="py-1 text-right">Líquido</th><th class="py-1">Observação</th></tr>
                    </thead>
                    <tbody>
                        <template x-for="e in history.items" :key="e.id">
                            <tr class="border-t border-rule">
                                <td class="py-1" x-text="e.created_at"></td>
                                <td class="py-1" x-text="e.rate_card_name"></td>
                                <td class="py-1" x-text="e.withholding_profile_name"></td>
                                <td class="py-1 text-right" x-text="brl(e.base_amount)"></td>
                                <td class="py-1 text-right" x-text="brl(e.net_amount)"></td>
                                <td class="py-1" x-text="e.notes"></td>
                            </tr>
                        </template>
                        <tr x-show="!history.items.length"><td colspan="6" class="py-6 text-center text-ink-mute">Nenhuma simulação ainda.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Histórico de fechamentos --}}
        <div x-show="events.open" x-cloak class="overlay flex items-center justify-center" @click.self="events.open = false">
            <div class="dialog max-w-2xl max-h-[80vh] overflow-y-auto space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold">Histórico de fechamentos: {{ $period->format('m/Y') }}</h3>
                    <button @click="events.open = false" class="rounded-lg px-2 text-2xl leading-none text-ink-mute hover:bg-ink/5 hover:text-ink" aria-label="Fechar">&times;</button>
                </div>
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs text-ink-mute">
                        <tr><th class="py-1">Ação</th><th class="py-1">Quando</th><th class="py-1">Quem</th><th class="py-1">Situação</th><th class="py-1">Motivo</th></tr>
                    </thead>
                    <tbody>
                        <template x-for="e in events.items" :key="e.at + e.action">
                            <tr class="border-t border-rule">
                                <td class="py-1" x-text="e.action === 'closed' ? 'Fechado' : 'Reaberto'"></td>
                                <td class="py-1" x-text="e.at"></td>
                                <td class="py-1" x-text="e.user"></td>
                                <td class="py-1" x-text="e.confirmed + ' de ' + e.total"></td>
                                <td class="py-1" x-text="e.reason"></td>
                            </tr>
                        </template>
                        <tr x-show="!events.items.length"><td colspan="5" class="py-6 text-center text-ink-mute">Nenhum evento ainda.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Reabrir --}}
        <div x-show="openReopen" x-cloak class="overlay flex items-center justify-center" @click.self="openReopen = false">
            <div class="dialog max-w-md space-y-4">
                <h3 class="text-lg font-bold">Reabrir conferência</h3>
                <p class="text-sm text-ink-mute">O motivo fica registrado no histórico.</p>
                <textarea x-model="reopenReason" rows="3" class="w-full" placeholder="Motivo da reabertura"></textarea>
                <div class="flex justify-end gap-2">
                    <button @click="openReopen = false" class="btn-ghost">Cancelar</button>
                    <button @click="reopenMonth()" class="btn-primary">Reabrir</button>
                </div>
            </div>
        </div>

        {{-- Carregar mais itens --}}
        @if ($reconciliation && !$closed)
            <div x-show="openLoad" x-cloak class="overlay flex items-center justify-center" @click.self="openLoad = false">
                <form method="POST" action="{{ route('reconciliations.populate') }}" class="dialog max-w-lg space-y-4">
                    @csrf
                    <input type="hidden" name="month" value="{{ $monthValue }}">
                    <h3 class="text-lg font-bold">Carregar mais itens em {{ $period->format('m/Y') }}</h3>
                    <label class="flex items-start gap-2">
                        <input type="radio" name="mode" value="all" checked class="mt-1">
                        <span>Todos os clientes ativos que ainda não estão nesta conferência ({{ $missingClients }})</span>
                    </label>
                    <label class="flex items-start gap-2">
                        <input type="radio" name="mode" value="select" class="mt-1">
                        <span>
                            Selecionar clientes
                            <select name="client_ids[]" multiple size="5" class="mt-1 block w-full rounded-lg border-rule text-sm">
                                @foreach (\App\Models\Client::where('active', true)->orderBy('name')->get() as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </span>
                    </label>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="openLoad = false" class="btn-ghost">Cancelar</button>
                        <button class="btn-primary">Carregar</button>
                    </div>
                </form>
            </div>

            <div x-show="addItem.open" x-cloak class="overlay flex items-center justify-center" @click.self="addItem.open = false">
                <div class="dialog max-w-md space-y-4">
                    <h3 class="text-lg font-bold">Adicionar item</h3>
                    <div>
                        <x-input-label value="Cliente" />
                        <select x-model="addItem.clientId" class="mt-1 block w-full rounded-lg border-rule text-sm">
                            <option value="">Selecione…</option>
                            @foreach (\App\Models\Client::where('active', true)->orderBy('name')->get() as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Descrição (opcional)" />
                        <input type="text" x-model="addItem.label" class="mt-1 block w-full rounded-lg border-rule text-sm">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button @click="addItem.open = false" class="btn-ghost">Cancelar</button>
                        <button @click="submitAddItem()" class="btn-primary">Adicionar</button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function reconciliationPage() {
            return {
                openLoad: false,
                openReopen: false,
                reopenReason: '',
                csrf: document.querySelector('meta[name="csrf-token"]').content,
                estimate: { open: false, itemId: null, clientId: null, clientName: '', rateCardId: '', profileId: '', amount: '', notes: '', result: null, error: null },
                history: { open: false, clientName: '', items: [] },
                events: { open: false, items: [] },
                addItem: { open: false, clientId: '', label: '' },

                brl(v) {
                    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v || 0);
                },

                headers() {
                    return { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf };
                },

                openEstimate(itemId, clientId, clientName, profileId) {
                    this.estimate = { open: true, itemId, clientId, clientName, rateCardId: '', profileId: profileId || '', amount: '', notes: '', result: null, error: null };
                    if (this.estimate.profileId) this.calculate();
                },

                // Exemplo fictício usado pelo tour "Como simular impostos". Não grava nada.
                openEstimateDemo() {
                    this.estimate = {
                        open: true, demo: true, itemId: null, clientId: null, clientName: 'Cliente de exemplo',
                        rateCardId: '', profileId: '', amount: '10000', notes: '', error: null,
                        result: {
                            levies_total: 1115, withheld_total: 615, net_amount: 9385,
                            breakdown: [
                                { levy_id: 1, name: 'ISS', percentage: 5, amount: 500, withheld: false },
                                { levy_id: 2, name: 'PIS', percentage: 0.65, amount: 65, withheld: true },
                                { levy_id: 3, name: 'COFINS', percentage: 3, amount: 300, withheld: true },
                                { levy_id: 4, name: 'CSLL', percentage: 1, amount: 100, withheld: true },
                                { levy_id: 5, name: 'IRRF', percentage: 1.5, amount: 150, withheld: true },
                            ],
                        },
                    };
                },

                calculate() {
                    if (this.estimate.demo) return;
                    this.estimate.error = null;
                    this.estimate.result = null;
                    if (!this.estimate.rateCardId || !this.estimate.profileId || this.estimate.amount === '') return;

                    fetch(@json(route('estimates.calculate')), {
                        method: 'POST', headers: this.headers(),
                        body: JSON.stringify({ rate_card_id: this.estimate.rateCardId, withholding_profile_id: this.estimate.profileId, amount: this.estimate.amount })
                    }).then(r => r.json()).then(data => {
                        if (data.ok) { this.estimate.result = data; } else { this.estimate.error = data.message; }
                    });
                },

                saveEstimate() {
                    if (!this.estimate.result || this.estimate.demo) return;
                    fetch(@json(route('estimates.store')), {
                        method: 'POST', headers: this.headers(),
                        body: JSON.stringify({
                            client_id: this.estimate.clientId, rate_card_id: this.estimate.rateCardId,
                            withholding_profile_id: this.estimate.profileId, amount: this.estimate.amount,
                            notes: this.estimate.notes, reconciliation_item_id: this.estimate.itemId,
                        })
                    }).then(r => r.json()).then(data => {
                        if (data.ok) { this.estimate.open = false; window.location.reload(); }
                        else { this.estimate.error = data.message; }
                    });
                },

                openClientHistory(clientId, clientName) {
                    this.history = { open: true, clientName, items: [] };
                    fetch(`/clients/${clientId}/estimates`, { headers: { Accept: 'application/json' } })
                        .then(r => r.json()).then(data => { this.history.items = data.estimates; });
                },

                loadEvents() {
                    this.events = { open: true, items: [] };
                    @if ($reconciliation)
                        fetch(@json(route('reconciliations.events', $reconciliation)), { headers: { Accept: 'application/json' } })
                            .then(r => r.json()).then(data => { this.events.items = data.events; });
                    @endif
                },

                toggleConfirm(itemId, checked) {
                    fetch(`/reconciliation/items/${itemId}/confirm`, {
                        method: 'POST', headers: this.headers(), body: JSON.stringify({ confirmed: checked })
                    }).then(r => r.json()).then(() => window.location.reload());
                },

                async removeItem(itemId) {
                    if (!await confirmAction({ title: 'Remover da conferência?', message: 'O cliente sai da conferência deste mês. Você pode adicioná-lo de novo depois.', label: 'Remover' })) return;
                    fetch(`/reconciliation/items/${itemId}`, {
                        method: 'DELETE', headers: this.headers()
                    }).then(() => window.location.reload());
                },

                async closeMonth() {
                    if (!await confirmAction({ title: 'Fechar a conferência de {{ $period->format('m/Y') }}?', message: 'Depois de fechado, ninguém confirma, adiciona, edita ou remove itens. Você pode reabrir informando o motivo.', label: 'Fechar mês', tone: 'primary' })) return;
                    @if ($reconciliation)
                        fetch(@json(route('reconciliations.close', $reconciliation)), { method: 'POST', headers: this.headers(), body: '{}' })
                            .then(() => window.location.reload());
                    @endif
                },

                reopenMonth() {
                    if (!this.reopenReason.trim()) { alert('Informe o motivo.'); return; }
                    @if ($reconciliation)
                        fetch(@json(route('reconciliations.reopen', $reconciliation)), {
                            method: 'POST', headers: this.headers(), body: JSON.stringify({ reason: this.reopenReason })
                        }).then(() => window.location.reload());
                    @endif
                },

                openAddItem() { this.addItem = { open: true, clientId: '', label: '' }; },

                submitAddItem() {
                    if (!this.addItem.clientId) { alert('Selecione um cliente.'); return; }
                    @if ($reconciliation)
                        fetch(@json(route('reconciliations.items.store', $reconciliation)), {
                            method: 'POST', headers: this.headers(),
                            body: JSON.stringify({ client_id: this.addItem.clientId, label: this.addItem.label })
                        }).then(() => window.location.reload());
                    @endif
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
