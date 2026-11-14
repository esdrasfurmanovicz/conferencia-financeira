<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $rateCard->exists ? 'Editar tabela de alíquota' : 'Nova tabela de alíquota' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <form method="POST" action="{{ $rateCard->exists ? route('rate-cards.update', $rateCard) : route('rate-cards.store') }}"
                class="bg-white shadow-sm rounded-lg p-6 space-y-4">
                @csrf
                @if ($rateCard->exists) @method('PUT') @endif

                <div>
                    <x-input-label for="name" value="Nome" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" value="{{ old('name', $rateCard->name) }}" required autofocus />
                </div>
                <div>
                    <x-input-label for="description" value="Descrição (opcional)" />
                    <textarea id="description" name="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $rateCard->description) }}</textarea>
                </div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $rateCard->active ?? true))>
                    <span>Ativa</span>
                </label>
                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('rate-cards.index') }}" class="px-4 py-2 text-sm text-gray-600">Voltar</a>
                    <button class="rounded-md bg-gray-800 px-4 py-2 text-sm text-white">Salvar</button>
                </div>
            </form>

            @if ($rateCard->exists)
                <div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
                    <h3 class="font-semibold text-gray-800">Impostos desta tabela</h3>

                    <table class="min-w-full text-sm divide-y divide-gray-100">
                        <thead class="text-left text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2">Imposto</th>
                                <th class="py-2">Alíquota</th>
                                <th class="py-2">Vigência</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($rateCard->items as $item)
                                <tr>
                                    <td class="py-2">{{ $item->levy->name }} ({{ $item->levy->code }})</td>
                                    <td class="py-2">{{ number_format($item->percentage, 3, ',', '.') }}%</td>
                                    <td class="py-2 text-gray-500">
                                        {{ $item->valid_from?->format('d/m/Y') ?? '—' }} a {{ $item->valid_until?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="py-2 text-right">
                                        <form method="POST" action="{{ route('rate-cards.items.destroy', [$rateCard, $item]) }}" onsubmit="return confirm('Remover este imposto da tabela?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600">Remover</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-gray-400">Nenhum imposto configurado ainda.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if (($availableLevies ?? collect())->isNotEmpty())
                        <form method="POST" action="{{ route('rate-cards.items.store', $rateCard) }}" class="flex flex-wrap items-end gap-3 pt-2 border-t">
                            @csrf
                            <div>
                                <x-input-label value="Imposto" />
                                <select name="levy_id" class="mt-1 rounded-md border-gray-300 shadow-sm text-sm" required>
                                    @foreach ($availableLevies as $levy)
                                        <option value="{{ $levy->id }}">{{ $levy->name }} ({{ $levy->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label value="Alíquota %" />
                                <input type="number" step="0.001" min="0" max="100" name="percentage" class="mt-1 rounded-md border-gray-300 shadow-sm text-sm w-28" required>
                            </div>
                            <div>
                                <x-input-label value="Vigência de (opcional)" />
                                <input type="date" name="valid_from" class="mt-1 rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                            <div>
                                <x-input-label value="Vigência até (opcional)" />
                                <input type="date" name="valid_until" class="mt-1 rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                            <button class="rounded-md bg-gray-800 px-4 py-2 text-sm text-white">Adicionar</button>
                        </form>
                    @else
                        <p class="text-sm text-gray-400">Todos os impostos ativos já estão nesta tabela.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
