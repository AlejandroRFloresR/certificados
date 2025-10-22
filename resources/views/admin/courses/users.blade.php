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
                            <td class="px-4 py-2">
                                @if($course->tutors->isEmpty())
                                    <span class="text-gray-500">—</span>
                                @else
                                    <ul class="text-left list-disc list-inside">
                                        @foreach($course->tutors as $t)
                                            <li>{{ $t->name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>

                            <td class="px-4 py-2">
                                @php
                                    // ¿ya existe certificado para este alumno en ESTE curso?
                                    // Ideal: traer certificates eager-loaded en el controlador para evitar N+1
                                    $existing = $user->certificates->firstWhere('course_id', $course->id)
                                        ?? \App\Models\Certificate::where('user_id', $user->id)
                                            ->where('course_id', $course->id)
                                            ->first();
                                @endphp

                                @if($existing)
                                    {{-- Ya emitido → mostrar un solo botón "Descargar" (y opcional "Verificar") --}}
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('certificates.download', $existing->certificate_code) }}"
                                        class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1 text-white hover:bg-blue-700">
                                            Descargar
                                        </a>
                                    </div>
                                @else
                                    {{-- Aún no emitido → mostrar selector de tipo + botón "Emitir" (POST -> certificates.emit) --}}
                                    <form method="POST" action="{{ route('certificates.emit') }}" class="flex items-center justify-center gap-2">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                        <input type="hidden" name="course_id" value="{{ $course->id }}">

                                        <select name="type" class="border rounded px-2 py-1" required>
                                            <option value="asistio">Asistió</option>
                                            <option value="dicto">Dictó</option>
                                            <option value="aprobado">Aprobado</option>
                                        </select>

                                        <button class="px-3 py-1 rounded bg-emerald-600 text-white hover:bg-emerald-700">
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
