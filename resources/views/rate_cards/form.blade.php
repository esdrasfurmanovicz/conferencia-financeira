<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">
            {{ $rateCard->exists ? 'Editar tabela de alíquota' : 'Nova tabela de alíquota' }}
        </h1>
    </x-slot>

    <div class="">
        <div class="max-w-4xl space-y-6">
            <x-flash />

            <form method="POST" action="{{ $rateCard->exists ? route('rate-cards.update', $rateCard) : route('rate-cards.store') }}"
                class="panel space-y-6 p-6 sm:p-9">
                @csrf
                @if ($rateCard->exists) @method('PUT') @endif

                <div>
                    <x-input-label for="name" value="Nome" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" value="{{ old('name', $rateCard->name) }}" required autofocus />
                </div>
                <div>
                    <x-input-label for="description" value="Descrição (opcional)" />
                    <textarea id="description" name="description" rows="2" class="mt-1 block w-full rounded-lg border-rule">{{ old('description', $rateCard->description) }}</textarea>
                </div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $rateCard->active ?? true))>
                    <span>Ativa</span>
                </label>
                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('rate-cards.index') }}" class="btn-ghost">Voltar</a>
                    <button class="btn-primary">Salvar</button>
                </div>
            </form>

            @if ($rateCard->exists)
                <div class="panel space-y-6 p-6 sm:p-9" data-tour="rc-items">
                    <h3 class="font-semibold text-ink">Impostos desta tabela</h3>

                    <table class="data-table">
                        <thead class="text-left text-xs text-ink-mute">
                            <tr>
                                <th class="py-2">Imposto</th>
                                <th class="py-2">Alíquota</th>
                                <th class="py-2">Vigência</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rule/70">
                            @forelse ($rateCard->items as $item)
                                <tr>
                                    <td class="py-2">{{ $item->levy->name }} ({{ $item->levy->code }})</td>
                                    <td class="py-2">{{ number_format($item->percentage, 3, ',', '.') }}%</td>
                                    <td class="py-2 text-ink-mute">
                                        @if ($item->valid_from && $item->valid_until)
                                            {{ $item->valid_from->format('d/m/Y') }} a {{ $item->valid_until->format('d/m/Y') }}
                                        @elseif ($item->valid_from)
                                            A partir de {{ $item->valid_from->format('d/m/Y') }}
                                        @elseif ($item->valid_until)
                                            Até {{ $item->valid_until->format('d/m/Y') }}
                                        @else
                                            Sem prazo
                                        @endif
                                    </td>
                                    <td class="py-2 text-right">
                                        <form method="POST" action="{{ route('rate-cards.items.destroy', [$rateCard, $item]) }}" data-confirm-title="Tirar {{ $item->levy->code }} da tabela?" data-confirm="A alíquota deste imposto deixa de valer nesta tabela. O imposto continua no catálogo." data-confirm-label="Tirar da tabela">
                                            @csrf @method('DELETE')
                                            <button class="link-danger">Remover</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-ink-mute">Nenhum imposto configurado ainda.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if (($availableLevies ?? collect())->isNotEmpty())
                        <form method="POST" action="{{ route('rate-cards.items.store', $rateCard) }}" class="flex flex-wrap items-end gap-3 pt-2 border-t" data-tour="rc-add-item">
                            @csrf
                            <div>
                                <x-input-label value="Imposto" />
                                <select name="levy_id" class="mt-1 rounded-lg border-rule text-sm" required>
                                    @foreach ($availableLevies as $levy)
                                        <option value="{{ $levy->id }}">{{ $levy->name }} ({{ $levy->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label value="Alíquota %" />
                                <input type="number" step="0.001" min="0" max="100" name="percentage" class="mt-1 rounded-lg border-rule text-sm w-28" required>
                            </div>
                            <div>
                                <x-input-label value="Vigência de (opcional)" />
                                <input type="date" name="valid_from" class="mt-1 rounded-lg border-rule text-sm">
                            </div>
                            <div>
                                <x-input-label value="Vigência até (opcional)" />
                                <input type="date" name="valid_until" class="mt-1 rounded-lg border-rule text-sm">
                            </div>
                            <button class="btn-primary">Adicionar</button>
                        </form>
                    @else
                        <p class="text-sm text-ink-mute">Todos os impostos ativos já estão nesta tabela.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
