<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function edit(User $user)
    {
        return view('users.form', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
        ]);

        if ($user->id === $request->user()->id && $data['role'] !== $user->role) {
            return back()->withInput()->with('error', 'Você não pode mudar o seu próprio perfil. Peça a outro administrador.');
        }

        if ($user->isAdmin() && $data['role'] !== User::ROLE_ADMIN && $this->isLastAdmin($user)) {
            return back()->withInput()->with('error', 'Este é o único administrador. Torne outra pessoa administradora antes de mudar o perfil dele.');
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->save();

        return redirect()->route('users.index')->with('success', 'Usuário atualizado.');
    }

    public function updatePassword(Request $request, User $user): RedirectResponse
    {
        $data = $request->validateWithBag('password', [
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => Str::random(60),
        ])->save();

        // A pessoa precisa entrar de novo com a senha nova (quando as sessões ficam no banco).
        if ($user->id !== $request->user()->id) {
            $this->endSessions($user);
        }

        return back()->with('success', 'Senha alterada.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Você não pode remover a sua própria conta por aqui. Use Meu perfil.');
        }

        if ($this->isLastAdmin($user)) {
            return back()->with('error', 'Não dá para remover o único administrador.');
        }

        $this->endSessions($user);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuário removido.');
    }

    private function isLastAdmin(User $user): bool
    {
        return $user->isAdmin()
            && User::where('role', User::ROLE_ADMIN)->where('id', '!=', $user->id)->doesntExist();
    }

    private function endSessions(User $user): void
    {
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }
    }
}
