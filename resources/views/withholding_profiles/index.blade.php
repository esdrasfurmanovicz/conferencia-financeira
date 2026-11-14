<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Perfis de retenção</h1>
    </x-slot>

    <div class="">
        <div class="space-y-5">
            <x-flash />

            <p class="text-sm text-ink-mute">
                Já vêm perfis para os tipos de cliente mais comuns; ajuste ou crie outros se precisar. Cada perfil diz, por imposto: se ele se aplica ao cliente e, se sim, se o cliente retém
                esse imposto na hora de pagar (em vez de o prestador recolher depois).
            </p>

            <div class="flex justify-end">
                <a href="{{ route('withholding-profiles.create') }}" data-tour="add-button" class="btn-primary">Novo perfil</a>
            </div>

            <div class="table-wrap" data-tour="table">
                <table class="data-table">
                    <thead class="bg-paper/70 text-left text-xs text-ink-mute">
                        <tr>
                            <th class="px-5 py-4">Nome</th>
                            <th class="px-5 py-4">Clientes vinculados</th>
                            <th class="px-5 py-4">Situação</th>
                            <th class="px-5 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-rule/70">
                        @forelse ($profiles as $profile)
                            <tr>
                                <td class="px-5 py-5">{{ $profile->name }}</td>
                                <td class="px-5 py-5">{{ $profile->clients_count }}</td>
                                <td class="px-5 py-5">
                                    <span class="{{ $profile->active ? 'chip-ok' : 'chip-mute' }}">
                                        {{ $profile->active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-5 text-right" data-tour="row-actions">
                                    <a href="{{ route('withholding-profiles.edit', $profile) }}" class="link-action">Editar</a>
                                    <form method="POST" action="{{ route('withholding-profiles.destroy', $profile) }}" class="ml-2 inline" data-confirm-title="Remover {{ $profile->name }}?" data-confirm="O perfil e as regras dele serão apagados. Esta ação não pode ser desfeita.">
                                        @csrf @method('DELETE')
                                        <button class="link-danger">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-12 text-center text-ink-mute">Nenhum perfil cadastrado ainda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
