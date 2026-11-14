<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\WithholdingProfile;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::with('withholdingProfile')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->q . '%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        $profiles = WithholdingProfile::where('active', true)->orderBy('name')->get();

        return view('clients.form', ['client' => new Client, 'profiles' => $profiles]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Client::create($data);

        return redirect()->route('clients.index')->with('success', 'Cliente cadastrado.');
    }

    public function edit(Client $client)
    {
        $profiles = WithholdingProfile::where('active', true)->orWhere('id', $client->withholding_profile_id)->orderBy('name')->get();

        return view('clients.form', compact('client', 'profiles'));
    }

    public function update(Request $request, Client $client)
    {
        $client->update($this->validated($request));

        return redirect()->route('clients.index')->with('success', 'Cliente atualizado.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Cliente removido.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:160',
            'document' => 'nullable|string|max:20',
            'withholding_profile_id' => 'nullable|exists:withholding_profiles,id',
            'active' => 'boolean',
        ]);
        $data['active'] = $request->boolean('active');

        return $data;
    }
}
