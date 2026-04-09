<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">Importar usuarios</h2>
            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center rounded-md border border-white bg-white px-3 py-1 text-hospitalblue font-medium hover:bg-hospitalblue hover:text-white">
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-2xl mx-auto">
        @if(session('success'))
            <div class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-700">
                <ul class="list-disc ml-6">
                    @foreach ($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('admin.users.import.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium">Archivo (.xlsx/.xls/.csv)</label>
                    <input type="file" name="file" required class="mt-1 block w-full border rounded">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium">Rol por defecto</label>
                        <select name="default_role" class="mt-1 w-full rounded border">
                            <option value="">— ninguno —</option>
                            <option value="user">user</option>
                            <option value="tutor">tutor</option>
                            <option value="admin">admin</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Si el archivo no trae columna <code>role</code>, se usará este valor.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Inscribir a este curso (opcional)</label>
                        <select name="enroll_course_id" class="mt-1 w-full rounded border">
                            <option value="">— ninguno —</option>
                            @foreach(\App\Models\Course::orderBy('title')->get() as $c)
                                <option value="{{ $c->id }}">{{ $c->title }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Además, podés pasar múltiples cursos por fila usando <code>course_ids</code> en el archivo.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center rounded-md border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        Importar
                    </button>
                </div>
            </form>
        </div>

        {{-- Detalle de fallas (si hubo) --}}
        @if(session('failures') && session('failures')->count())
            <div class="mt-6 bg-white shadow rounded-lg p-4">
                <h3 class="font-semibold mb-2">Filas con errores</h3>
                <ul class="list-disc ml-6 text-sm">
                    @foreach(session('failures') as $failure)
                        <li>
                            Fila {{ $failure->row() }}:
                            @foreach($failure->errors() as $err)
                                <span class="text-red-600">{{ $err }}</span>
                            @endforeach
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-app-layout>
