<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Crear usuario
            </h2>
            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center rounded-md bg-gray-200 px-3 py-2 text-sm font-medium text-gray-900 hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                Volver al listado
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto">

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

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input name="name" value="{{ old('name') }}" required
                           class="mt-1 w-full rounded border-gray-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="mt-1 w-full rounded border-gray-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">DNI</label>
                    <input name="dni" value="{{ old('dni') }}" required
                           class="mt-1 w-full rounded border-gray-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input name="telefono" value="{{ old('telefono') }}" required
                           class="mt-1 w-full rounded border-gray-300">
                </div>
            </div>

            {{-- Rol --}}
            <div class="max-w-sm">
                <label class="block text-sm font-medium text-gray-700">Rol</label>
                <select name="role" id="role" class="mt-1 w-full rounded border-gray-300">
                    @foreach($roles as $role)
                        {{-- si en tu controlador enviaste $roles = Role::all(['name']); --}}
                        <option value="{{ $role->name }}" @selected(old('role')===$role->name)>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach

                    {{-- Si en cambio pasás un arreglo plano: ['admin','tutor','user'], usá:
                    @foreach($roles as $name)
                        <option value="{{ $name }}" @selected(old('role')===$name)>{{ ucfirst($name) }}</option>
                    @endforeach
                    --}}
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    Admin · Tutor · User
                </p>
                <p class="mt-1 text-xs text-emerald-700">
                    Si elegís <strong>tutor</strong>, al guardar se creará el perfil de Tutor y subira a la lista de tutores automaticamente.
                </p>
            </div>

            {{-- Password --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Contraseña</label>
                    <input type="password" name="password" required
                           class="mt-1 w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" required
                           class="mt-1 w-full rounded border-gray-300">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center rounded-md border px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Crear
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
