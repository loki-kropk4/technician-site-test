<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * Columns the users table may be sorted by. Anything outside this list
     * is rejected rather than passed to orderBy() directly.
     */
    private const SORTABLE_COLUMNS = ['id', 'name', 'email', 'role', 'created_at'];

    public function index(Request $request)
    {
        $sort = $request->query('sort', 'id');
        $sort = in_array($sort, self::SORTABLE_COLUMNS, true) ? $sort : 'id';

        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';

        $users = User::query()
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        $admin = User::where('role', UserRole::Admin)->first();

        return view('admin.users.index', [
            'users' => $users,
            'sort' => $sort,
            'direction' => $direction,
            'adminName' => $admin?->name,
        ]);
    }

    public function create()
    {
        return view('admin.users.form', ['user' => null]);
    }

    public function store(StoreUserRequest $request)
    {
        User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => UserRole::Technician,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Technician account created.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
        ];

        if ($request->filled('new_password')) {
            $data['password'] = Hash::make($request->validated('new_password'));
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "{$user->name}'s record was updated.");
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted.');
    }
}
