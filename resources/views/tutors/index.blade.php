<x-app-layout>
    <x-slot name="header">
        <div class = "flex items-center justify-between">
        <h2 class="text-xl font-semibold text-white">
            {{ __('Listado de Tutores') }}
        </h2>
         <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center rounded-md border border-white bg-white px-3 py-1 text-hospitalblue font-medium hover:bg-hospitalblue hover:text-white">
                Crear tutor
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto">
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-blue-100">
                        <th class="border px-4 py-2 ">Nombre</th>
                        <th class="border px-4 py-2">Firma</th>
                        <th class="border px-4 py-2 ">Cursos</th>
                        <th class="border px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tutors as $tutor)
                        <tr>
                            <td class="text-center border  px-4 py-2">{{ $tutor->name }}</td>
                            <td class="text-center border px-4 py-2">
                                @if($tutor->signature)
                                    <img src="{{ asset('storage/' . $tutor->signature) }}" alt="Firma" class="h-16 mx-auto">
                                @else
                                    Sin firma
                                @endif
                            </td>
                            <td class="text-center border px-6 py-4">
                            @if($tutor->courses->isEmpty())
                                <span class="text-gray-400">Sin cursos asignados</span>
                            @else
                                <ul class="list-disc list-inside text-sm text-gray-700">
                            @foreach($tutor->courses as $course)
                                    <li>{{ $course->title }}</li>
                            @endforeach
                                </ul>
                            @endif
                            </td>
                            <td class="text-center border px-6 py-4">
                                <a href="{{ route('tutors.editCourses', $tutor) }}" class="rounded border border-blue-600 px-2 py-1 text-blue-600 hover:bg-blue-50">Editar cursos</a>
                                <a href="{{ route('admin.tutors.signature.edit', $tutor) }}" class="rounded border border-gray-400 px-2 py-1 text-gray-700 hover:bg-gray-50">Subir/editar firma</a>
                            </td>   
                        </tr>
                    @endforeach
                    @if($tutors->isEmpty())
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-gray-500">
                                No hay tutores cargados aún.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>