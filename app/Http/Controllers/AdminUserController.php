<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageUsers(), 403);

        $search = trim((string) $request->query('q', ''));
        $usersQuery = User::query()->orderBy('name');

        if ($search !== '') {
            $normalizedSearch = Str::lower($search);

            $matchingRoleKeys = collect(User::availableRoles())
                ->filter(fn (string $label, string $key) => Str::contains(Str::lower($label.' '.$key), $normalizedSearch))
                ->keys()
                ->all();

            $usersQuery->where(function ($query) use ($normalizedSearch, $matchingRoleKeys) {
                $query
                    ->whereRaw('LOWER(name) LIKE ?', ['%'.$normalizedSearch.'%'])
                    ->orWhereRaw('LOWER(email) LIKE ?', ['%'.$normalizedSearch.'%'])
                    ->orWhereRaw('LOWER(role) LIKE ?', ['%'.$normalizedSearch.'%']);

                if ($matchingRoleKeys !== []) {
                    $query->orWhereIn('role', $matchingRoleKeys);
                }
            });
        }

        return view('admin.users', [
            'currentUser' => $request->user(),
            'users' => $usersQuery->get(),
            'search' => $search,
            'roles' => User::availableRoles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canManageUsers(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(array_keys(User::availableRoles()))],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'permissions' => $this->permissionsForRole($data['role']),
            'is_approved' => true,
        ]);

        return back()->with('success', 'Пользователь создан.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->canManageUsers(), 403);

        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys(User::availableRoles()))],
        ]);

        $user->update([
            'role' => $data['role'],
            'permissions' => $this->permissionsForRole($data['role']),
        ]);

        return back()->with('success', 'Роль пользователя обновлена.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->canManageUsers(), 403);

        if ($request->user()->is($user)) {
            return back()->with('error', 'Нельзя удалить самого себя.');
        }

        $user->delete();

        return back()->with('success', 'Пользователь удален.');
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->canManageUsers(), 403);

        $user->update([
            'is_approved' => true,
            'is_rejected' => false,
        ]);

        return back()->with('success', 'Пользователь одобрен.');
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->canManageUsers(), 403);

        $user->update([
            'is_approved' => false,
            'is_rejected' => true,
        ]);

        return back()->with('success', 'Пользователь отклонен.');
    }

    private function permissionsForRole(string $role): array
    {
        return User::defaultPermissionsByRole()[$role] ?? [];
    }
}
