<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Editar usuário</h1>
        <p class="page-lead">{{ $user->name }} · {{ $user->email }}</p>
    </x-slot>

    <div class="max-w-3xl space-y-6">
        <x-flash />

        <form method="POST" action="{{ route('users.update', $user) }}" class="panel space-y-6 p-6 sm:p-9">
            @csrf @method('PUT')

            <div>
                <x-input-label for="name" value="Nome" />
                <x-text-input id="name" name="name" class="mt-1.5 block w-full" value="{{ old('name', $user->name) }}" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
            </div>

            <div>
                <x-input-label for="email" value="E-mail" />
                <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full" value="{{ old('email', $user->email) }}" required />
                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
            </div>

            <div>
                <x-input-label for="role" value="Perfil de acesso" />
                <select id="role" name="role" class="mt-1.5 block w-full" @disabled($user->id === auth()->id())>
                    @foreach (\App\Models\User::ROLES as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @if ($user->id === auth()->id())
                    <input type="hidden" name="role" value="{{ $user->role }}">
                    <p class="mt-1.5 text-sm text-ink-mute">Você não pode mudar o seu próprio perfil. Peça a outro administrador.</p>
                @else
                    <p class="mt-1.5 text-sm text-ink-mute">Administrador gerencia usuários (editar, trocar senha e remover). Usuário só usa a conferência e os cadastros.</p>
                @endif
                <x-input-error :messages="$errors->get('role')" class="mt-1.5" />
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('users.index') }}" class="btn-ghost">Voltar</a>
                <button class="btn-primary">Salvar</button>
            </div>
        </form>

        <form method="POST" action="{{ route('users.password', $user) }}" class="panel space-y-6 p-6 sm:p-9" data-tour="user-password">
            @csrf @method('PATCH')

            <div>
                <h2 class="text-lg font-bold text-ink">Alterar senha</h2>
                <p class="mt-1 text-sm text-ink-mute">
                    @if ($user->id === auth()->id())
                        Defina uma senha nova para a sua conta.
                    @else
                        Defina uma senha nova para esta pessoa. Ela precisará entrar de novo com ela.
                    @endif
                </p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="password" value="Nova senha" />
                    <x-text-input id="password" name="password" type="password" class="mt-1.5 block w-full" autocomplete="new-password" />
                    <x-input-error :messages="$errors->password->get('password')" class="mt-1.5" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Confirmar nova senha" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1.5 block w-full" autocomplete="new-password" />
                </div>
            </div>

            <div class="flex justify-end">
                <button class="btn-primary">Alterar senha</button>
            </div>
        </form>
    </div>
</x-app-layout>
