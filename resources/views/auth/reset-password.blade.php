<x-guest-layout>
    <h1 class="text-3xl font-extrabold tracking-tight">Nova senha</h1>
    <p class="mt-1.5 text-sm text-ink-mute">Escolha uma senha nova para sua conta.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-7 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email', $request->email)" autofocus required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>
        <div>
            <x-input-label for="password" value="Senha" />
            <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password"  required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>
        <div>
            <x-input-label for="password_confirmation" value="Confirmar senha" />
            <x-text-input id="password_confirmation" class="mt-1.5 block w-full" type="password" name="password_confirmation"  required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <x-primary-button class="w-full py-2.5">Salvar nova senha</x-primary-button>
    </form>
</x-guest-layout>
