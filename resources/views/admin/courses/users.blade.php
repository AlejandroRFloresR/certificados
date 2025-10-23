<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
            Listado de Alumnos en {{ $course->title }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto">

        {{-- Flashes globales --}}
        @if (session('success'))
            <div class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-2 text-green-800">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-2 text-red-800">
                {{ session('error') }}
            </div>
        @endif

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
                        @php
                            // Buscar certificado existente para este alumno en este curso
                            $existing = $user->certificates->firstWhere('course_id', $course->id)
                                ?? \App\Models\Certificate::where('user_id', $user->id)
                                    ->where('course_id', $course->id)
                                    ->first();

                            // Si venimos de emitir y el flash trae el code, lo usamos (por si el $existing no estaba eager-loaded)
                            $flashCode = session('cert_code');
                            $code = $existing?->certificate_code ?? $flashCode;
                        @endphp

                        <tr class="border-t text-center">
                            <td class="px-4 py-2">{{ $index + 1 }}</td>
                            <td class="px-4 py-2">{{ $user->name }}</td>
                            <td class="px-4 py-2">{{ $user->email }}</td>
                            <td class="px-4 py-2">{{ $user->pivot->created_at->format('d/m/Y') }}</td>

                            {{-- Tutores del curso (no del usuario) --}}
                            <td class="px-4 py-2 text-left align-top">
                                @if($course->tutors->isEmpty())
                                    <span class="text-gray-500">Sin tutores asignados</span>
                                @else
                                   <ul class="pl-5" style="list-style: disc outside;">
                                        @foreach($course->tutors as $t)
                                            <li style="line-height:1.25rem">{{ $t->name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>

                            <td class="px-4 py-2">
                                @php
                                    $existing = $user->certificates->first(); // ya viene filtrado por course_id

                                    // 1) tomar de la columna 'type'
                                    $type = $existing?->type;

                                    // 2) fallback: si viene null, buscar en snapshot_data
                                    if (!$type) {
                                        $type = data_get($existing?->snapshot_data, 'type');
                                    }
                                    if (is_array($type)) { $type = reset($type) ?: null; } // por si viniera array

                                    $badgeText = $type ? ucfirst($type) : '—';

                                    $badges = [
                                        'asistio'  => ['bg' => 'bg-blue-100',    'text' => 'text-blue-800',   'border' => 'border-blue-200'],
                                        'dicto'    => ['bg' => 'bg-purple-100',  'text' => 'text-purple-800', 'border' => 'border-purple-200'],
                                        'aprobado' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-800','border' => 'border-emerald-200'],
                                    ];
                                    $style = $type && isset($badges[$type])
                                        ? $badges[$type]
                                        : ['bg'=>'bg-gray-100','text'=>'text-gray-800','border'=>'border-gray-200'];
                                @endphp
                                @if($existing)
                                    <div class="flex items-center gap-2 flex-nowrap">
                                        <a href="{{ route('certificates.download', $existing->certificate_code) }}"
                                        class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1 text-white hover:bg-blue-700">
                                            Descargar
                                        </a>

                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded border {{ $style['bg'].' '.$style['text'].' '.$style['border'] }}">
                                            {{ $badgeText }}
                                        </span>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('certificates.emit') }}"
                                        class="inline-flex items-center gap-2 flex-wrap">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                        <input type="hidden" name="course_id" value="{{ $course->id }}">

                                        <select name="type" required
                                                class="border rounded px-2 py-1">
                                            <option value="asistio">Asistió</option>
                                            <option value="dicto">Dictó</option>
                                            <option value="aprobado">Aprobado</option>
                                        </select>

                                        <button type="submit"
                                                class="px-3 py-1 rounded bg-emerald-600 text-white hover:bg-emerald-700">
                                            Emitir
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>
