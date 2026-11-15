@once
    @push('scripts')
        <script src="{{ asset('js/modulos/flujo/proceso-reloj.js') }}?v={{ @filemtime(public_path('js/modulos/flujo/proceso-reloj.js')) }}"></script>
    @endpush
@endonce