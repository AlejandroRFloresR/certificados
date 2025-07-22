<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Inscritos en: {{ $course->title }}</h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto">
        @if ($users->isEmpty())
            <p>No hay usuarios inscritos en este curso.</p>
        @else
            <table class="w-full table-auto bg-white shadow rounded">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="p-2">#</th>
                        <th class="p-2">Nombre</th>
                        <th class="p-2">Email</th>
                        <th class="p-2">Fecha de inscripción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $index => $user)
                        <tr class="border-t text-left">
                            <td class="p-2">{{ $index + 1 }}</td>
                            <td class="p-2">{{ $user->name }}</td>
                            <td class="p-2">{{ $user->email }}</td>
                            <td class="p-2">{{ $user->pivot->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>