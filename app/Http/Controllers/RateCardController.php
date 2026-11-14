<?php

namespace App\Http\Controllers;

use App\Models\Levy;
use App\Models\RateCard;
use App\Models\RateCardItem;
use Illuminate\Http\Request;

class RateCardController extends Controller
{
    public function index()
    {
        $rateCards = RateCard::withCount('items')->orderBy('name')->get();

        return view('rate_cards.index', compact('rateCards'));
    }

    public function create()
    {
        return view('rate_cards.form', ['rateCard' => new RateCard]);
    }

    public function store(Request $request)
    {
        $rateCard = RateCard::create($this->validated($request));

        return redirect()->route('rate-cards.edit', $rateCard)->with('success', 'Tabela criada. Agora adicione os impostos.');
    }

    public function edit(RateCard $rateCard)
    {
        $rateCard->load('items.levy');
        $availableLevies = Levy::where('active', true)->whereNotIn('id', $rateCard->items->pluck('levy_id'))->orderBy('name')->get();

        return view('rate_cards.form', compact('rateCard', 'availableLevies'));
    }

    public function update(Request $request, RateCard $rateCard)
    {
        $rateCard->update($this->validated($request));

        return redirect()->route('rate-cards.edit', $rateCard)->with('success', 'Tabela atualizada.');
    }

    public function destroy(RateCard $rateCard)
    {
        $rateCard->delete();

        return redirect()->route('rate-cards.index')->with('success', 'Tabela removida.');
    }

    public function addItem(Request $request, RateCard $rateCard)
    {
        $data = $request->validate([
            'levy_id' => 'required|exists:levies,id',
            'percentage' => 'required|numeric|min:0|max:100',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ]);

        RateCardItem::updateOrCreate(
            ['rate_card_id' => $rateCard->id, 'levy_id' => $data['levy_id']],
            $data
        );

        return redirect()->route('rate-cards.edit', $rateCard)->with('success', 'Imposto adicionado à tabela.');
    }

    public function destroyItem(RateCard $rateCard, RateCardItem $item)
    {
        abort_unless($item->rate_card_id === $rateCard->id, 404);
        $item->delete();

        return redirect()->route('rate-cards.edit', $rateCard)->with('success', 'Imposto removido da tabela.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);
        $data['active'] = $request->boolean('active');

        return $data;
    }
}
