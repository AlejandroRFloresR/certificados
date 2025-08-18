<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Listado de Alumnos en {{ $course->title }}</h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto">
        @if ($users->isEmpty())
            <p>No hay usuarios inscritos en este curso.</p>
        @else
            <table class="w-full table-auto bg-white shadow rounded">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2">Nombre</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Fecha de inscripción</th>
                        <th class="px-4 py-2">Tutores</th>
                        <th class="px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $index => $user)
                        <tr class="border-t text-center ">
                            <td class="px-4 py-2">{{ $index + 1 }}</td>
                            <td class="px-4 py-2">{{ $user->name }}</td>
                            <td class="px-4 py-2">{{ $user->email }}</td>
                            <td class="px-4 py-2">{{ $user->pivot->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">{{ $user->course}}</td>
                            <td>
                                    <a href="{{ route('courses.certificate', ['course' => $course->id, 'user' => $user->id]) }}"   class="inline-block mt-2 px-4 py-2 bg-indigo-600 text-black rounded hover:bg-indigo-700">
                                        Descargar certificado
                                    </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>