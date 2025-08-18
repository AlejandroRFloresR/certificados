
<x-app-layout>
<form method="POST" action="{{ route('tutors.store') }}" enctype="multipart/form-data">
    @csrf

    <label>Nombre del Tutor:</label>
    <input type="text" name="name" required>

    <label>Firma (imagen):</label>
    <input type="file" name="signature" accept="image/*" required>
    <div id="courses-container">
        <label>Cursos:</label>
        <div class="Course-select">
            <select name="courses[]" required>
                <option value="">Seleccionar un curso</option>
            
                @foreach($courses as $course)
                <option value="{{ $course->id }}">{{ $course->title }}</option>
                @endforeach
        </select>
        </div>
    </div>
    <button type="button" onclick="addCourseSelect"()>Agregar otro curso</button>
    <br><br>

        <button type="submit">Guardar</button>
        
</form>
<script>
    function addCourseSelect(){
        const container= document.getElementById('courses-container');
        const selectHTML= `
            <div class="course-select">
                <select name="courses[]" required>
                        <option value="">-- Selecciona un curso --</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="this.parentElement.remove()">Eliminar</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', selectHTML);
        }
    </script>
    
</x-app-layout>