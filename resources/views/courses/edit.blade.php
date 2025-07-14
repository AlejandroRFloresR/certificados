<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Editar Curso') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto">
        @if ($errors->any())
            <div class="mb-4 text-red-600 font-medium">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <form action="{{ route('courses.update', $course->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Título del curso
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title', $course->title) }}"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Descripción
                    </label>
                    <textarea name="description" id="description" rows="4"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description', $course->description) }}</textarea>
                </div>

                <div class="mb-4">
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Fecha de inicio
                    </label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $course->start_date) }}"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="mb-4">
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Fecha de finalización
                    </label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $course->end_date) }}"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('courses.index') }}"
                       class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 mr-2">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700">
                        Actualizar Curso
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>