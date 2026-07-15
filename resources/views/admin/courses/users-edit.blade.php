<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Asignar alumnos — {{ $course->title }}
            </h2>
            <a href="{{ route('admin.courses.users', $course) }}"
               class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-medium text-hospitalblue hover:bg-gray-300">
               Volver al listado
            </a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto py-6 space-y-4">

        @if (session('success'))
            <div class="rounded border border-green-300 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Buscador --}}
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ $q }}"
                   placeholder="Buscar por nombre, email o DNI"
                   class="w-full md:max-w-md rounded border-gray-300">
            <button class="rounded bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">Buscar</button>
            @if($q !== '')
                <a href="{{ route('admin.courses.users.edit', $course) }}"
                   class="rounded px-3 py-2 border text-gray-700 hover:bg-gray-50">Limpiar</a>
            @endif
        </form>

        <form method="POST" action="{{ route('admin.courses.users.update', $course) }}">
            @csrf
            @method('PUT')

            <div class="overflow-hidden rounded border border-gray-200">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left"></th>
                            <th class="px-3 py-2 text-left">Nombre</th>
                            <th class="px-3 py-2 text-left">Email</th>
                            <th class="px-3 py-2 text-left">DNI</th>
                            <th class="px-3 py-2 text-left">Teléfono</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr class="border-t">
                                <td class="px-3 py-2 text-center">
                                    <input type="checkbox"
                                           class="chk-user"
                                           name="users[]"
                                           value="{{ $u->id }}"
                                           @checked(in_array($u->id, $enrolledIds, true))>
                                </td>
                                <td class="px-3 py-2">{{ $u->name }}</td>
                                <td class="px-3 py-2">{{ $u->email }}</td>
                                <td class="px-3 py-2">{{ $u->dni }}</td>
                                <td class="px-3 py-2">{{ $u->telefono }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-gray-500">
                                    No se encontraron usuarios.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex items-center gap-2">
                <a href="{{ route('admin.courses.users', $course) }}"
                   class="inline-flex items-center rounded-md border px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                   Cancelar
                </a>
                <button class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 bg-blue-600">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
