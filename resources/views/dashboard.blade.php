<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-white">
            Mis Cursos
        </h2>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto space-y-8">

        {{-- BLOQUE 1: Cursos donde estoy inscripto como alumno --}}
                <div class="overflow-x-auto bg-white shadow rounded-lg">
                    <table class="min-w-full text-sm">
                        <thead class="bg-blue-100">
                            <tr>
                                <th class="px-4 py-2 text-center text-xs font-medium ">
                                    Curso
                                </th>
                                <!--
                                <th class="px-4 py-2 text-center text-xs font-medium ">
                                    Inicio
                                </th>
                                <th class="px-4 py-2 text-center text-xs font-medium ">
                                    Fin
                                </th>
                                -->
                                <th class="px-4 py-2 text-center text-xs font-medium ">
                                    Certificado
                                </th>
                                <th class="px-4 py-2 text-center text-xs font-medium ">
                                    Acción
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white ">
                            @foreach($studentCourses as $course)
                                @php
                                    $cert = $certsByCourse->get($course->id);
                                    $type = $cert?->type ?? data_get($cert?->snapshot_data, 'type');
                                @endphp
                                <tr>
                                    <td class="text-center border px-6 py-4">
                                        {{ $course->title }}
                                    </td>
                                    <!--
                                    <td class="text-center border px-6 py-4">
                                        @if($course->start_date)
                                            {{ \Carbon\Carbon::parse($course->start_date)->format('d/m/Y') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-center border px-6 py-4">
                                        @if($course->end_date)
                                            {{ \Carbon\Carbon::parse($course->end_date)->format('d/m/Y') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    -->
                                    {{-- Columna Certificado --}}
                                    <td class="text-center border px-6 py-4">
                                        @if($cert)
                                            <div class="flex items-center justify-center gap-2">
                                                <span class="inline-flex items-center rounded-full bg-green-100 text-green-800 px-2 py-0.5 text-xs">
                                                    Emitido
                                                </span>

                                                @if($type)
                                                    <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-800 px-2 py-0.5 text-xs">
                                                        {{ ucfirst($type) }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">Aún no emitido</span>
                                        @endif
                                    </td>

                                    {{-- Columna Acción --}}
                                    <td class="text-center border px-6 py-4">
                                        @if($cert)
                                            <a href="{{ route('certificates.download', $cert->certificate_code) }}"
                                               class="inline-flex items-center rounded bg-blue-600 px-3 py-1 text-white text-xs hover:bg-blue-700">
                                                Descargar
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400">Sin certificado</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                                @if($studentCourses->isEmpty())
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-center text-gray-500">
                                        No estás inscripto en ningún curso.
                                    </td>
                                </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        

        {{-- BLOQUE 2: Cursos donde soy tutor (solo si tengo rol tutor) --}}
        @if(auth()->user()->hasRole('tutor'))
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">
                    Mis cursos como tutor
                </h3>

                @if($tutorCourses->isEmpty())
                    <p class="text-sm text-gray-500">
                        No tenés cursos asignados como tutor por el momento.
                    </p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Curso
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Inicio
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Fin
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                @foreach($tutorCourses as $course)
                                    <tr>
                                        <td class="px-4 py-2">
                                            {{ $course->title }}
                                        </td>
                                        <td class="px-4 py-2">
                                            @if($course->start_date)
                                                {{ \Carbon\Carbon::parse($course->start_date)->format('d/m/Y') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            @if($course->end_date)
                                                {{ \Carbon\Carbon::parse($course->end_date)->format('d/m/Y') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

    </div>
</x-app-layout>
