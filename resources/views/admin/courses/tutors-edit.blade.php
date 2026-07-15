<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
        Asignar tutores — {{ $course->title }}
      </h2>
      <a href="{{ route('admin.courses.users', $course) }}"
         class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-medium text-hospitalblue hover:bg-gray-300">
         Volver al listado
      </a>
    </div>
  </x-slot>

  <div class="max-w-4xl mx-auto py-6 space-y-4">
    @if ($errors->any())
      <div class="rounded border border-red-300 bg-red-50 px-4 py-3 text-red-700">
        <ul class="list-disc ml-6">
          @foreach ($errors->all() as $e) <li class="text-sm">{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form method="GET" class="flex items-center gap-2">
      <input type="text" name="q" value="{{ $q }}" placeholder="Buscar tutor por nombre o email"
             class="w-full md:max-w-md rounded border-gray-300">
      <button class="rounded bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">Buscar</button>
      @if($q !== '')
        <a href="{{ route('admin.courses.tutors.edit', $course) }}"
           class="rounded px-3 py-2 border text-gray-700 hover:bg-gray-50">Limpiar</a>
      @endif
    </form>

    <form method="POST" action="{{ route('admin.courses.tutors.update', $course) }}">
      @csrf @method('PUT')

      <p class="text-sm text-gray-600 mb-2">Seleccioná hasta <strong>3</strong> tutores.</p>

      <div class="rounded border overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-3 py-2 w-12 text-center">Sel.</th>
              <th class="px-3 py-2 text-left">Nombre</th>
              <th class="px-3 py-2 text-left">Email</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tutors as $t)
              <tr class="border-t">
                <td class="px-3 py-2 text-center">
                  <input type="checkbox" class="chk-tutor"
                         name="tutors[]" value="{{ $t->id }}"
                         @checked(in_array($t->id, $selected, true))>
                </td>
                <td class="px-3 py-2">{{ $t->user->name ?? $t->name }}</td>
                <td class="px-3 py-2">{{ $t->user->email ?? '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="px-3 py-6 text-center text-gray-500">No hay tutores.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex items-center gap-2">
        <a href="{{ route('admin.courses.users', $course) }}"
           class="inline-flex items-center rounded-md border px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
           Cancelar
        </a>
        <button class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 bg-blue-600">
          Guardar
        </button>
      </div>
    </form>
  </div>

  {{-- Limitar a 3 seleccionados en el front (ayuda UX) --}}
  <script>
    const maxTutors = 3;
    const boxes = document.querySelectorAll('.chk-tutor');
    function enforceLimit() {
      const checked = Array.from(boxes).filter(b => b.checked);
      if (checked.length > maxTutors) {
        // desmarca el último que superó el límite
        this.checked = false;
        alert(`Máximo ${maxTutors} tutores por curso.`);
      }
    }
    boxes.forEach(b => b.addEventListener('change', enforceLimit));
  </script>
</x-app-layout>
