<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.head')
    </head>
    <body class="font-sans">
        @php
            $page = \App\Support\Tours::forRoute(request()->route()?->getName());
            $welcome = \App\Support\Tours::welcome();
            $tourRegistry = ['welcome' => $welcome] + collect($page['tours'] ?? [])->keyBy('id')->all();
        @endphp

        <div class="min-h-screen" x-data="{ sidebar: false }" @keydown.escape.window="sidebar = false" @tour-sidebar.window="sidebar = $event.detail">
            {{-- Fundo escurecido do menu no celular --}}
            <div x-show="sidebar" x-cloak class="overlay lg:hidden" style="z-index: 30" @click="sidebar = false"></div>

            @include('layouts.navigation')

            <div class="lg:pl-64">
                {{-- Barra superior (celular) --}}
                <div class="sticky top-0 z-20 flex h-14 items-center gap-3 border-b border-rule bg-paper/90 px-4 backdrop-blur lg:hidden">
                    <button type="button" class="rounded-lg p-2 text-ink hover:bg-ink/5" @click="sidebar = true" aria-label="Abrir menu">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    </button>
                    <x-application-logo class="h-7 w-7 text-ok" />
                    <span class="text-sm font-extrabold tracking-tight">Conferência financeira</span>
                </div>

                <main class="mx-auto max-w-[1360px] px-4 py-6 sm:px-10 sm:py-12">
                    @isset($header)
                        <header class="mb-8">
                            {{ $header }}
                        </header>
                    @endisset

                    {{ $slot }}
                </main>
            </div>
        </div>

        <x-help-center :page="$page" />
        <x-tour-engine :tours="$tourRegistry" :auto-welcome="! Auth::user()->onboarded_at" />
        <x-confirm-dialog />

        @stack('scripts')
    </body>
</html>
