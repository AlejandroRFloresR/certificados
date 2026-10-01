<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index(Request $request): Response
    {
        $q    = trim((string) $request->query('q', ''));
        $role = (string) $request->query('role', '');

        $users = User::with('roles:id,name')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('dni', 'like', "%{$q}%")))
            ->when($role !== '', fn ($query) => $query->role($role))
            ->latest()
            ->paginate(15)
            ->withQueryString()   // mantiene ?q= y ?role= al cambiar de página
            ->through(fn (User $u) => [
                'id'       => $u->id,
                'name'     => $u->name,
                'email'    => $u->email,
                'dni'      => $u->dni,
                'telefono' => $u->telefono,
                'role'     => $u->roles->first()?->name,
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users'   => $users,
            'filters' => ['q' => $q, 'role' => $role],
            'roles'   => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function create(): Response
    {
        return $this->form(null);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(null));

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'dni'      => $data['dni'],
            'telefono' => $data['telefono'],
        ]);

        $user->syncRoles([$data['role']]);
        $this->syncTutorProfile($user);

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user): Response
    {
        return $this->form($user);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate($this->rules($user));

        $user->fill([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'dni'      => $data['dni'],
            'telefono' => $data['telefono'],
        ]);

        // Contraseña opcional: solo si se completó
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        // Rol: no se puede cambiar el propio, ni quitar el último admin
        if ($user->id !== auth()->id() && !empty($data['role'])) {
            $currentRole = $user->roles->pluck('name')->first();

            if ($currentRole === 'admin' && $data['role'] !== 'admin' && User::role('admin')->count() <= 1) {
                return back()->withErrors(['role' => 'No podés quitar el último administrador.']);
            }

            $user->syncRoles([$data['role']]);
            $this->syncTutorProfile($user);
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado.');
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|exists:roles,name']);

        $user->syncRoles([$request->role]);
        $this->syncTutorProfile($user);

        return back()->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No podés eliminar tu propio usuario.');
        }

        optional($user->tutor)->delete();
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado.');
    }

    /** Misma página para crear y editar */
    private function form(?User $user): Response
    {
        return Inertia::render('Admin/Users/Form', [
            'user' => $user ? [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'dni'      => $user->dni,
                'telefono' => $user->telefono,
                'role'     => $user->getRoleNames()->first(),
                'is_self'  => $user->id === auth()->id(),
            ] : null,
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    private function rules(?User $user): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'dni'      => ['required', 'string', 'max:20', Rule::unique('users', 'dni')->ignore($user?->id)],
            'telefono' => ['required', 'string', 'max:20'],
            // Obligatoria al crear, opcional al editar
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            // Obligatorio al crear; al editarse a sí mismo no se envía
            'role'     => [$user ? 'nullable' : 'required', 'exists:roles,name'],
        ];
    }

    /**
     * El perfil Tutor existe solo si el usuario tiene rol tutor.
     * Al quitarle el rol se borra el perfil (y con él su firma y sus cursos como tutor).
     */
    private function syncTutorProfile(User $user): void
    {
        if ($user->hasRole('tutor')) {
            Tutor::firstOrCreate(['user_id' => $user->id], ['name' => $user->name]);
        } elseif ($user->tutor) {
            $user->tutor()->delete();
        }
    }
}
