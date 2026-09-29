{{-- Campos del catálogo público. Espera $course (puede ser null en el alta) --}}
@php $course = $course ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-white">Fecha de inicio</label>
        <input type="date" name="start_date"
               value="{{ old('start_date', $course?->start_date ? \Carbon\Carbon::parse($course->start_date)->format('Y-m-d') : '') }}"
               class="mt-1 w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-white">Fecha de finalización</label>
        <input type="date" name="end_date"
               value="{{ old('end_date', $course?->end_date ? \Carbon\Carbon::parse($course->end_date)->format('Y-m-d') : '') }}"
               class="mt-1 w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-white">Carga horaria (horas)</label>
        <input type="number" name="hours" min="1" max="2000"
               value="{{ old('hours', $course?->hours) }}"
               class="mt-1 w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-white">Categoría</label>
        <input type="text" name="category" maxlength="100" list="course-categories"
               placeholder="Ej: Enfermería, Emergencias…"
               value="{{ old('category', $course?->category) }}"
               class="mt-1 w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
        <datalist id="course-categories">
            @foreach(\App\Models\Course::whereNotNull('category')->distinct()->orderBy('category')->pluck('category') as $cat)
                <option value="{{ $cat }}">
            @endforeach
        </datalist>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-white">Modalidad</label>
        <select name="modality"
                class="mt-1 w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
            <option value="">—</option>
            @foreach(\App\Models\Course::MODALITIES as $value => $label)
                <option value="{{ $value }}" @selected(old('modality', $course?->modality) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-white">Lugar / plataforma</label>
        <input type="text" name="location" maxlength="255"
               placeholder="Ej: Aula magna, Zoom…"
               value="{{ old('location', $course?->location) }}"
               class="mt-1 w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
    </div>
</div>

<label class="flex items-start gap-3 rounded-md bg-white/10 p-3">
    <input type="hidden" name="is_public" value="0">
    <input type="checkbox" name="is_public" value="1"
           @checked(old('is_public', $course?->is_public))
           class="mt-0.5 rounded border-gray-300 text-hospitalblue focus:ring-blue-500">
    <span class="text-sm text-white">
        <span class="font-medium">Publicar en el catálogo de cursos</span>
        <span class="block text-white/70">Si está marcado, el curso se muestra en la página pública /cursos.</span>
    </span>
</label>
