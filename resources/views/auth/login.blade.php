<x-guest-layout>
    <h1 class="text-3xl font-extrabold tracking-tight">Entrar</h1>
    <p class="mt-1.5 text-sm text-ink-mute">Acesse a conferência do mês.</p>

    <x-auth-session-status class="mt-5" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5">
        @csrf
        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')" autofocus required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>
        <div>
            <x-input-label for="password" value="Senha" />
            <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password"  required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-ink-mute">
                <input id="remember_me" type="checkbox" name="remember">
                Manter conectado
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-ink underline decoration-rule decoration-2 underline-offset-4 hover:decoration-ok" href="{{ route('password.request') }}">Esqueci a senha</a>
            @endif
        </div>

        <x-primary-button class="w-full py-2.5">Entrar</x-primary-button>
    </form>

    @if (Route::has('register'))
        <p class="mt-6 text-sm text-ink-mute">Ainda não tem conta? <a href="{{ route('register') }}" class="font-semibold text-ink underline decoration-rule decoration-2 underline-offset-4 hover:decoration-ok">Criar conta</a></p>
    @endif
</x-guest-layout>
