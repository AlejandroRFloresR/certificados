<x-guest-layout>
    <section class="py-10">
        <div class="max-w-4xl mx-auto px-4">
            {{-- Hero / encabezado --}}
            <div class="text-center">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-gray-100">
                    Sistema de Certificados
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-300">
                    Ingresá tu DNI para consultar certificados emitidos a tu nombre.
                </p>
            </div>

            {{-- Formulario de búsqueda por DNI (GET /?dni=...) --}}
            <form method="GET" action="{{ route('home') }}" class="mt-8 max-w-md mx-auto space-y-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">DNI</label>
                <input
                    name="dni"
                    value="{{ old('dni', $dni ?? '') }}"
                    placeholder="Ej: 12345678"
                    class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    required
                    autocomplete="off"
                >
                <div class="flex items-center gap-2">
                    <button class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
                        Buscar
                    </button>
                    @if(($dni ?? '') !== '')
                        <a href="{{ route('home') }}" class="text-sm text-gray-600 dark:text-gray-300 hover:underline">Limpiar</a>
                    @endif
                </div>
            </form>

            {{-- Resultados --}}
            @if(($dni ?? '') !== '')
                <div class="mt-10">
                    @if(!$user)
                        <div class="rounded border border-red-300 bg-red-50 p-4 text-red-700">
                            No se encontró un usuario con el DNI <strong>{{ $dni }}</strong>.
                        </div>
                    @else
                        <div class="mb-3 text-sm text-gray-600 ">
                            Resultados para: <strong>{{ $user->name }}</strong> (DNI {{ $dni }})
                        </div>

                        @if($certs->isEmpty())
                            <div class="rounded border border-yellow-300 bg-yellow-50 p-4 text-yellow-800">
                                No hay certificados emitidos para este usuario.
                            </div>
                        @else
                            <div class="overflow-x-auto rounded border border-gray-200 ">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-100 ">
                                        <tr class="text-left">
                                            <th class="px-4 py-2">Curso</th>
                                            <th class="px-4 py-2">Fecha emisión</th>
                                            <th class="px-4 py-2">Código</th>
                                            <th class="px-4 py-2">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 ">
                                        @foreach($certs as $c)
                                            @php
                                                $code = $c->certificate_code ?? null;
                                                $masked = $code ? (mb_substr($code, 0, 4) . '…' . mb_substr($code, -4)) : '—';
                                                $dateCol = $c->issued_date ?? $c->created_at;
                                                $date = $dateCol ? \Illuminate\Support\Carbon::parse($dateCol)->format('Y-m-d') : '—';
                                            @endphp
                                            <tr>
                                                <td class="px-4 py-2">{{ $c->course?->title ?? '—' }}</td>
                                                <td class="px-4 py-2">{{ $date }}</td>
                                                <td class="px-4 py-2 font-mono">{{ $masked }}</td>
                                                <td class="px-4 py-2">
                                                    @if($code)
                                                        {{-- Usa tu ruta existente de verificación por código --}}
                                                        <a href="{{ route('certificates.verify', ['code' => $code]) }}"
                                                           class="text-blue-600 hover:underline">
                                                            Verificar
                                                        </a>
                                                    @else
                                                        <span class="text-gray-400">Sin código</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif
                </div>
            @endif
        </div>
    </section>
</x-guest-layout>
