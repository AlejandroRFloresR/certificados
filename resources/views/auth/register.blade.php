<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nombre')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>
        <!-- DNI -->
        <div class="mt-4">
            <x-input-label for="dni" :value="__('Documento')"/>
            <x-text-input id="dni" class="block mt-1 w-full" type="text" name="dni" value="{{ old('dni') }}" required/>
        </div>
        <!-- Teléfono -->
        <div class="mt-4">
            <x-input-label for="telefono" :value="__('Telefono')"/>
            <x-text-input id="telefono" class="block mt-1 w-full" type="text" name="telefono" value="{{ old('telefono') }}"/>
        </div>
        <div class="flex items-center justify-end mt-4">
            <a class="ml-4 underline font-semibold text-black hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-white" href="{{ route('login') }}">
                {{ __('Tengo Cuenta. Ingresar') }}
            </a>
            
        </div>


            <x-primary-button class="ms-4">
                {{ __('Registrarse') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
