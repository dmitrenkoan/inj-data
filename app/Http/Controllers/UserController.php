<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Battalion;
use App\Models\Brigade;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        $user = $request->user();

        $users = User::query()
            ->with(['brigade', 'battalion'])
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('brigade_id', $user->brigade_id))
            ->orderBy('name')
            ->get();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $this->roleOptions($user),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        Gate::authorize('create', User::class);

        return Inertia::render('Users/Create', $this->formOptions($request));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $this->normalize($request->validated());
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return Redirect::route('users.index')->with('status', 'Користувача додано.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, User $user): Response
    {
        Gate::authorize('update', $user);

        return Inertia::render('Users/Edit', [
            ...$this->formOptions($request),
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $this->normalize($request->validated());

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return Redirect::route('users.index')->with('status', 'Користувача оновлено.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return Redirect::route('users.index')->with('status', 'Користувача видалено.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        $role = UserRole::from($data['role']);

        if ($role === UserRole::SuperAdmin) {
            $data['brigade_id'] = null;
            $data['battalion_id'] = null;
        } elseif ($role === UserRole::Brigade) {
            $data['battalion_id'] = null;
        } elseif ($role === UserRole::Battalion && ! empty($data['battalion_id'])) {
            $data['brigade_id'] = Battalion::find($data['battalion_id'])?->brigade_id;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(Request $request): array
    {
        $user = $request->user();

        return [
            'roles' => $this->roleOptions($user),
            'brigades' => $user->isSuperAdmin() ? Brigade::query()->orderBy('name')->get() : [Brigade::find($user->brigade_id)],
            'battalions' => Battalion::query()->visibleTo($user)->with('brigade')->orderBy('name')->get(),
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function roleOptions(User $user): array
    {
        $roles = $user->isSuperAdmin() ? UserRole::cases() : [UserRole::Brigade, UserRole::Battalion];

        return array_map(fn ($role) => ['value' => $role->value, 'label' => $role->label()], $roles);
    }
}
