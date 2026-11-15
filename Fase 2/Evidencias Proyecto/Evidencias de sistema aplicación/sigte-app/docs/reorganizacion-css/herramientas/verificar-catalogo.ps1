param([string]$Etiqueta = 'cat-despues')
$t = Join-Path $env:TEMP 'sigte-ref'
$paginas = [IO.File]::ReadAllText((Join-Path $t 'paginas-catalogo.txt'))
docker exec sigte-app php artisan view:clear | Out-Null
docker exec sigte-app php /tmp/render.php despues - 'catalogo,catalogo-admin,ficha'
& (Join-Path $t 'medir.ps1') -Dir despues -Etiqueta $Etiqueta -Paginas $paginas
node (Join-Path $t 'comparar.js') cat-antes $Etiqueta
