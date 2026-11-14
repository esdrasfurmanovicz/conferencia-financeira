{{--
    Caixa de confirmação única do sistema. Duas formas de usar:
      1) Em um formulário:  <form data-confirm="Texto" data-confirm-title="Título" data-confirm-label="Remover">
      2) No Alpine/JS:      if (!await confirmAction({ title, message, label, tone: 'danger' })) return;
    tone: 'danger' (vermelho, padrão) ou 'primary'.
--}}
<div
    x-data="confirmDialog()"
    x-on:keydown.escape.window="open && answer(false)"
    x-show="open" x-cloak
    class="fixed inset-0 z-[70] flex items-center justify-center p-4"
    role="alertdialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-message"
>
    <div class="overlay" style="z-index: 0" @click="answer(false)"></div>

    <div x-ref="card" tabindex="-1" class="dialog relative z-10 max-w-md focus:outline-none"
        x-show="open"
        x-transition:enter="transition duration-150 ease-out"
        x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100">
        <div class="flex gap-4">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full"
                :class="opts.tone === 'primary' ? 'bg-ok-soft text-ok-ink' : 'bg-bad-soft text-bad'">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </span>
            <div class="min-w-0">
                <h2 id="confirm-title" class="text-lg font-extrabold leading-snug tracking-tight" x-text="opts.title"></h2>
                <p id="confirm-message" class="mt-1.5 text-sm leading-relaxed text-ink-mute" x-text="opts.message"></p>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <button type="button" class="btn-secondary" @click="answer(false)" x-text="opts.cancelLabel"></button>
            <button type="button" x-ref="ok" class="btn" :class="opts.tone === 'primary' ? 'btn-primary' : 'btn-danger'" @click="answer(true)" x-text="opts.label"></button>
        </div>
    </div>
</div>

<script>
    function confirmDialog() {
        const defaults = { title: 'Tem certeza?', message: 'Esta ação não pode ser desfeita.', label: 'Confirmar', cancelLabel: 'Cancelar', tone: 'danger' };
        return {
            open: false,
            opts: { ...defaults },
            resolver: null,
            init() {
                // Uso pelo JS das páginas: await confirmAction({...})
                window.confirmAction = (o = {}) => new Promise(resolve => {
                    this.opts = { ...defaults, ...o };
                    this.resolver = resolve;
                    this.open = true;
                    // Foco no "Cancelar" seria mais seguro; foco no botão principal agiliza o teclado.
                    this.$nextTick(() => this.$refs.ok.focus());
                });

                // Uso em formulários com data-confirm
                document.addEventListener('submit', async (e) => {
                    const form = e.target;
                    if (!(form instanceof HTMLFormElement) || !form.dataset.confirm || form.dataset.confirmed === '1') return;
                    e.preventDefault();
                    const ok = await window.confirmAction({
                        title: form.dataset.confirmTitle || undefined,
                        message: form.dataset.confirm,
                        label: form.dataset.confirmLabel || 'Remover',
                        tone: form.dataset.confirmTone || 'danger',
                    });
                    if (ok) { form.dataset.confirmed = '1'; form.submit(); }
                });
            },
            answer(value) {
                this.open = false;
                if (this.resolver) { this.resolver(value); this.resolver = null; }
            },
        };
    }
</script>
