<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">
                Usuarios
            </h2>
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center rounded-md border border-white bg-white px-3 py-1 text-hospitalblue font-medium hover:bg-hospitalblue hover:text-white">
                Crear usuario
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto">
        {{-- flashes --}}
        @if (session('success'))
            <div class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-2 text-green-700">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-2 text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="overflow-x-auto rounded border border-gray-200 bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Nombre</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">DNI</th>
                        <th class="px-4 py-2 text-left">Teléfono</th>
                        <th class="px-4 py-2 text-left">Rol</th>
                        <th class="px-4 py-2 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php $currentRole = $user->roles->pluck('name')->first(); @endphp
                        <tr class="border-b border-gray-200">
                            <td class="px-4 py-2">{{ $user->id }}</td>
                            <td class="px-4 py-2">{{ $user->name }}</td>
                            <td class="px-4 py-2">{{ $user->email }}</td>
                            <td class="px-4 py-2">{{ $user->dni }}</td>
                            <td class="px-4 py-2">{{ $user->telefono }}</td>

                            {{-- Cambio rápido de rol --}}
                            <td class="px-4 py-2">
                                <form method="POST" action="{{ route('admin.users.assign-role', $user) }}" id="role-form-{{ $user->id }}" class="flex items-center gap-2">
                                    @csrf
                                    <select name="role"
                                            class="rounded border border-gray-300 px-2 py-1 "
                                            onchange="document.getElementById('role-form-{{ $user->id }}').submit();">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" @selected($currentRole === $role->name)>{{ ucfirst($role->name) }}</option>
                                        @endforeach
                                    </select>
                                    <noscript>
                                        <button type="submit" class="rounded border px-2 py-1">Actualizar</button>
                                    </noscript>
                                </form>
                            </td>

                            {{-- Acciones --}}
                            <td class="px-4 py-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="rounded border border-blue-600 px-2 py-1 text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-800">
                                        Editar
                                    </a>

                                    <a href="{{ route('admin.users.edit-password', $user) }}"
                                       class="rounded border border-gray-400 px-2 py-1 text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">
                                        Contraseña
                                    </a>

                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          onsubmit="return confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.');"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded bg-red-600 px-2 py-1 text-white hover:bg-red-700 disabled:opacity-60"
                                                @if ($user->id === auth()->id()) disabled title="No puedes eliminar tu propio usuario" @endif>
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">No hay usuarios para mostrar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
