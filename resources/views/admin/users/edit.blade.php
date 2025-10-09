<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Editar usuario
            </h2>
            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center rounded-md bg-gray-200 px-3 py-2 text-sm font-medium text-gray-900 hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                Volver al listado
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto">

        @if (session('success'))
            <div class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-700">
                <div class="font-semibold mb-1">Revisá estos campos:</div>
                <ul class="list-disc ml-6">
                    @foreach ($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nombre</label>
                    <input name="name" value="{{ old('name', $user->name) }}" required
                           class="mt-1 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="mt-1 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">DNI</label>
                    <input name="dni" value="{{ old('dni', $user->dni) }}" required
                           class="mt-1 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Teléfono</label>
                    <input name="telefono" value="{{ old('telefono', $user->telefono) }}" required
                           class="mt-1 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                </div>
            </div>

            {{-- Rol --}}
            <div class="max-w-sm">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Rol</label>
                <select name="role" id="role"
                        class="mt-1 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    @foreach($roles as $id => $name)
                        <option value="{{ $name }}" @selected(old('role', $currentRole)===$name)>{{ ucfirst($name) }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">admin · tutor · user</p>
            </div>

            {{-- Bloque Tutor (visible si rol=tutor) --}}
            <div id="tutor-fields" class="hidden rounded border border-gray-200 dark:border-gray-700 p-4">
                <div class="font-semibold mb-2 text-gray-800 dark:text-gray-200">Vincular / crear Tutor</div>

                <div class="max-w-sm">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tutor existente</label>
                    <select name="tutor_id"
                            class="mt-1 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">— Ninguno —</option>
                        @foreach($tutors as $t)
                            <option value="{{ $t->id }}"
                                @selected(old('tutor_id', optional($user->tutor)->id) == $t->id)>
                                {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Si elegís uno, se ignoran los campos de “crear nuevo”.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nombre (Tutor)</label>
                        <input name="tutor_name" value="{{ old('tutor_name', optional($user->tutor)->name) }}"
                               class="mt-1 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Firma (ruta/ID)</label>
                        <input name="tutor_signature" value="{{ old('tutor_signature', optional($user->tutor)->signature) }}"
                               class="mt-1 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    </div>
                </div>
            </div>

            {{-- Password opcional --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nueva contraseña (opcional)</label>
                    <input type="password" name="password"
                           class="mt-1 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                           placeholder="Dejar vacío para no cambiar">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation"
                           class="mt-1 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                           placeholder="Repetir nueva contraseña">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center rounded-md border px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-800">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Guardar
                </button>
            </div>
        </form>
    </div>

    <script>
        const roleSel = document.getElementById('role');
        const tutorBlock = document.getElementById('tutor-fields');
        function toggleTutor(){
            tutorBlock.classList.toggle('hidden', roleSel.value !== 'tutor');
        }
        roleSel.addEventListener('change', toggleTutor);
        // estado inicial:
        toggleTutor();
    </script>
</x-app-layout>
