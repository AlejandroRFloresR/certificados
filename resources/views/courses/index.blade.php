<x-app-layout>
    <x-slot name="header">
        <div class = "flex items-center justify-between">
        <h2 class="text-xl font-semibold text-white">
            {{ __('Listado de Cursos') }}
        </h2>
        
        <a href="{{ route('admin.courses.create') }}"
           class=" font-medium py-1 px-3 inline-flex items-center rounded-md bg-white border border-white text-hospitalblue hover:bg-hospitalblue hover:text-white">
            Crear Curso
        </a>
    </div>
    </x-slot>
    
    <div class="py-6 max-w-6xl mx-auto">
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full text-sm">
                <thead class="bg-blue-100">
                    <tr>
                        <th class="border px-4 py-2">Título</th>
                        <th class="border px-4 py-2">Inicio</th>
                        <th class="border px-4 py-2">Fin</th>
                        <th class="border px-4 py-2">Tutores</th>
                        <th class="border px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y bg-white">
                    @foreach ($courses as $course)
                        @auth
                        <tr>
                            <td class="text-center border px-4 py-2">{{ $course->title }}</td>
                            <td class="text-center border px-4 py-2">{{ $course->start_date }}</td>
                            <td class="text-center border px-4 py-2">{{ $course->end_date }}</td>
                            <td class="text-center border px-4 py-2">
                                @if($course->tutors->isEmpty())
                                    <span class="text-xs text-gray-400">Sin tutores asignados</span>
                                @else
                                    <ul class="list-disc list-inside text-xs text-gray-700">
                                        @foreach($course->tutors as $tutor)
                                            <li>
                                                {{-- Si el tutor está ligado a un user, mostrás el nombre del user;
                                                    si no, el name del tutor --}}
                                                {{ optional($tutor->user)->name ?? $tutor->name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td class="text-center border px-4 py-2 space-x-2">
                                @if(auth()->user()->HasRole('admin'))
                                <a href="{{ route('admin.courses.edit', $course->id) }}"
                                   class="rounded border border-blue-600 px-2 py-1 text-blue-600 hover:bg-blue-50">Editar</a>
                            
                            
                                <form action="{{ route('admin.courses.destroy', $course->id) }}"
                                      method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded bg-red-600 px-2 py-1 text-white hover:bg-red-700 disabled:opacity-60"
                                            onclick="return confirm('¿Estás seguro?')">
                                        Eliminar
                                    </button>
                                </form>
                                <a href="{{ route('admin.courses.users', $course->id) }}"    class="rounded border border-gray-400 px-2 py-1 text-gray-700 hover:bg-gray-50">   
                                    Listado de Alumnos
                                </a>
                                @endif
                               
                                 @if (!auth()->user()->courses->contains($course->id))
                                    <form method="POST" action="{{ route('courses.enroll', $course->id) }}" class="inline">
                                    @csrf
                                        <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                            Inscribirme
                                        </button>
                                    </form>
                                @else
                                <span class="inline-block rounded-full bg-gray-100 text-gray-800 text-xs px-2 py-1 border border-gray-300">
                                Ya inscrito
                                </span>
                                @endif
                            </td>
                        </tr>
                        @endauth
                    @endforeach
                    @if($courses->isEmpty())
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">
                                No hay cursos cargados aún.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>