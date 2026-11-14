<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $profile->exists ? 'Editar perfil de retenção' : 'Novo perfil de retenção' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ $profile->exists ? route('withholding-profiles.update', $profile) : route('withholding-profiles.store') }}"
                class="bg-white shadow-sm rounded-lg p-6 space-y-4">
                @csrf
                @if ($profile->exists) @method('PUT') @endif

                <div>
                    <x-input-label for="name" value="Nome" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" value="{{ old('name', $profile->name) }}" required autofocus />
                </div>
                <div>
                    <x-input-label for="description" value="Descrição (opcional)" />
                    <textarea id="description" name="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $profile->description) }}</textarea>
                </div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $profile->active ?? true))>
                    <span>Ativo</span>
                </label>

                @if (isset($levies))
                    <div class="pt-4 border-t">
                        <h3 class="font-semibold text-gray-800 mb-2">Regras por imposto</h3>
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="py-2">Imposto</th>
                                    <th class="py-2 text-center">Se aplica</th>
                                    <th class="py-2 text-center">É retido</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($levies as $levy)
                                    @php($rule = $rulesByLevy[$levy->id] ?? null)
                                    <tr>
                                        <td class="py-2">{{ $levy->name }} ({{ $levy->code }})</td>
                                        <td class="py-2 text-center">
                                            <input type="checkbox" name="applies[]" value="{{ $levy->id }}" @checked($rule?->applies)>
                                        </td>
                                        <td class="py-2 text-center">
                                            <input type="checkbox" name="withheld[]" value="{{ $levy->id }}" @checked($rule?->withheld)>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-400 pt-2 border-t">Salve o perfil para configurar as regras por imposto.</p>
                @endif

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('withholding-profiles.index') }}" class="px-4 py-2 text-sm text-gray-600">Voltar</a>
                    <button class="rounded-md bg-gray-800 px-4 py-2 text-sm text-white">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
