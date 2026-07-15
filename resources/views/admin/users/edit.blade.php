<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Editar usuario
            </h2>
            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center rounded-md border border-white bg-white px-3 py-1 text-hospitalblue font-medium hover:bg-hospitalblue hover:text-white">
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
                    <label class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input name="name" value="{{ old('name', $user->name) }}" required
                           class="mt-1 w-full rounded border-gray-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="mt-1 w-full rounded border-gray-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">DNI</label>
                    <input name="dni" value="{{ old('dni', $user->dni) }}" required
                           class="mt-1 w-full rounded border-gray-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input name="telefono" value="{{ old('telefono', $user->telefono) }}" required
                           class="mt-1 w-full rounded border-gray-300">
                </div>
            </div>

            {{-- Rol --}}
                <div class="max-w-sm">
                    <label class="block text-sm font-medium text-gray-700">Rol</label>

                    @if ($user->id === auth()->id())
                        @php $currentRole = $user->getRoleNames()->first(); @endphp
                        <input type="text" value="{{ $currentRole ?? 'sin rol' }}"
                            class="mt-1 w-full rounded border-gray-300" readonly>
                        <p class="mt-1 text-xs text-gray-500">No podés cambiar tu propio rol.</p>
                    @else
                        <select name="role" id="role" class="mt-1 w-full rounded border-gray-300">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" @selected($user->hasRole($role->name))>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Opciones: admin · tutor · user</p>
                    @endif
                </div>

                {{-- Password opcional --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nueva contraseña (opcional)</label>
                    <input type="password" name="password"
                           class="mt-1 w-full rounded border-gray-300"
                           placeholder="Dejar vacío para no cambiar">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation"
                           class="mt-1 w-full rounded border-gray-300"
                           placeholder="Repetir nueva contraseña">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center rounded-md border px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
