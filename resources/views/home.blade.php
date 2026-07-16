<x-public-layout>

    <section class="bg-gradient-to-br from-hospitalblue to-hospitalblue-dark px-4 py-12 sm:px-8 sm:py-16">
        <div class="mx-auto max-w-xl text-center">
            <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-white/70">
                <span class="h-1.5 w-1.5 rounded-full bg-hospitalbrown"></span>
                Verificación pública
            </span>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                Verificá la validez de un certificado
            </h1>
            <p class="mx-auto mt-2 max-w-md text-sm text-white/80">
                Ingresá el DNI del titular o el código del certificado para confirmar que fue emitido por el sistema.
            </p>

            <form method="GET" class="mt-6 flex gap-2 rounded-xl bg-white p-1.5 shadow-lg">
                <label class="flex flex-1 items-center gap-2 px-3">
                    <svg class="h-[17px] w-[17px] shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                        <path d="M21 21l-4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <input type="text" name="q" value="{{ $q ?? '' }}"
                           placeholder="DNI o código de certificado"
                           class="w-full border-0 p-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-0" />
                </label>
                <button class="shrink-0 rounded-lg bg-hospitalblue px-5 py-2.5 text-sm font-semibold text-white hover:bg-hospitalblue-dark">
                    Buscar
                </button>
            </form>
            <p class="mt-3 text-xs text-white/60">
                Ej: 30123456 (DNI) — o el código que figura en tu PDF
            </p>
        </div>
    </section>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-8">

        @if(!empty($q))
            @if($certByCode)
                <div class="mb-5 flex items-start gap-2 rounded-lg border border-green-200 bg-green-50 p-3.5 text-sm text-green-800">
                    <svg class="mt-0.5 h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.6"/>
                        <path d="M8.5 12.3l2.3 2.3 4.7-5.1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span><strong>Certificado válido.</strong> Este certificado fue emitido por el sistema.</span>
                </div>
            @elseif($user && $certs->count())
                <div class="mb-5 flex items-start gap-2 rounded-lg border border-green-200 bg-green-50 p-3.5 text-sm text-green-800">
                    <svg class="mt-0.5 h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.6"/>
                        <path d="M8.5 12.3l2.3 2.3 4.7-5.1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>
                        <strong>{{ $certs->count() }} {{ $certs->count() > 1 ? 'certificados encontrados' : 'certificado encontrado' }}.</strong>
                    </span>
                </div>
            @else
                <div class="mb-5 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 p-3.5 text-sm text-red-800">
                    <svg class="mt-0.5 h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.6"/>
                        <path d="M12 8v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <circle cx="12" cy="16" r="0.9" fill="currentColor"/>
                    </svg>
                    <span><strong>No se encontraron resultados</strong> para “{{ $q }}”. Verificá que el DNI o el código estén escritos correctamente.</span>
                </div>
            @endif
        @endif

        @if($user && $certs->count())
            <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gray-50 px-4 py-3 sm:px-5">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                        @if($user->dni)
                            <p class="text-xs tabular-nums text-hospitalgray">DNI {{ $user->dni }}</p>
                        @endif
                    </div>
                    <span class="text-xs text-hospitalgray">
                        {{ $certs->count() }} {{ $certs->count() > 1 ? 'certificados' : 'certificado' }}
                    </span>
                </div>

                {{-- Desktop: tabla --}}
                <div class="hidden overflow-x-auto sm:block">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-hospitalgray">
                                <th class="px-5 py-2.5">Curso</th>
                                <th class="px-5 py-2.5">Tipo</th>
                                <th class="px-5 py-2.5">Emitido</th>
                                <th class="px-5 py-2.5">Código</th>
                                <th class="px-5 py-2.5 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($certs as $c)
                                @php $type = $c->type ?? data_get($c->snapshot_data,'type'); @endphp
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-900">{{ $c->course->title ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        @if($type)
                                            <span class="inline-flex rounded-full bg-hospitalblue/10 px-2 py-0.5 text-xs font-semibold text-hospitalblue">
                                                {{ ucfirst($type) }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 tabular-nums text-gray-600">
                                        {{ $c->issued_date ? \Carbon\Carbon::parse($c->issued_date)->format('d/m/Y') : '—' }}
                                    </td>
                                    <td class="px-5 py-3 font-mono text-xs tabular-nums text-hospitalgray">
                                        {{ $c->certificate_code }}
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="{{ route('certificates.download', $c->certificate_code) }}"
                                           class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-sm font-semibold text-hospitalblue hover:bg-hospitalblue/10">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M12 4v11m0 0l-4-4m4 4l4-4M5 19h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Descargar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile: tarjetas apiladas --}}
                <div class="flex flex-col divide-y divide-gray-100 sm:hidden">
                    @foreach($certs as $c)
                        @php $type = $c->type ?? data_get($c->snapshot_data,'type'); @endphp
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-medium text-gray-900">{{ $c->course->title ?? '—' }}</p>
                                @if($type)
                                    <span class="shrink-0 rounded-full bg-hospitalblue/10 px-2 py-0.5 text-xs font-semibold text-hospitalblue">
                                        {{ ucfirst($type) }}
                                    </span>
                                @endif
                            </div>
                            <div class="mt-1.5 flex items-center justify-between text-xs text-hospitalgray">
                                <span>Emitido</span>
                                <span class="tabular-nums">
                                    {{ $c->issued_date ? \Carbon\Carbon::parse($c->issued_date)->format('d/m/Y') : '—' }}
                                </span>
                            </div>
                            <p class="mt-1 break-all font-mono text-xs text-hospitalgray">{{ $c->certificate_code }}</p>
                            <a href="{{ route('certificates.download', $c->certificate_code) }}"
                               class="mt-2 inline-flex items-center gap-1.5 text-sm font-semibold text-hospitalblue">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 4v11m0 0l-4-4m4 4l4-4M5 19h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Descargar
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

</x-public-layout>
