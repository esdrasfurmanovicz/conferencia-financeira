<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\LevyEstimate;
use App\Models\RateCard;
use App\Models\ReconciliationItem;
use App\Models\WithholdingProfile;
use App\Services\LevyCalculator;
use Illuminate\Http\Request;

class EstimateController extends Controller
{
    public function __construct(private LevyCalculator $calculator)
    {
    }

    public function calculate(Request $request)
    {
        $data = $request->validate([
            'rate_card_id' => 'required|exists:rate_cards,id',
            'withholding_profile_id' => 'required|exists:withholding_profiles,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $result = $this->calculator->estimate(
            RateCard::find($data['rate_card_id']),
            WithholdingProfile::find($data['withholding_profile_id']),
            (float) $data['amount']
        );

        if (!$result->ok) {
            return response()->json(['ok' => false, 'message' => $result->error], 422);
        }

        return response()->json([
            'ok' => true,
            'breakdown' => $result->breakdown,
            'levies_total' => $result->leviesTotal,
            'withheld_total' => $result->withheldTotal,
            'net_amount' => $result->netAmount,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'rate_card_id' => 'required|exists:rate_cards,id',
            'withholding_profile_id' => 'required|exists:withholding_profiles,id',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'reconciliation_item_id' => 'nullable|exists:reconciliation_items,id',
        ]);

        $client = Client::findOrFail($data['client_id']);
        $rateCard = RateCard::findOrFail($data['rate_card_id']);
        $profile = WithholdingProfile::findOrFail($data['withholding_profile_id']);

        $result = $this->calculator->estimate($rateCard, $profile, (float) $data['amount']);
        if (!$result->ok) {
            return response()->json(['ok' => false, 'message' => $result->error], 422);
        }

        $estimate = LevyEstimate::create([
            'client_id' => $client->id,
            'rate_card_id' => $rateCard->id,
            'withholding_profile_id' => $profile->id,
            'rate_card_name' => $rateCard->name,
            'withholding_profile_name' => $profile->name,
            'base_amount' => $data['amount'],
            'levies_total' => $result->leviesTotal,
            'withheld_total' => $result->withheldTotal,
            'net_amount' => $result->netAmount,
            'breakdown' => $result->breakdown,
            'notes' => $data['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        $linked = false;
        if (!empty($data['reconciliation_item_id'])) {
            $item = ReconciliationItem::with('reconciliation')
                ->where('client_id', $client->id)
                ->find($data['reconciliation_item_id']);
            if ($item && !$item->reconciliation->isClosed()) {
                $item->update(['levy_estimate_id' => $estimate->id]);
                $linked = true;
            }
        }

        return response()->json([
            'ok' => true,
            'linked' => $linked,
            'estimate' => $estimate->toPanelArray(),
            'count' => LevyEstimate::where('client_id', $client->id)->count(),
        ]);
    }

    public function history(Client $client)
    {
        $estimates = $client->estimates()->with('creator')->latest()->limit(50)->get()
            ->map(fn (LevyEstimate $e) => $e->toPanelArray());

        return response()->json(['client_name' => $client->name, 'estimates' => $estimates]);
    }
}
