<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tabelas de alíquota</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <p class="text-sm text-gray-500">
                Cada tabela é um preset de alíquotas por imposto (ex.: "Serviço de consultoria"). Ela
                diz <strong>quanto</strong> cobrar de cada imposto; o perfil de retenção do cliente diz
                <strong>se</strong> esse imposto é retido.
            </p>

            <div class="flex justify-end">
                <a href="{{ route('rate-cards.create') }}" class="rounded-md bg-gray-800 px-4 py-2 text-sm text-white">Nova tabela</a>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-2">Nome</th>
                            <th class="px-4 py-2">Impostos configurados</th>
                            <th class="px-4 py-2">Situação</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($rateCards as $rateCard)
                            <tr>
                                <td class="px-4 py-2">{{ $rateCard->name }}</td>
                                <td class="px-4 py-2">{{ $rateCard->items_count }}</td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-0.5 rounded text-xs {{ $rateCard->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $rateCard->active ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    <a href="{{ route('rate-cards.edit', $rateCard) }}" class="text-indigo-600">Editar</a>
                                    <form method="POST" action="{{ route('rate-cards.destroy', $rateCard) }}" class="inline" onsubmit="return confirm('Remover esta tabela?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">Nenhuma tabela cadastrada ainda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
