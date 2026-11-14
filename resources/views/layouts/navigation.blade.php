@php
    $icon = [
        'check' => 'M4.5 12.75l6 6 9-13.5',
        'users' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'table' => 'M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5',
        'shield' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
        'cog' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z',
        'percent' => 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z M6 6h.008v.008H6V6z',
        'help' => 'M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z',
    ];

    $main = [
        ['reconciliations.*', 'reconciliations.index', 'Conferência mensal', 'check', 'nav-conferencia'],
    ];
    $registry = [
        ['levies.*', 'levies.index', 'Impostos', 'percent', 'nav-impostos'],
        ['rate-cards.*', 'rate-cards.index', 'Tabelas de alíquota', 'table', 'nav-tabelas'],
        ['withholding-profiles.*', 'withholding-profiles.index', 'Perfis de retenção', 'shield', 'nav-perfis'],
        ['clients.*', 'clients.index', 'Clientes', 'users', 'nav-clientes'],
    ];
@endphp

<aside
    class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-ink text-white transition-transform duration-200 lg:translate-x-0"
    :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
    aria-label="Navegação principal"
>
    <div class="flex h-16 items-center gap-3 px-5">
        <a href="{{ route('reconciliations.index') }}" class="flex items-center gap-2.5 rounded-lg">
            <x-application-logo class="h-8 w-8 text-ok" />
            <span class="text-[15px] font-extrabold leading-4 tracking-tight">Conferência<br><span class="font-medium text-white/60">financeira</span></span>
        </a>
        <button type="button" class="ml-auto rounded-lg p-1.5 text-white/60 hover:bg-white/10 lg:hidden" @click="sidebar = false" aria-label="Fechar menu">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4">
        <div class="space-y-1">
            @foreach ($main as [$pattern, $route, $label, $ic, $tourKey])
                <a href="{{ route($route) }}" data-tour="{{ $tourKey }}"
                   class="side-link {{ request()->routeIs($pattern) ? 'side-link-active' : '' }}"
                   @if (request()->routeIs($pattern)) aria-current="page" @endif>
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon[$ic] }}"/></svg>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div>
            <p class="px-3 pb-2 text-xs font-semibold text-white/45">Cadastros</p>
            <div class="space-y-1" data-tour="nav-cadastros">
                @foreach ($registry as [$pattern, $route, $label, $ic, $tourKey])
                    <a href="{{ route($route) }}" data-tour="{{ $tourKey }}"
                       class="side-link {{ request()->routeIs($pattern) ? 'side-link-active' : '' }}"
                       @if (request()->routeIs($pattern)) aria-current="page" @endif>
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon[$ic] }}"/></svg>
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        @if (Auth::user()->isAdmin())
            <div>
                <p class="px-3 pb-2 text-xs font-semibold text-white/45">Administração</p>
                <a href="{{ route('users.index') }}" data-tour="nav-usuarios"
                   class="side-link {{ request()->routeIs('users.*') ? 'side-link-active' : '' }}"
                   @if (request()->routeIs('users.*')) aria-current="page" @endif>
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon['cog'] }}"/></svg>
                    Usuários
                </a>
            </div>
        @endif
    </nav>

    <div class="space-y-1 border-t border-white/10 p-3" data-tour="nav-user">
        <div class="flex items-center gap-3 rounded-lg px-3 py-2">
            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-ok text-sm font-bold">{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold">{{ Auth::user()->name }}</p>
                <p class="truncate text-xs text-white/50">{{ Auth::user()->roleLabel() }}</p>
                <div class="flex gap-3 text-xs text-white/60">
                    <a href="{{ route('profile.edit') }}" class="hover:text-white hover:underline">Meu perfil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="hover:text-white hover:underline">Sair</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</aside>
