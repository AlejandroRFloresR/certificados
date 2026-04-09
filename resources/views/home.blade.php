<x-guest-layout>
  <div class="max-w-3xl mx-auto py-3">
      <h2 class="text-xl font-semibold text-white text-center">
        Verificación de certificados
      </h2>
      <div class="rounded p-4">
        <form method="GET" class="flex gap-2 items-center">
          <input type="text" name="q" value="{{ $q ?? '' }}"
                placeholder="Ingresá DNI o Código de certificado"
                class="w-full rounded border-gray-300" />
          <button class="rounded bg-blue-600 px-3 py-2 text-white">Buscar</button>
        </form>

        @if(!empty($q))
          @if($certByCode)
            <div class="mt-3 rounded border border-green-300 bg-green-50 p-3 text-green-800">
              Certificado encontrado y válido.
            </div>
          @elseif($user && $certs->count())
            <div class="mt-3 rounded p-3 text-white">
              Certificados encontrados.
            </div>
          @else
            <div class="mt-3 rounded border border-red-300 bg-red-50 p-3 text-red-800">
              No se encontraron resultados para “{{ $q }}”.
            </div>
          @endif
        @endif
      </div>

      @if($user && $certs->count())
        <div class="rounded  p-2">
            <div class="font-semibold text-white text-center mb-1">
              {{ $user->name }} @if($user->dni) — DNI: {{ $user->dni }} @endif
            </div>

          <div class="overflow-x-auto rounded ">
            <table class="w-full table-fixed text-sm">
              {{-- colgroup: ajustá anchos a gusto --}}
              <colgroup>
                <col class="w-[35%]">   {{-- Curso --}}
                <col class="w-[14%]">   {{-- Tipo --}}
                <col class="w-[20%]">   {{-- Emitido --}}
                <col class="w-[12%]">   {{-- Acción --}}
              </colgroup>

              <thead class="bg-blue-100">
                <tr>
                  <th class="px-3 py-2 text-center">Curso</th>
                  <th class="px-3 py-2 text-center">Tipo</th>
                  <th class="px-3 py-2 text-center">Emitido</th>
                  <th colspan="2" class="px-3 py-2 text-center">Acción</th>
                </tr>
              </thead>

              <tbody class="bg-white">
                @foreach($certs as $c)
                  @php $type = $c->type ?? data_get($c->snapshot_data,'type'); @endphp
                  <tr class="border-t">
                    <td class="px-3 py-2 text-center">{{ $c->course->title ?? '—' }}</td>
                    <td class="px-3 py-2 text-center">{{ $type ? ucfirst($type) : '—' }}</td>
                    <td class="px-3 py-2 text-center">
                      {{ $c->issued_date ? \Carbon\Carbon::parse($c->issued_date)->format('d/m/Y') : '—' }}
                    </td>

                    <td colspan="2" class="px-3 py-2 text-center">
                      <a href="{{ route('certificates.download', $c->certificate_code) }}"
                        class="inline-flex items-center rounded bg-blue-600 px-3 py-1 text-white hover:bg-blue-700">
                        Descargar
                      </a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endif
  </div>
      <div class="flex gap-2">
        @auth
          <a href="{{ route('dashboard') }}" class="rounded bg-emerald-600 px-3 py-2 text-white">Ir al panel</a>
        @else
          <a href="{{ route('login') }}" class="rounded bg-blue-600 px-3 py-2 text-white">Iniciar sesión</a>
          @if (Route::has('register'))
            <a href="{{ route('register') }}" class="rounded border px-3 py-2 text-white">Registrarme</a>
          @endif
        @endauth
      </div>
</div>
</x-guest-layout>
