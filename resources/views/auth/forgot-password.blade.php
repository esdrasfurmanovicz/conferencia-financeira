<x-guest-layout>
    <h1 class="text-3xl font-extrabold tracking-tight">Recuperar senha</h1>
    <p class="mt-1.5 text-sm text-ink-mute">Informe seu e-mail e enviaremos um link para criar uma nova senha.</p>

    <x-auth-session-status class="mt-5" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-7 space-y-5">
        @csrf
        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')" autofocus required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <x-primary-button class="w-full py-2.5">Enviar link</x-primary-button>
    </form>

    <p class="mt-6 text-sm"><a href="{{ route('login') }}" class="font-semibold text-ink underline decoration-rule decoration-2 underline-offset-4 hover:decoration-ok">Voltar para entrar</a></p>
</x-guest-layout>
