<x-guest-layout>
    <h1 class="text-3xl font-extrabold tracking-tight">Confirme seu e-mail</h1>
    <p class="mt-1.5 text-sm text-ink-mute">Enviamos um link de confirmação para o seu e-mail. Clique nele para começar. Não chegou? Podemos enviar outro.</p>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-5 rounded-xl bg-ok-soft px-4 py-3 text-sm font-medium text-ok-ink">Enviamos um novo link para o e-mail do cadastro.</div>
    @endif

    <div class="mt-7 flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>Reenviar e-mail</x-primary-button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-sm font-semibold text-ink underline decoration-rule decoration-2 underline-offset-4 hover:decoration-ok">Sair</button>
        </form>
    </div>
</x-guest-layout>
