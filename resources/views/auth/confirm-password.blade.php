<x-guest-layout>
    <h1 class="text-3xl font-extrabold tracking-tight">Confirme sua senha</h1>
    <p class="mt-1.5 text-sm text-ink-mute">Esta é uma área protegida. Digite sua senha para continuar.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-7 space-y-5">
        @csrf
        <div>
            <x-input-label for="password" value="Senha" />
            <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password"  required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <x-primary-button class="w-full py-2.5">Confirmar</x-primary-button>
    </form>
</x-guest-layout>
