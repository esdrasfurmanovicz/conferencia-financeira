<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Conferência mensal</h2>
    </x-slot>

    @php
        $monthValue = $period->format('Y-m');
        $percent = $total ? (int) round($confirmed / $total * 100) : 0;
        $closed = (bool) $reconciliation?->isClosed();
    @endphp

    <div class="py-8" x-data="reconciliationPage()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <div class="bg-white shadow-sm rounded-lg p-4 flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <a class="px-2 py-1 rounded border text-sm" href="{{ route('reconciliations.index', ['month' => $period->copy()->subMonth()->format('Y-m')]) }}">&lsaquo;</a>
                    <form method="GET">
                        <input type="month" name="month" value="{{ $monthValue }}" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                    </form>
                    <a class="px-2 py-1 rounded border text-sm" href="{{ route('reconciliations.index', ['month' => $period->copy()->addMonth()->format('Y-m')]) }}">&rsaquo;</a>
                </div>

                @if ($reconciliation)
                    <div class="flex-1 min-w-[220px]">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-semibold">{{ $confirmed }} de {{ $total }} conferidos</span>
                            <span class="text-gray-500">{{ $percent }}%</span>
                        </div>
                        <div class="h-2 bg-gray-100 rounded">
                            <div class="h-2 bg-green-500 rounded" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 ml-auto">
                        @if ($closed)
                            <span class="px-2 py-1 rounded text-xs bg-red-100 text-red-800">
                                Fechado em {{ $reconciliation->closed_at?->format('d/m/Y H:i') }}
                                @if ($reconciliation->closedBy) por {{ $reconciliation->closedBy->name }} @endif
                            </span>
                            <button type="button" class="px-3 py-1.5 rounded border text-sm" @click="openReopen = true">Reabrir</button>
                        @else
                            <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-800">Aberto</span>
                            <button type="button" class="px-3 py-1.5 rounded border border-red-300 text-red-700 text-sm" @click="closeMonth()">Fechar mês</button>
                        @endif
                        <button type="button" class="px-3 py-1.5 rounded border text-sm" @click="loadEvents()">Histórico</button>
                    </div>
                @endif
            </div>

            @if ($closed)
                <div class="rounded-md bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                    Este mês está fechado: confirmar, adicionar, editar ou remover itens fica bloqueado até reabrir.
                </div>
            @endif

            @unless ($reconciliation)
                <div class="bg-white shadow-sm rounded-lg p-10 text-center space-y-4">
                    <h3 class="text-lg font-semibold">A conferência de {{ $period->format('m/Y') }} ainda não foi aberta</h3>
                    <p class="text-gray-500">Escolha como começar este mês.</p>
                    <div class="flex justify-center gap-2 flex-wrap">
                        <form method="POST" action="{{ route('reconciliations.populate') }}">
                            @csrf
                            <input type="hidden" name="month" value="{{ $monthValue }}">
                            <input type="hidden" name="mode" value="all">
                            <button class="rounded-md bg-gray-800 px-4 py-2 text-sm text-white">
                                Carregar todos os clientes ativos ({{ $missingClients }})
                            </button>
                        </form>
                        @if ($previous)
                            <form method="POST" action="{{ route('reconciliations.populate') }}">
                                @csrf
                                <input type="hidden" name="month" value="{{ $monthValue }}">
                                <input type="hidden" name="mode" value="copy">
                                <button class="rounded-md border px-4 py-2 text-sm">Copiar de {{ $previous->period->format('m/Y') }}</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('reconciliations.populate') }}">
                            @csrf
                            <input type="hidden" name="month" value="{{ $monthValue }}">
                            <input type="hidden" name="mode" value="empty">
                            <button class="rounded-md border px-4 py-2 text-sm">Começar do zero</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <form method="GET" class="flex flex-wrap gap-2 items-center">
                        <input type="hidden" name="month" value="{{ $monthValue }}">
                        <input type="text" name="name" value="{{ request('name') }}" placeholder="Nome do cliente" class="rounded-md border-gray-300 text-sm">
                        <select name="status" class="rounded-md border-gray-300 text-sm">
                            <option value="">Todos</option>
                            <option value="pending" @selected(request('status') === 'pending')>Pendentes</option>
                            <option value="confirmed" @selected(request('status') === 'confirmed')>Conferidos</option>
                        </select>
                        <button class="rounded-md border px-3 py-1.5 text-sm">Filtrar</button>
                    </form>

                    @unless ($closed)
                        <div class="flex gap-2">
                            <button type="button" class="rounded-md border px-3 py-1.5 text-sm" @click="openAddItem()">Adicionar item</button>
                            <button type="button" class="rounded-md border px-3 py-1.5 text-sm" @click="openLoad = true">Carregar mais</button>
                        </div>
                    @endunless
                </div>

                <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-2 w-8"></th>
                                <th class="px-3 py-2">Cliente</th>
                                <th class="px-3 py-2">Perfil</th>
                                <th class="px-3 py-2 text-right">Base</th>
                                <th class="px-3 py-2 text-right">Impostos</th>
                                <th class="px-3 py-2 text-right">Retido</th>
                                <th class="px-3 py-2 text-right">Líquido</th>
                                <th class="px-3 py-2">Conferido em</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($items as $item)
                                @php($estimate = $item->estimate)
                                <tr class="{{ $item->isConfirmed() ? 'bg-green-50' : '' }}">
                                    <td class="px-3 py-2">
                                        <input type="checkbox" {{ $closed ? 'disabled' : '' }}
                                            @checked($item->isConfirmed())
                                            @change="toggleConfirm({{ $item->id }}, $event.target.checked)">
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ $item->client->name }}
                                        @if ($item->label)<div class="text-xs text-gray-400">{{ $item->label }}</div>@endif
                                    </td>
                                    <td class="px-3 py-2">{{ $item->client->withholdingProfile?->name ?? '—' }}</td>
                                    <td class="px-3 py-2 text-right">{{ $estimate ? 'R$ ' . number_format($estimate->base_amount, 2, ',', '.') : '—' }}</td>
                                    <td class="px-3 py-2 text-right">{{ $estimate ? 'R$ ' . number_format($estimate->levies_total, 2, ',', '.') : '—' }}</td>
                                    <td class="px-3 py-2 text-right">{{ $estimate ? 'R$ ' . number_format($estimate->withheld_total, 2, ',', '.') : '—' }}</td>
                                    <td class="px-3 py-2 text-right font-semibold">{{ $estimate ? 'R$ ' . number_format($estimate->net_amount, 2, ',', '.') : '—' }}</td>
                                    <td class="px-3 py-2 text-xs text-gray-500">
                                        {{ $item->confirmed_at?->format('d/m/Y H:i') }}
                                        @if ($item->confirmedByUser)<div>{{ $item->confirmedByUser->name }}</div>@endif
                                    </td>
                                    <td class="px-3 py-2 text-right space-x-2 whitespace-nowrap">
                                        <button type="button" class="text-indigo-600"
                                            @click="openEstimate({{ $item->id }}, {{ $item->client_id }}, '{{ addslashes($item->client->name) }}', {{ $item->client->withholding_profile_id ?? 'null' }})">Simular</button>
                                        <button type="button" class="text-gray-600" @click="openClientHistory({{ $item->client_id }}, '{{ addslashes($item->client->name) }}')">Histórico</button>
                                        @unless ($closed)
                                            <button type="button" class="text-red-600" @click="removeItem({{ $item->id }})">Remover</button>
                                        @endunless
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="px-3 py-8 text-center text-gray-400">Nenhum item encontrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $items->links() }}
            @endunless
        </div>

        {{-- Painel de simulação --}}
        <div x-show="estimate.open" x-cloak class="fixed inset-0 bg-black/30 z-40 flex justify-end" @click.self="estimate.open = false">
            <div class="bg-white w-full max-w-lg h-full overflow-y-auto p-6 space-y-4 shadow-xl">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold">Simular impostos</h3>
                        <p class="text-sm text-gray-500" x-text="estimate.clientName || 'Simulação avulsa'"></p>
                    </div>
                    <button @click="estimate.open = false" class="text-gray-400">✕</button>
                </div>

                <div>
                    <x-input-label value="Tabela de alíquota" />
                    <select x-model="estimate.rateCardId" @change="calculate()" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                        <option value="">Selecione…</option>
                        @foreach (\App\Models\RateCard::where('active', true)->orderBy('name')->get() as $rc)
                            <option value="{{ $rc->id }}">{{ $rc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label value="Perfil de retenção" />
                    <select x-model="estimate.profileId" @change="calculate()" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                        <option value="">Selecione…</option>
                        @foreach (\App\Models\WithholdingProfile::where('active', true)->orderBy('name')->get() as $wp)
                            <option value="{{ $wp->id }}">{{ $wp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label value="Valor (R$)" />
                    <input type="number" step="0.01" min="0" x-model="estimate.amount" @change="calculate()" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <x-input-label value="Observação (opcional)" />
                    <input type="text" x-model="estimate.notes" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                </div>

                <template x-if="estimate.error">
                    <div class="rounded-md bg-red-50 border border-red-200 px-3 py-2 text-sm text-red-700" x-text="estimate.error"></div>
                </template>

                <template x-if="estimate.result">
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-gray-50 rounded p-3"><div class="text-xs text-gray-500">Total impostos</div><div class="text-lg font-semibold" x-text="brl(estimate.result.levies_total)"></div></div>
                            <div class="bg-gray-50 rounded p-3"><div class="text-xs text-gray-500">Total retido</div><div class="text-lg font-semibold text-red-600" x-text="brl(estimate.result.withheld_total)"></div></div>
                            <div class="bg-gray-50 rounded p-3 col-span-2"><div class="text-xs text-gray-500">Valor líquido</div><div class="text-lg font-semibold text-green-700" x-text="brl(estimate.result.net_amount)"></div></div>
                        </div>
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-xs uppercase text-gray-500">
                                <tr><th class="py-1">Imposto</th><th class="py-1 text-right">Alíquota</th><th class="py-1 text-right">Valor</th><th class="py-1 text-center">Retido?</th></tr>
                            </thead>
                            <tbody>
                                <template x-for="line in estimate.result.breakdown" :key="line.levy_id">
                                    <tr class="border-t">
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

                <div class="flex justify-end gap-2 pt-2 border-t">
                    <button @click="estimate.open = false" class="px-4 py-2 text-sm text-gray-600">Fechar</button>
                    <button @click="saveEstimate()" :disabled="!estimate.result" class="rounded-md bg-gray-800 disabled:opacity-40 px-4 py-2 text-sm text-white">Salvar simulação</button>
                </div>
            </div>
        </div>

        {{-- Histórico de simulações do cliente --}}
        <div x-show="history.open" x-cloak class="fixed inset-0 bg-black/30 z-40 flex items-center justify-center" @click.self="history.open = false">
            <div class="bg-white w-full max-w-3xl max-h-[80vh] overflow-y-auto rounded-lg p-6 space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Histórico de simulações — <span x-text="history.clientName"></span></h3>
                    <button @click="history.open = false" class="text-gray-400">✕</button>
                </div>
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase text-gray-500">
                        <tr><th class="py-1">Quando</th><th class="py-1">Tabela</th><th class="py-1">Perfil</th><th class="py-1 text-right">Base</th><th class="py-1 text-right">Líquido</th><th class="py-1">Observação</th></tr>
                    </thead>
                    <tbody>
                        <template x-for="e in history.items" :key="e.id">
                            <tr class="border-t">
                                <td class="py-1" x-text="e.created_at"></td>
                                <td class="py-1" x-text="e.rate_card_name"></td>
                                <td class="py-1" x-text="e.withholding_profile_name"></td>
                                <td class="py-1 text-right" x-text="brl(e.base_amount)"></td>
                                <td class="py-1 text-right" x-text="brl(e.net_amount)"></td>
                                <td class="py-1" x-text="e.notes"></td>
                            </tr>
                        </template>
                        <tr x-show="!history.items.length"><td colspan="6" class="py-6 text-center text-gray-400">Nenhuma simulação ainda.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Histórico de fechamentos --}}
        <div x-show="events.open" x-cloak class="fixed inset-0 bg-black/30 z-40 flex items-center justify-center" @click.self="events.open = false">
            <div class="bg-white w-full max-w-2xl max-h-[80vh] overflow-y-auto rounded-lg p-6 space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Histórico de fechamentos — {{ $period->format('m/Y') }}</h3>
                    <button @click="events.open = false" class="text-gray-400">✕</button>
                </div>
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase text-gray-500">
                        <tr><th class="py-1">Ação</th><th class="py-1">Quando</th><th class="py-1">Quem</th><th class="py-1">Situação</th><th class="py-1">Motivo</th></tr>
                    </thead>
                    <tbody>
                        <template x-for="e in events.items" :key="e.at + e.action">
                            <tr class="border-t">
                                <td class="py-1" x-text="e.action === 'closed' ? 'Fechado' : 'Reaberto'"></td>
                                <td class="py-1" x-text="e.at"></td>
                                <td class="py-1" x-text="e.user"></td>
                                <td class="py-1" x-text="e.confirmed + ' de ' + e.total"></td>
                                <td class="py-1" x-text="e.reason"></td>
                            </tr>
                        </template>
                        <tr x-show="!events.items.length"><td colspan="5" class="py-6 text-center text-gray-400">Nenhum evento ainda.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Reabrir --}}
        <div x-show="openReopen" x-cloak class="fixed inset-0 bg-black/30 z-40 flex items-center justify-center" @click.self="openReopen = false">
            <div class="bg-white w-full max-w-md rounded-lg p-6 space-y-4">
                <h3 class="text-lg font-semibold">Reabrir conferência</h3>
                <p class="text-sm text-gray-500">O motivo fica registrado no histórico.</p>
                <textarea x-model="reopenReason" rows="3" class="w-full rounded-md border-gray-300 text-sm" placeholder="Motivo da reabertura"></textarea>
                <div class="flex justify-end gap-2">
                    <button @click="openReopen = false" class="px-4 py-2 text-sm text-gray-600">Cancelar</button>
                    <button @click="reopenMonth()" class="rounded-md bg-gray-800 px-4 py-2 text-sm text-white">Reabrir</button>
                </div>
            </div>
        </div>

        {{-- Carregar mais itens --}}
        @if ($reconciliation && !$closed)
            <div x-show="openLoad" x-cloak class="fixed inset-0 bg-black/30 z-40 flex items-center justify-center" @click.self="openLoad = false">
                <form method="POST" action="{{ route('reconciliations.populate') }}" class="bg-white w-full max-w-lg rounded-lg p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="month" value="{{ $monthValue }}">
                    <h3 class="text-lg font-semibold">Carregar mais itens em {{ $period->format('m/Y') }}</h3>
                    <label class="flex items-start gap-2">
                        <input type="radio" name="mode" value="all" checked class="mt-1">
                        <span>Todos os clientes ativos que ainda não estão nesta conferência ({{ $missingClients }})</span>
                    </label>
                    <label class="flex items-start gap-2">
                        <input type="radio" name="mode" value="select" class="mt-1">
                        <span>
                            Selecionar clientes
                            <select name="client_ids[]" multiple size="5" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                @foreach (\App\Models\Client::where('active', true)->orderBy('name')->get() as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </span>
                    </label>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="openLoad = false" class="px-4 py-2 text-sm text-gray-600">Cancelar</button>
                        <button class="rounded-md bg-gray-800 px-4 py-2 text-sm text-white">Carregar</button>
                    </div>
                </form>
            </div>

            <div x-show="addItem.open" x-cloak class="fixed inset-0 bg-black/30 z-40 flex items-center justify-center" @click.self="addItem.open = false">
                <div class="bg-white w-full max-w-md rounded-lg p-6 space-y-4">
                    <h3 class="text-lg font-semibold">Adicionar item</h3>
                    <div>
                        <x-input-label value="Cliente" />
                        <select x-model="addItem.clientId" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                            <option value="">Selecione…</option>
                            @foreach (\App\Models\Client::where('active', true)->orderBy('name')->get() as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Descrição (opcional)" />
                        <input type="text" x-model="addItem.label" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button @click="addItem.open = false" class="px-4 py-2 text-sm text-gray-600">Cancelar</button>
                        <button @click="submitAddItem()" class="rounded-md bg-gray-800 px-4 py-2 text-sm text-white">Adicionar</button>
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

                calculate() {
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
                    if (!this.estimate.result) return;
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

                removeItem(itemId) {
                    if (!confirm('Remover este item da conferência?')) return;
                    fetch(`/reconciliation/items/${itemId}`, {
                        method: 'DELETE', headers: this.headers()
                    }).then(() => window.location.reload());
                },

                closeMonth() {
                    if (!confirm('Fechar a conferência deste mês?')) return;
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
