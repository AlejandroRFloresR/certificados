<x-app-layout>
<div class="max-w-7x1 mx-auto p-6 bg-green min-h-screen">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-white">
            {{ __('Listado de Tutores') }}
        </h2>
    </x-slot>
    <div class="flex justify-end mb-4">
        <a href="{{ route('tutors.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-black font-bold py-2 px-4 rounded">
            Nuevo
        </a>
    </div>

    <table class="table-auto w-full border">
        <thead>
            <tr class="bg-blue-100">
                <th class="border px-4 py-2 ">Nombre</th>
                <th class="border px-4 py-2">Firma</th>
                <th class="border px-4 py-2 ">Cursos</th>
                <th class="border px-4 py-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tutors as $tutor)
                <tr>
                    <td class="border px-4 py-2">{{ $tutor->name }}</td>
                    <td class="border px-4 py-2">
                        @if($tutor->signature)
                            <img src="{{ asset('storage/' . $tutor->signature) }}" alt="Firma" class="h-16">
                        @else
                            Sin firma
                        @endif
                    </td>
                    <td class="px-6 py-4">
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
                    <td class="px-6 py-4">
                        <a href="{{ route('tutors.editCourses', $tutor) }}" class="bg-green-500 text-black px-3 py-1 rounded hover:bg-green-600 text-sm">
                            Editar cursos
                        </a>
                    </td>   
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center py-4">No hay tutores cargados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
</x-app-layout>