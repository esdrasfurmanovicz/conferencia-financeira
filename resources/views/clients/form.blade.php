<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">
            {{ $client->exists ? 'Editar cliente' : 'Novo cliente' }}
        </h1>
    </x-slot>

    <div class="">
        <div class="max-w-3xl">
            <form method="POST" action="{{ $client->exists ? route('clients.update', $client) : route('clients.store') }}"
                class="panel space-y-6 p-6 sm:p-9">
                @csrf
                @if ($client->exists) @method('PUT') @endif

                <div>
                    <x-input-label for="name" value="Nome" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" value="{{ old('name', $client->name) }}" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="document" value="CPF/CNPJ" />
                    <x-text-input id="document" name="document" class="mt-1 block w-full" value="{{ old('document', $client->document) }}" />
                </div>

                <div>
                    <x-input-label for="withholding_profile_id" value="Perfil de retenção" />
                    <select id="withholding_profile_id" name="withholding_profile_id" class="mt-1 block w-full rounded-lg border-rule">
                        <option value="">Sem perfil</option>
                        @foreach ($profiles as $profile)
                            <option value="{{ $profile->id }}" @selected(old('withholding_profile_id', $client->withholding_profile_id) == $profile->id)>
                                {{ $profile->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $client->active ?? true))>
                    <span>Ativo</span>
                </label>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('clients.index') }}" class="btn-ghost">Cancelar</a>
                    <button class="btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
