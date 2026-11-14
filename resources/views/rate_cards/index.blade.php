<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Tabelas de alíquota</h1>
    </x-slot>

    <div class="">
        <div class="space-y-5">
            <x-flash />

            <p class="text-sm text-ink-mute">
                Cada tabela é um preset de alíquotas por imposto (ex.: "Serviço de consultoria"). Ela
                diz <strong>quanto</strong> cobrar de cada imposto; o perfil de retenção do cliente diz
                <strong>se</strong> esse imposto é retido.
            </p>

            <div class="flex justify-end">
                <a href="{{ route('rate-cards.create') }}" data-tour="add-button" class="btn-primary">Nova tabela</a>
            </div>

            <div class="table-wrap" data-tour="table">
                <table class="data-table">
                    <thead class="bg-paper/70 text-left text-xs text-ink-mute">
                        <tr>
                            <th class="px-5 py-4">Nome</th>
                            <th class="px-5 py-4">Impostos configurados</th>
                            <th class="px-5 py-4">Situação</th>
                            <th class="px-5 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-rule/70">
                        @forelse ($rateCards as $rateCard)
                            <tr>
                                <td class="px-5 py-5">{{ $rateCard->name }}</td>
                                <td class="px-5 py-5">{{ $rateCard->items_count }}</td>
                                <td class="px-5 py-5">
                                    <span class="{{ $rateCard->active ? 'chip-ok' : 'chip-mute' }}">
                                        {{ $rateCard->active ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-5 text-right" data-tour="row-actions">
                                    <a href="{{ route('rate-cards.edit', $rateCard) }}" class="link-action">Editar</a>
                                    <form method="POST" action="{{ route('rate-cards.destroy', $rateCard) }}" class="ml-2 inline" data-confirm-title="Remover {{ $rateCard->name }}?" data-confirm="A tabela e as alíquotas dela serão apagadas. Esta ação não pode ser desfeita.">
                                        @csrf @method('DELETE')
                                        <button class="link-danger">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-12 text-center text-ink-mute">Nenhuma tabela cadastrada ainda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
