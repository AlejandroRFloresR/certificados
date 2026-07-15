<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">
                Usuarios
            </h2>
            <div class="">
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center rounded-md border border-white bg-white px-3 py-1 text-hospitalblue font-medium hover:bg-hospitalblue hover:text-white">
                Crear usuario
            </a>
            <a href="{{ route('admin.users.import.create') }}"
               class="inline-flex items-center rounded-md border border-white bg-white px-3 py-1 text-hospitalblue font-medium hover:bg-hospitalblue hover:text-white">
                Importar usuarios
            </a>
            </div>
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

        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-blue-100">
                        <th class="px-4 py-2 text-center">ID</th>
                        <th class="px-4 py-2 text-center">Nombre</th>
                        <th class="px-4 py-2 text-center">Email</th>
                        <th class="px-4 py-2 text-center">DNI</th>
                        <th class="px-4 py-2 text-center">Teléfono</th>
                        <th class="px-4 py-2 text-center">Rol</th>
                        <th class="px-4 py-2 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php $currentRole = $user->roles->pluck('name')->first(); @endphp
                        <tr>
                            <td class="border px-4 py-2 text-center">{{ $users->firstItem() + $loop->index }}</td>
                            <td class="border px-4 py-2 text-center">{{ $user->name }}</td>
                            <td class="border px-4 py-2 text-center">{{ $user->email }}</td>
                            <td class="border px-4 py-2 text-center">{{ $user->dni }}</td>
                            <td class="border px-4 py-2 text-center">{{ $user->telefono }}</td>
                            <td class="border px-4 py-2 text-center">
                                {{-- Columna Rol (solo lectura) --}}
                                @php $currentRole = $user->roles->pluck('name')->first(); @endphp
                                <span class="inline-block rounded-full bg-gray-100 text-gray-800 text-xs px-2 py-1 border border-gray-300">
                                {{ $currentRole ? ucfirst($currentRole) : 'Sin rol' }}
                                </span>
                            </td>

                            {{-- Acciones --}}
                            <td class="border px-4 py-2 text-center">
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="rounded border border-blue-600 px-2 py-1 text-blue-600 hover:bg-blue-50">
                                        Editar
                                    </a>

                                    <a href="{{ route('admin.users.edit-password', $user) }}"
                                       class="rounded border border-gray-400 px-2 py-1 text-gray-700 hover:bg-gray-50">
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
