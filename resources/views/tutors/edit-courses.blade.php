<x-app-layout>
    <h2 class="text-xl font-bold mb-4">Editar cursos para {{ $tutor->name }}</h2>

    <form method="POST" action="{{ route('tutors.updateCourses', $tutor) }}">
        @csrf
        @method('PUT')
            @foreach($courses as $course)
                <label class="flex items-center space-x-2">
                    <input type="checkbox"
                           name="courses[]"
                           value="{{ $course->id }}"
                           {{ $tutor->courses->contains($course->id) ? 'checked' : '' }}>
                    <span>{{ $course->title }}</span>
                </label>
            @endforeach
        </select>

        <button type="submit" class="mt-4 bg-blue-500 text-black px-4 py-2 rounded hover:bg-blue-600">
            Guardar cambios
        </button>
    </form>
</x-app-layout>