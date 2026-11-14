<x-guest-layout>
    <h1 class="text-3xl font-extrabold tracking-tight">Criar conta</h1>
    <p class="mt-1.5 text-sm text-ink-mute">Leva menos de um minuto.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-7 space-y-5">
        @csrf
        <div>
            <x-input-label for="name" value="Nome" />
            <x-text-input id="name" class="mt-1.5 block w-full" type="text" name="name" :value="old('name')" autofocus required autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>
        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
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

        <x-primary-button class="w-full py-2.5">Criar conta</x-primary-button>
    </form>

    <p class="mt-6 text-sm text-ink-mute">Já tem conta? <a href="{{ route('login') }}" class="font-semibold text-ink underline decoration-rule decoration-2 underline-offset-4 hover:decoration-ok">Entrar</a></p>
</x-guest-layout>
