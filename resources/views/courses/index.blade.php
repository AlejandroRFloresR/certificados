<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Listado de Cursos') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto">
        @if (session('success'))
            <div class="mb-4 text-green-600 font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-200 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2">Título</th>
                        <th class="px-4 py-2">Inicio</th>
                        <th class="px-4 py-2">Fin</th>
                        <th class="px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($courses as $course)
                        <tr>
                            <td class="px-4 py-2">{{ $course->title }}</td>
                            <td class="px-4 py-2">{{ $course->start_date }}</td>
                            <td class="px-4 py-2">{{ $course->end_date }}</td>
                            <td class="px-4 py-2 space-x-2">
                                <a href="{{ route('courses.edit', $course->id) }}"
                                   class="text-blue-600 hover:underline">Editar</a>

                                <form action="{{ route('courses.destroy', $course->id) }}"
                                      method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline"
                                            onclick="return confirm('¿Estás seguro?')">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if($courses->isEmpty())
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-gray-500">
                                No hay cursos cargados aún.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>