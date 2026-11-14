<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">
            {{ $profile->exists ? 'Editar perfil de retenção' : 'Novo perfil de retenção' }}
        </h1>
    </x-slot>

    <div class="">
        <div class="max-w-4xl">
            <form method="POST" action="{{ $profile->exists ? route('withholding-profiles.update', $profile) : route('withholding-profiles.store') }}"
                class="panel space-y-6 p-6 sm:p-9">
                @csrf
                @if ($profile->exists) @method('PUT') @endif

                <div>
                    <x-input-label for="name" value="Nome" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" value="{{ old('name', $profile->name) }}" required autofocus />
                </div>
                <div>
                    <x-input-label for="description" value="Descrição (opcional)" />
                    <textarea id="description" name="description" rows="2" class="mt-1 block w-full rounded-lg border-rule">{{ old('description', $profile->description) }}</textarea>
                </div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $profile->active ?? true))>
                    <span>Ativo</span>
                </label>

                @if (isset($levies))
                    <div class="pt-4 border-t" data-tour="rules">
                        <h3 class="font-semibold text-ink mb-2">Regras por imposto</h3>
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-xs text-ink-mute">
                                <tr>
                                    <th class="py-2">Imposto</th>
                                    <th class="py-2 text-center">Se aplica</th>
                                    <th class="py-2 text-center">É retido</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-rule/70">
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
                    <p class="text-sm text-ink-mute pt-2 border-t">Salve o perfil para configurar as regras por imposto.</p>
                @endif

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('withholding-profiles.index') }}" class="btn-ghost">Voltar</a>
                    <button class="btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
