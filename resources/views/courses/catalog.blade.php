<x-public-layout>
    @push('scripts')
        @viteReactRefresh
        @vite('resources/js/catalog.jsx')
    @endpush

    <div id="course-catalog" data-props='@json($props)'></div>

    <noscript>
        <p class="px-4 py-12 text-center text-sm text-hospitalgray">
            Necesitás habilitar JavaScript para ver el catálogo de cursos.
        </p>
    </noscript>
</x-public-layout>
