@if (session('success'))
    <div role="status" class="mb-4 flex items-start gap-3 rounded-xl border border-ok/25 bg-ok-soft px-4 py-3 text-sm font-medium text-ok-ink">
        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-ok"></span>
        {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div role="alert" class="mb-4 flex items-start gap-3 rounded-xl border border-bad/25 bg-bad-soft px-4 py-3 text-sm font-medium text-bad-ink">
        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-bad"></span>
        {{ session('error') }}
    </div>
@endif
