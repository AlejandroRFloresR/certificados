 <x-app-layout>
 <form method="POST" action="{{ route('certificates.issue') }}">
    @csrf

    <select name="user_id" required>
        <option value="">Seleccionar usuario</option>
        @foreach ($users as $user)
            <option value="{{ $user->id }}">{{ $user->name }}</option>
        @endforeach
    </select>

    <select name="course_id" required>
        <option value="">Seleccionar curso</option>
        @foreach ($courses as $course)
            <option value="{{ $course->id }}">{{ $course->title }}</option>
        @endforeach
    </select>

    <button type="submit">Emitir certificado</button>
</form>
</x-app-layout>