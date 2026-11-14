<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Clientes</h1>
    </x-slot>

    <div class="">
        <div class="space-y-5">
            <x-flash />

            <div class="flex justify-between items-center gap-3">
                <form method="GET" class="max-w-sm flex-1" data-tour="search">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nome…"
                        class="w-full rounded-lg border-rule text-sm">
                </form>
                <a href="{{ route('clients.create') }}" data-tour="add-button" class="btn-primary">Novo cliente</a>
            </div>

            <div class="table-wrap" data-tour="table">
                <table class="data-table">
                    <thead class="bg-paper/70 text-left text-xs text-ink-mute">
                        <tr>
                            <th class="px-5 py-4">Nome</th>
                            <th class="px-5 py-4">Documento</th>
                            <th class="px-5 py-4">Perfil de retenção</th>
                            <th class="px-5 py-4">Situação</th>
                            <th class="px-5 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-rule/70">
                        @forelse ($clients as $client)
                            <tr>
                                <td class="px-5 py-5">{{ $client->name }}</td>
                                <td class="px-5 py-5">{{ $client->document }}</td>
                                <td class="px-5 py-5">{{ $client->withholdingProfile?->name ?? 'Sem perfil' }}</td>
                                <td class="px-5 py-5">
                                    <span class="{{ $client->active ? 'chip-ok' : 'chip-mute' }}">
                                        {{ $client->active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-5 text-right" data-tour="row-actions">
                                    <a href="{{ route('clients.edit', $client) }}" class="link-action">Editar</a>
                                    <form method="POST" action="{{ route('clients.destroy', $client) }}" class="ml-2 inline" data-confirm-title="Remover {{ $client->name }}?" data-confirm="O cadastro do cliente será apagado. Esta ação não pode ser desfeita.">
                                        @csrf @method('DELETE')
                                        <button class="link-danger">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-12 text-center text-ink-mute">Nenhum cliente cadastrado ainda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $clients->links() }}
        </div>
    </div>
</x-app-layout>
