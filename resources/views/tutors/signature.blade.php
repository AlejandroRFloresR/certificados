<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
        {{ $title ?? 'Firma del tutor' }}
      </h2>
      <a href="{{ route('tutors.index') }}"
         class="inline-flex items-center rounded-md border px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
         Volver
      </a>
    </div>
  </x-slot>

  <div class="max-w-xl mx-auto py-6 space-y-4">
      @if (session('success'))
        <div class="rounded border border-green-300 bg-green-50 px-4 py-3 text-green-800">
          {{ session('success') }}
        </div>
      @endif

      @if ($errors->any())
        <div class="rounded border border-red-300 bg-red-50 px-4 py-3 text-red-700">
          <div class="font-semibold mb-1">Revisá:</div>
          <ul class="list-disc ml-6">
            @foreach ($errors->all() as $e)
              <li class="text-sm">{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Firma (imagen)</label>
          <input type="file" name="signature" accept="image/*" required
                 class="block w-full rounded border-gray-300">
          <p class="text-xs text-gray-500 mt-1">Formatos: PNG. Máx. 4 MB.</p>
          @error('signature') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        @if($tutor->signature)
          <div>
            <p class="text-sm text-gray-700 mb-1">Actual:</p>
            <img src="{{ asset('storage/'.$tutor->signature) }}" alt="Firma actual" class="h-20 bg-white p-2 rounded border">
          </div>
        @endif

        <div class="flex items-center gap-2">
          <button class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            Guardar
          </button>
        </div>
      </form>
  </div>
</x-app-layout>
