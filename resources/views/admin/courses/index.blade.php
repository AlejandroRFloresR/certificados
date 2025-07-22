<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Cursos disponibles</h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto">
        @foreach ($courses as $course)
            <div class="bg-white shadow p-4 mb-4 rounded">
                <h3 class="text-lg font-bold">{{ $course->title }}</h3>
                <p class="text-sm text-gray-600">Inscritos: {{ $course->users_count }}</p>

                <a href="{{ route('admin.course.users', $course->id) }}"
                   class="mt-2 inline-block px-4 py-2 bg-indigo-600 text-black rounded hover:bg-indigo-700">
                    Ver inscritos
                </a>
            </div>
        @endforeach
    </div>
   
</x-app-layout>