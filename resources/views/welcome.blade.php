<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Psica: cuidado clinico com escuta, presenca e continuidade.">
        <link rel="icon" type="image/png" href="{{ asset('favicon/favicon-96x96.png') }}" sizes="96x96">
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
        <title>Psica | Cuidado que acompanha</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,400;6..72,500&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root { --ink: #173b3f; --teal: #1d6461; --coral: #e17c61; --paper: #f5f1e9; --line: #d7ded5; }
            body { background: var(--paper); color: var(--ink); font-family: 'DM Sans', sans-serif; }
            .serif { font-family: 'Newsreader', Georgia, serif; }
            .hero-grid { background-image: linear-gradient(var(--line) 1px, transparent 1px), linear-gradient(90deg, var(--line) 1px, transparent 1px); background-size: 42px 42px; }
            .reveal { animation: rise .7s ease both; }
            .reveal-delay { animation-delay: .12s; }
            @keyframes rise { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
            @media (prefers-reduced-motion: reduce) { .reveal { animation: none; } }
        </style>
    </head>
    <body>
        <div class="min-h-screen overflow-hidden">
            <header class="relative z-10 mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-10">
                <a href="{{ url('/') }}" aria-label="Psica, inicio" class="flex items-center gap-3"><img src="{{ asset('images/logo-horizontal.png') }}" alt="Psica" class="h-10 w-auto"></a>
                <nav class="flex items-center gap-5 text-sm font-semibold" aria-label="Navegacao principal">
                    <a href="#como-funciona" class="hidden transition hover:text-[var(--coral)] sm:inline">Como funciona</a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="rounded-full border border-[var(--ink)] px-4 py-2 transition hover:bg-[var(--ink)] hover:text-white">Painel</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden transition hover:text-[var(--coral)] sm:inline">Entrar</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-full border border-[var(--ink)] px-4 py-2 transition hover:bg-[var(--ink)] hover:text-white">Equipe</a>
                        @endif
                    @endauth
                </nav>
            </header>

            <main>
                <section class="hero-grid relative mx-auto grid max-w-7xl items-center gap-12 px-6 pb-20 pt-12 lg:grid-cols-[1.1fr_.9fr] lg:px-10 lg:pb-28 lg:pt-20">
                    <div class="relative z-10 max-w-2xl reveal">
                        <p class="mb-6 text-xs font-bold uppercase tracking-[.24em] text-[var(--coral)]">Cuidado psicologico com presenca</p>
                        <h1 class="serif text-6xl leading-[.92] tracking-tight sm:text-8xl">Um lugar para <em class="text-[var(--teal)]">voltar a si.</em></h1>
                        <p class="mt-8 max-w-lg text-lg leading-8 text-[var(--ink)]/75">A Psica aproxima pessoas e profissionais para construir processos de cuidado possiveis, atentos e continuos.</p>
                        <div class="mt-9 flex flex-wrap items-center gap-4"><a href="{{ route('solicitar.create') }}" class="inline-flex items-center gap-3 rounded-full bg-[var(--coral)] px-6 py-3.5 font-bold text-white shadow-[4px_4px_0_var(--ink)] transition hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[2px_2px_0_var(--ink)]">Solicitar uma sessao <span aria-hidden="true">&#8594;</span></a><a href="#como-funciona" class="font-semibold underline decoration-[var(--coral)] decoration-2 underline-offset-4">Conhecer a Psica</a></div>
                    </div>
                    <div class="relative flex min-h-[360px] items-center justify-center reveal reveal-delay lg:min-h-[500px]"><div class="absolute h-72 w-72 rounded-full bg-[#d5e2d3] sm:h-96 sm:w-96"></div><div class="relative flex h-64 w-56 rotate-3 flex-col justify-between border-2 border-[var(--ink)] bg-[#f8f5ef] p-6 shadow-[12px_12px_0_var(--teal)] sm:h-80 sm:w-72"><div class="flex items-start justify-between"><span class="text-xs font-bold uppercase tracking-[.18em]">Psica</span><span class="text-2xl text-[var(--coral)]" aria-hidden="true">&#10033;</span></div><div><p class="serif text-4xl leading-none">escuta<br><span class="text-[var(--coral)]">tambem</span><br>e cuidado.</p><div class="mt-6 h-px w-16 bg-[var(--ink)]"></div><p class="mt-3 text-xs font-semibold uppercase tracking-[.14em]">presenca • processo • vinculo</p></div></div></div>
                </section>

                <section id="como-funciona" class="border-y border-[var(--line)] bg-[#e8eee5]"><div class="mx-auto grid max-w-7xl gap-10 px-6 py-16 lg:grid-cols-[.8fr_1.2fr] lg:px-10 lg:py-20"><div><p class="text-xs font-bold uppercase tracking-[.24em] text-[var(--coral)]">Um cuidado feito no seu ritmo</p><h2 class="serif mt-4 max-w-md text-5xl leading-none">Comecar pode ser simples.</h2></div><div class="grid gap-8 sm:grid-cols-3"><div class="border-t-2 border-[var(--teal)] pt-4"><span class="text-sm font-bold text-[var(--coral)]">01</span><h3 class="mt-3 text-lg font-bold">Conte o que precisa</h3><p class="mt-2 text-sm leading-6 text-[var(--ink)]/70">Compartilhe seus dados e escolha um horario disponivel.</p></div><div class="border-t-2 border-[var(--teal)] pt-4"><span class="text-sm font-bold text-[var(--coral)]">02</span><h3 class="mt-3 text-lg font-bold">Encontre seu espaco</h3><p class="mt-2 text-sm leading-6 text-[var(--ink)]/70">O profissional recebe sua solicitacao e confirma o encontro.</p></div><div class="border-t-2 border-[var(--teal)] pt-4"><span class="text-sm font-bold text-[var(--coral)]">03</span><h3 class="mt-3 text-lg font-bold">Siga acompanhado</h3><p class="mt-2 text-sm leading-6 text-[var(--ink)]/70">Seu processo ganha continuidade, clareza e cuidado.</p></div></div></div></section>

                <section class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-8 px-6 py-16 sm:flex-row sm:items-center lg:px-10 lg:py-24"><div><p class="text-xs font-bold uppercase tracking-[.24em] text-[var(--coral)]">Seu proximo passo</p><h2 class="serif mt-3 text-5xl leading-none">Vamos conversar?</h2></div><a href="{{ route('solicitar.create') }}" class="inline-flex items-center gap-3 rounded-full bg-[var(--teal)] px-6 py-3.5 font-bold text-white transition hover:bg-[var(--ink)]">Solicitar uma sessao <span aria-hidden="true">&#8594;</span></a></section>
            </main>

            <footer class="border-t border-[var(--line)] px-6 py-6 text-sm text-[var(--ink)]/65 lg:px-10"><div class="mx-auto flex max-w-7xl justify-between gap-4"><span>Psica</span><span>Cuidado com presenca.</span></div></footer>
        </div>
    </body>
</html>
