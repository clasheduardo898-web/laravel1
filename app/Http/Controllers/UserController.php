<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Usuarios/Index', [
            'usuarios' => User::with('roles:id,name')->orderBy('name')->get()
                ->map(fn ($u) => [
                    'id' => $u->id, 'name' => $u->name, 'email' => $u->email,
                    'roles' => $u->roles->pluck('name'),
                ]),
            'roles' => Role::pluck('name'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::create([
            'name' => $data['name'], 'email' => $data['email'], 'password' => bcrypt($data['password']),
        ]);
        $user->assignRole($data['roles']);

        session()->flash('message', 'Usuario creado.');
        return back();
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|min:2',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|min:6',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }
        $user->save();
        $user->syncRoles($data['roles']);

        session()->flash('message', 'Usuario actualizado.');
        return back();
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            session()->flash('error', 'No puedes eliminar tu propio usuario.');
            return back();
        }
        $user->delete();
        session()->flash('message', 'Usuario eliminado.');
        return back();
    }
}