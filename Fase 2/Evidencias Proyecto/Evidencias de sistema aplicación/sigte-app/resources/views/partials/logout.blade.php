<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button class="side-logout" type="submit" onclick="try{sessionStorage.removeItem('sigte-carga')}catch(e){}">
        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M10 5.5H7.2A1.7 1.7 0 0 0 5.5 7.2v9.6A1.7 1.7 0 0 0 7.2 18.5H10" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
            <path d="M10.5 12H19" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
            <path d="M15.8 8.5 19.5 12l-3.7 3.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Cerrar sesión
    </button>
</form>
