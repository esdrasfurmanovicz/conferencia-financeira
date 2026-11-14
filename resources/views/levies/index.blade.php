<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catálogo de impostos</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <p class="text-sm text-gray-500">
                A lista de impostos que podem compor uma tabela de alíquota ou uma regra de retenção.
                O "grupo de retenção" e o "piso" são opcionais: use quando vários impostos pequenos só
                devem ser retidos juntos, a partir de um valor mínimo combinado.
            </p>

            <div class="flex justify-end">
                <a href="{{ route('levies.create') }}" class="rounded-md bg-gray-800 px-4 py-2 text-sm text-white">Novo imposto</a>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-2">Código</th>
                            <th class="px-4 py-2">Nome</th>
                            <th class="px-4 py-2">Grupo de retenção</th>
                            <th class="px-4 py-2">Piso</th>
                            <th class="px-4 py-2">Situação</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($levies as $levy)
                            <tr>
                                <td class="px-4 py-2 font-mono">{{ $levy->code }}</td>
                                <td class="px-4 py-2">{{ $levy->name }}</td>
                                <td class="px-4 py-2">{{ $levy->withholding_group ?? '—' }}</td>
                                <td class="px-4 py-2">{{ $levy->withholding_floor !== null ? 'R$ ' . number_format($levy->withholding_floor, 2, ',', '.') : '—' }}</td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-0.5 rounded text-xs {{ $levy->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $levy->active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    <a href="{{ route('levies.edit', $levy) }}" class="text-indigo-600">Editar</a>
                                    <form method="POST" action="{{ route('levies.destroy', $levy) }}" class="inline" onsubmit="return confirm('Remover este imposto?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Nenhum imposto cadastrado ainda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
