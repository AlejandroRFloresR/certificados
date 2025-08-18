<x-app-layout>
    <h2 class="text-xl font-bold mb-4">Cambiar contraseña para {{ $user->name }}</h2>

    <form method="POST" action="{{ route('admin.users.updatePassword', $user->id) }}">
        @csrf
        @method('PUT')

        <label>Nueva contraseña:</label>
        <input type="password" name="password" class="block w-full mb-2" required>

        <label>Confirmar contraseña:</label>
        <input type="password" name="password_confirmation" class="block w-full mb-4" required>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Guardar</button>
    </form>
</x-app-layout>