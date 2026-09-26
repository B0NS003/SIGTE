<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button class="side-logout" type="submit">Cerrar sesión</button>
</form>
