<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Perfis de retenção</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <p class="text-sm text-gray-500">
                Cada perfil diz, por imposto: se ele se aplica ao cliente e, se sim, se o cliente retém
                esse imposto na hora de pagar (em vez de o prestador recolher depois).
            </p>

            <div class="flex justify-end">
                <a href="{{ route('withholding-profiles.create') }}" class="rounded-md bg-gray-800 px-4 py-2 text-sm text-white">Novo perfil</a>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-2">Nome</th>
                            <th class="px-4 py-2">Clientes vinculados</th>
                            <th class="px-4 py-2">Situação</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($profiles as $profile)
                            <tr>
                                <td class="px-4 py-2">{{ $profile->name }}</td>
                                <td class="px-4 py-2">{{ $profile->clients_count }}</td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-0.5 rounded text-xs {{ $profile->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $profile->active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    <a href="{{ route('withholding-profiles.edit', $profile) }}" class="text-indigo-600">Editar</a>
                                    <form method="POST" action="{{ route('withholding-profiles.destroy', $profile) }}" class="inline" onsubmit="return confirm('Remover este perfil?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">Nenhum perfil cadastrado ainda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
