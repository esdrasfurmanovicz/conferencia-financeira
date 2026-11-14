<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\LevyEstimate;
use App\Models\Reconciliation;
use App\Models\ReconciliationEvent;
use App\Models\ReconciliationItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The monthly checklist: one Reconciliation per period, with one ReconciliationItem per client
 * expected to be reconciled that month. This is the screen meant to replace a finance team's
 * spreadsheet — populate the month in bulk, tick items off as they're checked, and close the
 * month with an auditable trail instead of just overwriting cells.
 */
class ReconciliationController extends Controller
{
    public function index(Request $request)
    {
        $period = $this->periodFrom($request->query('month'));
        $reconciliation = Reconciliation::with('closedBy')->where('period', $period->toDateString())->first();

        $previous = Reconciliation::where('period', '<', $period->toDateString())
            ->whereHas('items')
            ->orderByDesc('period')
            ->first();

        $items = null;
        $total = 0;
        $confirmed = 0;
        $missingClients = 0;

        if ($reconciliation) {
            $query = ReconciliationItem::where('reconciliation_id', $reconciliation->id)
                ->with(['client.withholdingProfile', 'estimate', 'confirmedByUser']);

            if ($request->filled('status')) {
                $request->status === 'confirmed'
                    ? $query->whereNotNull('confirmed_at')
                    : $query->whereNull('confirmed_at');
            }
            if ($request->filled('name')) {
                $query->whereHas('client', fn ($q) => $q->where('name', 'like', '%' . $request->name . '%'));
            }

            $items = $query->orderBy(
                Client::query()->select('name')->whereColumn('clients.id', 'reconciliation_items.client_id')->limit(1)
            )->paginate(25)->withQueryString();

            $total = ReconciliationItem::where('reconciliation_id', $reconciliation->id)->count();
            $confirmed = ReconciliationItem::where('reconciliation_id', $reconciliation->id)->whereNotNull('confirmed_at')->count();
        }

        if (!$reconciliation || !$reconciliation->isClosed()) {
            $present = $reconciliation ? $reconciliation->items()->pluck('client_id') : collect();
            $missingClients = Client::where('active', true)
                ->when($present->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $present))
                ->count();
        }

        return view('reconciliations.index', [
            'period' => $period,
            'reconciliation' => $reconciliation,
            'previous' => $previous,
            'items' => $items,
            'total' => $total,
            'confirmed' => $confirmed,
            'missingClients' => $missingClients,
        ]);
    }

    public function populate(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'mode' => 'required|in:all,select,copy,empty',
            'client_ids' => 'required_if:mode,select|array|min:1',
            'client_ids.*' => 'integer',
        ]);

        $period = $this->periodFrom($request->month);
        $redirect = redirect()->route('reconciliations.index', ['month' => $period->format('Y-m')]);
        $added = 0;

        try {
            DB::transaction(function () use ($request, $period, &$added) {
                $reconciliation = Reconciliation::firstOrCreate(
                    ['period' => $period->toDateString()],
                    ['created_by' => $request->user()->id]
                );

                if ($reconciliation->isClosed()) {
                    throw new \DomainException('Este mês está fechado. Reabra para alterá-lo.');
                }

                if ($request->mode === 'all' || $request->mode === 'select') {
                    $clientsQuery = Client::where('active', true);
                    if ($request->mode === 'select') {
                        $clientsQuery->whereIn('id', $request->client_ids);
                    }

                    $present = $reconciliation->items()->pluck('client_id')->all();
                    $latestEstimates = LevyEstimate::selectRaw('client_id, MAX(id) as id')->groupBy('client_id')->pluck('id', 'client_id');

                    $clientsQuery->when($present, fn ($q) => $q->whereNotIn('id', $present))
                        ->chunkById(200, function ($clients) use ($reconciliation, $latestEstimates, &$added) {
                            $rows = $clients->map(fn (Client $client) => [
                                'reconciliation_id' => $reconciliation->id,
                                'client_id' => $client->id,
                                'levy_estimate_id' => $latestEstimates->get($client->id),
                                'created_by' => auth()->id(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ])->all();
                            ReconciliationItem::insert($rows);
                            $added += count($rows);
                        });
                } elseif ($request->mode === 'copy') {
                    if ($reconciliation->items()->exists()) {
                        throw new \DomainException('Este mês já tem itens.');
                    }

                    $source = Reconciliation::where('period', '<', $period->toDateString())
                        ->whereHas('items')->orderByDesc('period')->first();
                    if (!$source) {
                        throw new \DomainException('Não há um mês anterior com itens para copiar.');
                    }

                    $source->items()->chunkById(200, function ($sourceItems) use ($reconciliation, &$added) {
                        $rows = $sourceItems->map(fn (ReconciliationItem $item) => [
                            'reconciliation_id' => $reconciliation->id,
                            'client_id' => $item->client_id,
                            'levy_estimate_id' => $item->levy_estimate_id,
                            'label' => $item->label,
                            'created_by' => auth()->id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ])->all();
                        ReconciliationItem::insert($rows);
                        $added += count($rows);
                    });
                }
            });

            $message = $request->mode === 'empty'
                ? 'Mês aberto.'
                : ($added === 0 ? 'Nenhum item novo.' : $added . ' ' . ($added === 1 ? 'item adicionado.' : 'itens adicionados.'));

            return $redirect->with('success', $message);
        } catch (\DomainException $e) {
            return $redirect->with('error', $e->getMessage());
        }
    }

    public function addItem(Request $request, Reconciliation $reconciliation)
    {
        if ($reconciliation->isClosed()) {
            return $this->lockedResponse();
        }

        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'label' => 'nullable|string|max:120',
            'note' => 'nullable|string|max:500',
        ]);

        $item = ReconciliationItem::create([
            'reconciliation_id' => $reconciliation->id,
            'client_id' => $data['client_id'],
            'levy_estimate_id' => LevyEstimate::where('client_id', $data['client_id'])->max('id'),
            'label' => $data['label'] ?? null,
            'note' => $data['note'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['ok' => true, 'id' => $item->id]);
    }

    public function updateItem(Request $request, ReconciliationItem $item)
    {
        if ($item->reconciliation->isClosed()) {
            return $this->lockedResponse();
        }

        $data = $request->validate([
            'label' => 'nullable|string|max:120',
            'note' => 'nullable|string|max:500',
        ]);
        $item->update($data);

        return response()->json(['ok' => true]);
    }

    public function destroyItem(ReconciliationItem $item)
    {
        if ($item->reconciliation->isClosed()) {
            return $this->lockedResponse();
        }

        $reconciliationId = $item->reconciliation_id;
        $item->delete();

        return response()->json(['ok' => true] + $this->counts($reconciliationId));
    }

    public function confirm(Request $request, ReconciliationItem $item)
    {
        if ($item->reconciliation->isClosed()) {
            return $this->lockedResponse();
        }

        $request->validate(['confirmed' => 'required|boolean']);
        $this->applyConfirm($item, $request->boolean('confirmed'), $request->user()->id);

        return response()->json([
            'ok' => true,
            'confirmed' => $item->isConfirmed(),
            'confirmed_at' => $item->confirmed_at?->format('d/m/Y H:i'),
            'confirmed_by' => $item->confirmedByUser?->name,
        ] + $this->counts($item->reconciliation_id));
    }

    public function confirmBulk(Request $request, Reconciliation $reconciliation)
    {
        if ($reconciliation->isClosed()) {
            return $this->lockedResponse();
        }

        $request->validate([
            'ids' => 'required|array|max:200',
            'ids.*' => 'integer',
            'confirmed' => 'required|boolean',
        ]);

        $confirmed = $request->boolean('confirmed');
        $userId = $request->user()->id;
        $items = ReconciliationItem::where('reconciliation_id', $reconciliation->id)->whereIn('id', $request->ids)->get();

        DB::transaction(function () use ($items, $confirmed, $userId) {
            $items->each(fn (ReconciliationItem $item) => $this->applyConfirm($item, $confirmed, $userId));
        });

        return response()->json(['ok' => true] + $this->counts($reconciliation->id));
    }

    public function close(Request $request, Reconciliation $reconciliation)
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        $counts = DB::transaction(function () use ($request, $reconciliation) {
            $locked = Reconciliation::lockForUpdate()->find($reconciliation->id);
            if ($locked->isClosed()) {
                return null;
            }

            $counts = $this->counts($locked->id);
            $locked->update(['status' => Reconciliation::CLOSED, 'closed_at' => now(), 'closed_by' => $request->user()->id]);

            ReconciliationEvent::create([
                'reconciliation_id' => $locked->id,
                'action' => ReconciliationEvent::CLOSED,
                'user_id' => $request->user()->id,
                'reason' => $request->note,
                'total_items' => $counts['total'],
                'confirmed_items' => $counts['confirmed'],
                'created_at' => now(),
            ]);

            return $counts;
        });

        if ($counts === null) {
            return response()->json(['ok' => false, 'message' => 'Este mês já está fechado.'], 422);
        }

        return response()->json(['ok' => true]);
    }

    public function reopen(Request $request, Reconciliation $reconciliation)
    {
        $request->validate(['reason' => 'required|string|min:3|max:500']);

        $reopened = DB::transaction(function () use ($request, $reconciliation) {
            $locked = Reconciliation::lockForUpdate()->find($reconciliation->id);
            if (!$locked->isClosed()) {
                return false;
            }

            $counts = $this->counts($locked->id);
            $locked->update(['status' => Reconciliation::OPEN, 'closed_at' => null, 'closed_by' => null]);

            ReconciliationEvent::create([
                'reconciliation_id' => $locked->id,
                'action' => ReconciliationEvent::REOPENED,
                'user_id' => $request->user()->id,
                'reason' => $request->reason,
                'total_items' => $counts['total'],
                'confirmed_items' => $counts['confirmed'],
                'created_at' => now(),
            ]);

            return true;
        });

        if (!$reopened) {
            return response()->json(['ok' => false, 'message' => 'Este mês não está fechado.'], 422);
        }

        return response()->json(['ok' => true]);
    }

    public function events(Reconciliation $reconciliation)
    {
        $events = $reconciliation->events()->with('user')->orderByDesc('id')->get()->map(fn (ReconciliationEvent $e) => [
            'action' => $e->action,
            'user' => $e->user?->name,
            'at' => $e->created_at?->format('d/m/Y H:i'),
            'reason' => $e->reason,
            'total' => $e->total_items,
            'confirmed' => $e->confirmed_items,
        ]);

        return response()->json(['events' => $events]);
    }

    private function applyConfirm(ReconciliationItem $item, bool $confirmed, int $userId): void
    {
        $item->update([
            'confirmed_at' => $confirmed ? now() : null,
            'confirmed_by' => $confirmed ? $userId : null,
        ]);
    }

    private function counts(int $reconciliationId): array
    {
        return [
            'total' => ReconciliationItem::where('reconciliation_id', $reconciliationId)->count(),
            'confirmed' => ReconciliationItem::where('reconciliation_id', $reconciliationId)->whereNotNull('confirmed_at')->count(),
        ];
    }

    private function lockedResponse()
    {
        return response()->json(['ok' => false, 'message' => 'Este mês está fechado. Reabra para editar.'], 423);
    }

    private function periodFrom(?string $month): Carbon
    {
        try {
            return Carbon::createFromFormat('!Y-m', (string) $month)->startOfMonth();
        } catch (\Exception $e) {
            return Carbon::now()->startOfMonth();
        }
    }
}
