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
                        @auth
                        <tr>
                            <td class="px-4 py-2">{{ $course->title }}</td>
                            <td class="px-4 py-2">{{ $course->start_date }}</td>
                            <td class="px-4 py-2">{{ $course->end_date }}</td>
                            <td class="px-4 py-2 space-x-2">
                                @if(auth()->user()->HasRole('admin'))
                                <a href="{{ route('courses.edit', $course->id) }}"
                                   class="text-blue-600 hover:underline">Editar</a>
                            </td>
                            <td class="px-4 py-2 space-x-2">
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
                                @endif
                            <td class="px-4 py-2 space-x-2">   
                                 @if (!auth()->user()->courses->contains($course->id))
                                    <form method="POST" action="{{ route('courses.enroll', $course->id) }}" class="inline">
                                    @csrf
                                        <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                            Inscribirme
                                        </button>
                                    </form>
                                @else
                                <span class="text-sm text-gray-500">Ya inscrito</span>
                                @endif
                            </td>
                            <td>
                                    <a href="{{ route('courses.certificate', $course->id) }}"   class="inline-block mt-2 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                        Descargar certificado
                                    </a>
                            </td>
                            <td class="px-4 py-2 space-x-2">
                                <a href="{{ route('admin.course.users', $course->id) }}"    class="inline-block mt-2 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">   
                                    Listado de Alumnos
                                </a>

                            </td>
                        </tr>
                        @endauth
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