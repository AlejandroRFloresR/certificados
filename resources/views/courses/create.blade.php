<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ __('Crear nuevo curso') }}
            </h2>
            <a href="{{ route('courses.index') }}"
                class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-medium text-hospitalblue hover:bg-gray-300">
                    Volver al listado
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-xl mx-auto">
        <div class="bg-hospitalblue shadow rounded-lg p-6">
            <form method="POST" action="{{ route('admin.courses.store') }}" class="space-y-6">
                @csrf

                {{-- Título --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-white">Título del Curso</label>
                    <input type="text" name="title"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           value="{{ old('title') }}" required>
                </div>

                {{-- Descripción --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-white">Descripción</label>
                    <textarea name="description"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              rows="4">{{ old('description') }}</textarea>
                </div>

                @include('courses.partials.catalog-fields', ['course' => $course ?? null])

                {{-- Botones --}}
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('courses.index') }}"
                       class="inline-flex items-center rounded-md bg-white border px-4 py-2 text-sm text-hospitalblue font-medium hover:bg-gray-300">
                        Cancelar
                     </a>
                    <button type="submit"
                            class="inline-flex items-center rounded-md border border-white bg-hospitalblue px-4 py-2 text-sm font-medium text-white hover:bg-hospitalblue-dark transition">
                        Guardar curso
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>