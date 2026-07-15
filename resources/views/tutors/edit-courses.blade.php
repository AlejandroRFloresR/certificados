<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-white">Editar cursos para {{ $tutor->name }}</h2>
    </x-slot>

    @if ($errors->any())
        <div class="mt-3 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-700">
            <ul class="list-disc ml-6">
                @foreach ($errors->all() as $e) <li class="text-sm">{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tutors.updateCourses', $tutor) }}" class="mt-4 space-y-2">
        @csrf
        @method('PUT')

        @foreach($courses as $course)
            @php
                $assigned    = $tutor->courses->contains($course->id);
                $isFull      = ($course->tutors_count >= 3);
                $disablePick = $isFull && !$assigned; // lleno y no pertenece → no permitir
            @endphp

            <label class="flex items-center gap-2 p-2 rounded border @if($disablePick) opacity-60 @endif">
                <input type="checkbox"
                       name="courses[]"
                       value="{{ $course->id }}"
                       {{ $assigned ? 'checked' : '' }}
                       {{ $disablePick ? 'disabled' : '' }}>
                <span class="flex-1">
                    {{ $course->title }}
                    <span class="text-xs text-gray-500">({{ $course->tutors_count }}/3 tutores)</span>
                </span>

                @if($disablePick)
                    <span class="text-xs text-rose-600">Cupo completo</span>
                @endif
            </label>
        @endforeach

        <button type="submit"
                class="mt-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            Guardar cambios
        </button>
    </form>
</x-app-layout>