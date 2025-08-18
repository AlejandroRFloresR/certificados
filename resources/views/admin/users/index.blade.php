<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Administrar Usuarios</h2>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto">
        @if (session('success'))
            <div class="mb-4 text-green-600">{{ session('success') }}</div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow rounded p-6 overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b">
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol actual</th>
                        <th>Cambiar rol</th>
                        <th>Cambiar contraseña</th>
                        <th>Eliminar</th>
                        <th>Actualizar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border-b">
                            <td class="py-2 text-center">{{ $user->name }}</td>
                            <td class="py-2 text-center">{{ $user->email }}</td>
                            <td class="py-2 text-center">
                                {{ $user->roles->pluck('name')->implode(', ') ?: 'Sin rol' }}
                            </td>
                            <td class="py-2 text-center">
                                <form method="POST" action="{{ route('admin.users.assignRole', $user->id) }}">
                                    @csrf
                                    <select name="role" class="border rounded px-2 py-1">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                            <td class="py-2 text-center">
                                    <button type="submit" class="ml-2 px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                        Actualizar
                                    </button>
                                </form> <!-- 🔚 Cerramos aquí el form de cambiar rol -->
                            </td>
                            </td>
                            <td class="py-2 text-center">
                                <a href="{{ route('admin.users.editPassword', $user->id) }}" class="bg-yellow-400 text-white px-2 py-1 rounded">
                                    Cambiar Contraseña
                                </a>
                            </td>
                            <td class="py-2 text-center">
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>