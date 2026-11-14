<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Usuários</h1>
    </x-slot>

    <div class="space-y-5">
        <x-flash />

        <p class="page-lead">
            Quem tem acesso ao sistema. Administradores veem esta lista e gerenciam as contas; os demais usuários só usam a conferência.
        </p>

        <div class="flex items-center justify-between gap-3">
            <form method="GET" class="max-w-sm flex-1" data-tour="search">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nome ou e-mail…" class="w-full">
            </form>
        </div>

        <div class="table-wrap" data-tour="table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Perfil</th>
                        <th>Cadastro</th>
                        <th><span class="sr-only">Ações</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="whitespace-nowrap font-semibold">
                                {{ $user->name }}
                                @if ($user->id === auth()->id())<span class="chip-mute ml-2">Você</span>@endif
                            </td>
                            <td class="text-ink-mute">{{ $user->email }}</td>
                            <td><span class="{{ $user->isAdmin() ? 'chip-ok' : 'chip-mute' }}">{{ $user->roleLabel() }}</span></td>
                            <td class="whitespace-nowrap text-ink-mute">{{ $user->created_at?->format('d/m/Y') }}</td>
                            <td class="whitespace-nowrap text-right" data-tour="row-actions">
                                <a href="{{ route('users.edit', $user) }}" class="link-action">Editar</a>
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="ml-2 inline"
                                        data-confirm-title="Remover {{ $user->name }}?"
                                        data-confirm="A conta será apagada e a pessoa perde o acesso na hora. As conferências e simulações que ela fez continuam, sem o nome dela. Esta ação não pode ser desfeita.">
                                        @csrf @method('DELETE')
                                        <button class="link-danger">Remover</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-ink-mute">Nenhum usuário encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </div>
</x-app-layout>
