<?php

namespace App\Http\Controllers;

use App\Models\Levy;
use Illuminate\Http\Request;

class LevyController extends Controller
{
    public function index()
    {
        $levies = Levy::orderBy('name')->get();

        return view('levies.index', compact('levies'));
    }

    public function create()
    {
        return view('levies.form', ['levy' => new Levy]);
    }

    public function store(Request $request)
    {
        Levy::create($this->validated($request));

        return redirect()->route('levies.index')->with('success', 'Imposto cadastrado.');
    }

    public function edit(Levy $levy)
    {
        return view('levies.form', compact('levy'));
    }

    public function update(Request $request, Levy $levy)
    {
        $levy->update($this->validated($request));

        return redirect()->route('levies.index')->with('success', 'Imposto atualizado.');
    }

    public function destroy(Levy $levy)
    {
        $levy->delete();

        return redirect()->route('levies.index')->with('success', 'Imposto removido.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:80',
            'withholding_group' => 'nullable|string|max:40',
            'withholding_floor' => 'nullable|numeric|min:0',
            'active' => 'boolean',
        ]);
        $data['active'] = $request->boolean('active');

        return $data;
    }
}
