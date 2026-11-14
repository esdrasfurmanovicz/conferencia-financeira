<?php

namespace App\Http\Controllers;

use App\Models\Levy;
use App\Models\WithholdingProfile;
use Illuminate\Http\Request;

class WithholdingProfileController extends Controller
{
    public function index()
    {
        $profiles = WithholdingProfile::withCount('clients')->orderBy('name')->get();

        return view('withholding_profiles.index', compact('profiles'));
    }

    public function create()
    {
        return view('withholding_profiles.form', ['profile' => new WithholdingProfile]);
    }

    public function store(Request $request)
    {
        $profile = WithholdingProfile::create($this->validated($request));
        $this->syncRules($request, $profile);

        return redirect()->route('withholding-profiles.edit', $profile)->with('success', 'Perfil criado.');
    }

    public function edit(WithholdingProfile $profile)
    {
        $profile->load('rules');
        $levies = Levy::where('active', true)->orderBy('name')->get();
        $rulesByLevy = $profile->rules->keyBy('levy_id');

        return view('withholding_profiles.form', compact('profile', 'levies', 'rulesByLevy'));
    }

    public function update(Request $request, WithholdingProfile $profile)
    {
        $profile->update($this->validated($request));
        $this->syncRules($request, $profile);

        return redirect()->route('withholding-profiles.edit', $profile)->with('success', 'Perfil atualizado.');
    }

    public function destroy(WithholdingProfile $profile)
    {
        $profile->delete();

        return redirect()->route('withholding-profiles.index')->with('success', 'Perfil removido.');
    }

    private function syncRules(Request $request, WithholdingProfile $profile): void
    {
        $applies = $request->input('applies', []);
        $withheld = $request->input('withheld', []);

        foreach (Levy::where('active', true)->pluck('id') as $levyId) {
            $profile->rules()->updateOrCreate(
                ['levy_id' => $levyId],
                [
                    'applies' => in_array($levyId, $applies),
                    'withheld' => in_array($levyId, $withheld),
                ]
            );
        }
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
