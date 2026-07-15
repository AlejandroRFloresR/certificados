<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">
                Cambiar contraseña
            </h2>
            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center rounded-md bg-gray-200 px-3 py-2 text-sm font-medium text-gray-900 hover:bg-gray-300">
                Volver al listado
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-md mx-auto">
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

        <div class="rounded border border-gray-200 bg-hospitalblue p-5">
            <div class="mb-4">
                <div class="text-sm text-white">
                    Usuario: <span class="font-semibold">{{ $user->name }}</span>
                    <span class="text-white">({{ $user->email }})</span>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.users.update-password', $user) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-white">Nueva contraseña</label>
                    <input type="password" name="password" required
                           class="mt-1 w-full rounded border-gray-300">
                    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-white">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" required
                           class="mt-1 w-full rounded border-gray-300 ">
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.users.index') }}"
                       class="inline-flex items-center rounded-md border px-3 py-2 text-sm  bg-white text-hospitalblue hover:bg-gray-300">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="inline-flex items-center border border-white rounded-md bg-hospitalblue px-4 py-2 text-sm font-medium text-white hover:bg-hospitalblue-dark">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
