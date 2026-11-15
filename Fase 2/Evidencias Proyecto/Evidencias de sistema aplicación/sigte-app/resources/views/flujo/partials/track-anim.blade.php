@once
    @push('scripts')
        <script src="{{ asset('js/anime.min.js') }}"></script>
        <script src="{{ asset('js/modulos/flujo/track-anim.js') }}?v={{ @filemtime(public_path('js/modulos/flujo/track-anim.js')) }}"></script>
    @endpush
@endonce
