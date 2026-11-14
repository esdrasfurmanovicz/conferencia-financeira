<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.head')
    </head>
    <body class="font-sans">
        <div class="grid min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
            {{-- Painel da marca --}}
            <section class="relative hidden overflow-hidden bg-ink p-12 text-white lg:flex lg:flex-col lg:justify-between">
                <a href="/" class="flex items-center gap-3">
                    <x-application-logo class="h-10 w-10 text-ok" />
                    <span class="text-lg font-extrabold tracking-tight">Conferência financeira</span>
                </a>

                <div class="max-w-md">
                    <h1 class="text-4xl font-extrabold leading-[1.1] tracking-tight">Todo cliente do mês, conferido e fechado.</h1>
                    <p class="mt-4 text-white/65">Simule os impostos, marque cada cliente como conferido e feche o mês com histórico de quem fez o quê.</p>
                </div>

                {{-- Amostra do que a pessoa vai usar: três linhas da conferência --}}
                <ul class="max-w-md space-y-2.5" aria-hidden="true">
                    @foreach ([['Padaria Estrela', 'R$ 12.480,00', true], ['Oficina Rocha', 'R$ 8.915,40', true], ['Clínica Vida', 'R$ 21.300,00', false]] as [$n, $v, $done])
                        <li class="flex items-center gap-3 rounded-xl bg-white/[.07] px-4 py-3">
                            <span class="grid h-6 w-6 place-items-center rounded-full {{ $done ? 'bg-ok' : 'border-2 border-white/30' }}">
                                @if ($done)<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5l4.5 4.5L19 7.5"/></svg>@endif
                            </span>
                            <span class="flex-1 text-sm font-semibold">{{ $n }}</span>
                            <span class="text-sm tabular-nums text-white/60">{{ $v }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- Formulário --}}
            <section class="flex min-h-screen flex-col justify-center px-6 py-10 sm:px-12">
                <a href="/" class="mb-8 flex items-center gap-2.5 lg:hidden">
                    <x-application-logo class="h-9 w-9 text-ok" />
                    <span class="font-extrabold tracking-tight">Conferência financeira</span>
                </a>
                <div class="w-full max-w-sm">
                    {{ $slot }}
                </div>
            </section>
        </div>
    </body>
</html>
