<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Crear nuevo curso') }}
        </h2>
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

                {{-- Fechas: inicio y fin (50/50) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-white">Fecha de inicio</label>
                        <input type="date" name="start_date"
                               value="{{ old('start_date') }}"
                               class="mt-1 w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white">Fecha de finalización</label>
                        <input type="date" name="end_date"
                               value="{{ old('end_date') }}"
                               class="mt-1 w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

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