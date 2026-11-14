{{--
    Tours guiados: destacam elementos reais da tela e explicam onde fica e como usar cada coisa.
    Os passos vêm de App\Support\Tours. Abrir um tour: $dispatch('start-tour', { id: 'welcome' })
    ou o id de um tour da tela (ex.: 'reconciliations.index:simulate').
--}}
@props(['tours' => [], 'autoWelcome' => false])

<div
    x-data="tourEngine(@js($tours), @js($autoWelcome), @js(route('onboarding.complete')))"
    x-on:start-tour.window="start($event.detail.id)"
    x-on:keydown.escape.window="active && finish()"
    x-on:keydown.arrow-right.window="active && next()"
    x-on:keydown.arrow-left.window="active && prev()"
    x-on:resize.window.debounce.100ms="active && place()"
    x-on:scroll.window.passive="active && place()"
    x-show="active" x-cloak
    class="fixed inset-0 z-[80]"
    role="dialog" aria-modal="true" aria-labelledby="tour-title" aria-describedby="tour-body"
>
    {{-- Bloqueia cliques na página durante o tour; quando não há destaque, escurece tudo --}}
    <div class="absolute inset-0" :class="rect ? '' : 'bg-ink/60 backdrop-blur-[2px]'" @click="nudge()"></div>

    {{-- Destaque: um recorte claro com o resto da tela escurecido --}}
    <div x-show="rect" class="pointer-events-none fixed rounded-2xl transition-all duration-300 ease-out motion-reduce:transition-none"
        :style="rect ? `top:${rect.top}px;left:${rect.left}px;width:${rect.width}px;height:${rect.height}px;box-shadow:0 0 0 3px #3FD08F,0 0 0 9999px rgba(18,38,43,.66)` : ''"></div>

    {{-- Balão com a explicação --}}
    <div x-ref="pop" tabindex="-1"
        class="fixed flex flex-col rounded-3xl bg-white p-6 shadow-lift focus:outline-none"
        :class="rect ? '' : 'sm:p-8'"
        :style="`top:${pos.top}px;left:${pos.left}px;width:${pos.width}px;visibility:${ready ? 'visible' : 'hidden'}`">
        <div class="flex items-center justify-between gap-3">
            <p class="text-sm font-semibold text-ok-ink" x-text="tour ? `${tour.title} · ${i + 1} de ${steps.length}` : ''"></p>
            <button type="button" class="-mr-2 rounded-lg px-2 text-2xl leading-none text-ink-mute hover:bg-ink/5 hover:text-ink" @click="finish()" aria-label="Fechar tour">&times;</button>
        </div>
        <h2 id="tour-title" class="mt-1.5 text-xl font-extrabold leading-snug tracking-tight" x-text="step?.title"></h2>
        <p id="tour-body" class="mt-2 text-[15px] leading-relaxed text-ink-mute" x-text="step?.body"></p>

        <div class="mt-5 flex items-center gap-1.5" aria-hidden="true">
            <template x-for="(s, n) in steps" :key="n">
                <span class="h-1.5 rounded-full transition-all" :class="n === i ? 'w-5 bg-ok' : 'w-1.5 bg-ink/15'"></span>
            </template>
        </div>

        <div class="mt-5 flex items-center gap-2">
            <button type="button" class="btn-ghost btn-sm" @click="finish()" x-show="!last">Sair do tour</button>
            <div class="ml-auto flex gap-2">
                <button type="button" class="btn-secondary btn-sm" @click="prev()" x-show="i > 0">Voltar</button>
                <button type="button" class="btn-primary btn-sm" @click="last ? done() : next()"
                    x-text="last ? (step?.cta ? step.cta.label : 'Concluir') : 'Próximo'"></button>
            </div>
        </div>
    </div>
</div>

<script>
    function tourEngine(registry, autoWelcome, completeUrl) {
        const sleep = ms => new Promise(r => setTimeout(r, ms));
        const find = sel => {
            if (!sel) return null;
            const q = /^[\[#.]/.test(sel) ? sel : `[data-tour="${sel}"]`;
            // Pega o primeiro elemento visível (o mesmo data-tour pode se repetir em linhas).
            return [...document.querySelectorAll(q)].find(el => el.getClientRects().length > 0) || null;
        };

        return {
            active: false, ready: false,
            tour: null, steps: [], i: 0,
            rect: null,
            pos: { top: 0, left: 0, width: 360 },
            token: 0,

            get step() { return this.steps[this.i]; },
            get last() { return this.i === this.steps.length - 1; },

            init() {
                if (autoWelcome) setTimeout(() => this.start('welcome'), 700);
            },

            start(id) {
                const t = registry[id];
                if (!t) return;
                // Passos "optional" cujo elemento não existe nesta tela são descartados.
                const steps = t.steps.filter(s => !(s.optional && s.target && !find(s.target)));
                if (!steps.length) return;
                this.tour = t; this.steps = steps; this.i = 0;
                this.active = true;
                document.body.classList.add('overflow-y-hidden');
                this.go(0);
            },

            next() { if (!this.last) this.go(this.i + 1); },
            prev() { if (this.i > 0) this.go(this.i - 1); },

            // Clique fora do balão: balança o balão em vez de fechar sem querer
            nudge() { this.$refs.pop.focus({ preventScroll: true }); },

            async go(n) {
                const my = ++this.token;
                this.ready = false;
                this.i = n;
                const s = this.steps[n];

                // No celular o menu é uma gaveta: abre quando o passo aponta para ele.
                window.dispatchEvent(new CustomEvent('tour-sidebar', { detail: !!s.sidebar }));

                if (s.before) {
                    window.dispatchEvent(new CustomEvent(s.before, { detail: s.detail || null }));
                    await sleep(450);
                } else if (s.sidebar) {
                    await sleep(250);
                }
                if (my !== this.token) return;

                const el = this.targetEl();
                if (el) {
                    el.scrollIntoView({ block: window.innerWidth < 640 ? 'start' : 'center', behavior: 'auto' });
                    if (window.innerWidth < 640) window.scrollBy(0, -90);
                }
                this.place();
                await this.$nextTick();
                this.place();
                this.ready = true;
                this.$refs.pop.focus({ preventScroll: true });
            },

            targetEl() {
                const s = this.step;
                let el = find(s?.target);
                if (el && s.wrap && el.parentElement) el = el.parentElement;
                return el;
            },

            place() {
                const pop = this.$refs.pop;
                if (!pop || !this.step) return;
                const vw = window.innerWidth, vh = window.innerHeight;
                const el = this.targetEl();
                const small = vw < 640;
                const w = Math.min(small ? vw - 24 : (el ? 360 : 460), vw - 24);
                this.pos.width = w;
                const h = pop.offsetHeight || 240;

                if (!el) {
                    this.rect = null;
                    this.pos = { width: w, left: (vw - w) / 2, top: Math.max(12, (vh - h) / 2) };
                    return;
                }

                const r = el.getBoundingClientRect();
                const pad = 8;
                this.rect = { top: r.top - pad, left: r.left - pad, width: r.width + pad * 2, height: r.height + pad * 2 };

                const gap = 16;
                let top, left;
                if (small) {
                    // Celular: balão fixo embaixo (ou em cima, se o destaque estiver embaixo)
                    left = 12;
                    top = r.bottom + gap + h < vh ? r.bottom + gap + pad : Math.max(12, r.top - gap - pad - h);
                    if (top + h > vh - 12) top = vh - h - 12;
                } else if (r.right + gap + w + pad < vw) {            // à direita
                    left = r.right + gap + pad; top = r.top + r.height / 2 - h / 2;
                } else if (r.left - gap - pad - w > 12) {              // à esquerda
                    left = r.left - gap - pad - w; top = r.top + r.height / 2 - h / 2;
                } else if (r.bottom + gap + h + pad < vh) {            // embaixo
                    top = r.bottom + gap + pad; left = r.left + r.width / 2 - w / 2;
                } else {                                               // em cima
                    top = r.top - gap - pad - h; left = r.left + r.width / 2 - w / 2;
                }
this.pos = {
                    width: w,
                    left: Math.min(Math.max(12, left), vw - w - 12),
                    top: Math.min(Math.max(12, top), vh - h - 12),
                };
            },

            // Último passo com botão de ação (ex.: ir para outra tela)
            done() {
                const cta = this.step?.cta;
                this.finish();
                if (cta && cta.url && cta.url !== window.location.href) window.location.href = cta.url;
            },

            finish() {
                this.token++;
                const t = this.tour;
                this.active = false; this.ready = false; this.rect = null;
                document.body.classList.remove('overflow-y-hidden');
                window.dispatchEvent(new CustomEvent('tour-sidebar', { detail: false }));
                if (t?.after) window.dispatchEvent(new CustomEvent(t.after));
                // Sair ou concluir o tour de boas-vindas marca o primeiro acesso como visto
                if (t?.id === 'welcome') {
                    fetch(completeUrl, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    }).catch(() => {});
                }
            },
        };
    }
</script>
