<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">
            {{ $levy->exists ? 'Editar imposto' : 'Novo imposto' }}
        </h1>
    </x-slot>

    <div class="">
        <div class="max-w-3xl">
            <form method="POST" action="{{ $levy->exists ? route('levies.update', $levy) : route('levies.store') }}"
                class="panel space-y-6 p-6 sm:p-9">
                @csrf
                @if ($levy->exists) @method('PUT') @endif

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="code" value="Código" />
                        <x-text-input id="code" name="code" class="mt-1 block w-full" value="{{ old('code', $levy->code) }}" required autofocus />
                    </div>
                    <div>
                        <x-input-label for="name" value="Nome" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" value="{{ old('name', $levy->name) }}" required />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="withholding_group" value="Grupo de retenção (opcional)" />
                        <x-text-input id="withholding_group" name="withholding_group" class="mt-1 block w-full" value="{{ old('withholding_group', $levy->withholding_group) }}" />
                    </div>
                    <div>
                        <x-input-label for="withholding_floor" value="Piso de retenção em R$ (opcional)" />
                        <x-text-input type="number" step="0.01" min="0" id="withholding_floor" name="withholding_floor" class="mt-1 block w-full" value="{{ old('withholding_floor', $levy->withholding_floor) }}" />
                    </div>
                </div>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $levy->active ?? true))>
                    <span>Ativo</span>
                </label>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('levies.index') }}" class="btn-ghost">Cancelar</a>
                    <button class="btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
