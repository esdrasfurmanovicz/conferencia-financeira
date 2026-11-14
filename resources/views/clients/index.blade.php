<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Clientes</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <div class="flex justify-between items-center gap-3">
                <form method="GET" class="flex-1 max-w-sm">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nome…"
                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                </form>
                <a href="{{ route('clients.create') }}" class="rounded-md bg-gray-800 px-4 py-2 text-sm text-white">Novo cliente</a>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-2">Nome</th>
                            <th class="px-4 py-2">Documento</th>
                            <th class="px-4 py-2">Perfil de retenção</th>
                            <th class="px-4 py-2">Situação</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($clients as $client)
                            <tr>
                                <td class="px-4 py-2">{{ $client->name }}</td>
                                <td class="px-4 py-2">{{ $client->document }}</td>
                                <td class="px-4 py-2">{{ $client->withholdingProfile?->name ?? '—' }}</td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-0.5 rounded text-xs {{ $client->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $client->active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    <a href="{{ route('clients.edit', $client) }}" class="text-indigo-600">Editar</a>
                                    <form method="POST" action="{{ route('clients.destroy', $client) }}" class="inline" onsubmit="return confirm('Remover este cliente?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Nenhum cliente cadastrado ainda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $clients->links() }}
        </div>
    </div>
</x-app-layout>
