<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Catálogo de impostos</h1>
    </x-slot>

    <div class="">
        <div class="space-y-5">
            <x-flash />

            <p class="text-sm text-ink-mute">
                Os impostos mais comuns já vêm cadastrados; use "Novo imposto" só para o que faltar. Esta lista define os impostos que podem compor uma tabela de alíquota ou uma regra de retenção.
                O "grupo de retenção" e o "piso" são opcionais: use quando vários impostos pequenos só
                devem ser retidos juntos, a partir de um valor mínimo combinado.
            </p>

            <div class="flex justify-end">
                <a href="{{ route('levies.create') }}" data-tour="add-button" class="btn-primary">Novo imposto</a>
            </div>

            <div class="table-wrap" data-tour="table">
                <table class="data-table">
                    <thead class="bg-paper/70 text-left text-xs text-ink-mute">
                        <tr>
                            <th class="px-5 py-4">Código</th>
                            <th class="px-5 py-4">Nome</th>
                            <th class="px-5 py-4">Grupo de retenção</th>
                            <th class="px-5 py-4">Piso</th>
                            <th class="px-5 py-4">Situação</th>
                            <th class="px-5 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-rule/70">
                        @forelse ($levies as $levy)
                            <tr>
                                <td class="px-5 py-5 font-mono">{{ $levy->code }}</td>
                                <td class="px-5 py-5">{{ $levy->name }}</td>
                                <td class="px-5 py-5">{{ $levy->withholding_group ?? 'Nenhum' }}</td>
                                <td class="whitespace-nowrap px-4 py-2">{{ $levy->withholding_floor !== null ? 'R$ ' . number_format($levy->withholding_floor, 2, ',', '.') : 'Sem piso' }}</td>
                                <td class="px-5 py-5">
                                    <span class="{{ $levy->active ? 'chip-ok' : 'chip-mute' }}">
                                        {{ $levy->active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-5 text-right" data-tour="row-actions">
                                    <a href="{{ route('levies.edit', $levy) }}" class="link-action">Editar</a>
                                    <form method="POST" action="{{ route('levies.destroy', $levy) }}" class="ml-2 inline" data-confirm-title="Remover {{ $levy->code }}?" data-confirm="O imposto será apagado, junto com as alíquotas e regras de retenção que usam ele. Esta ação não pode ser desfeita.">
                                        @csrf @method('DELETE')
                                        <button class="link-danger">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-ink-mute">Nenhum imposto cadastrado ainda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
