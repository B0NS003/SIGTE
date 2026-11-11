param([string]$Dir = 'antes', [string]$Etiqueta = 'antes', [string]$Solo = '', [string]$Variantes = 'claro-1280,oscuro-1280,claro-390', [string]$Paginas = '')
$edge = 'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'
$destino = Join-Path $env:TEMP 'sigte-ref\out'
New-Item -ItemType Directory -Force $destino | Out-Null
foreach ($v in $Variantes.Split(',')) {
    $tema, $ancho = $v.Split('-')
    $url = "http://localhost:8080/_ref/harness.html?d=$Dir&tema=$tema&w=$ancho"
    if ($Solo) { $url += "&solo=$Solo" }
    if ($Paginas) { $url += "&p=$Paginas" }
    $perfil = Join-Path $env:TEMP "sigte-ref\perfil-$v"
    $archivo = Join-Path $destino "$Etiqueta-$v.txt"
    $html = & $edge --headless=new --disable-gpu --no-first-run "--user-data-dir=$perfil" --window-size=1400,1000 --virtual-time-budget=200000 --timeout=240000 --dump-dom $url 2>$null | Out-String
    $m = [regex]::Match($html, '<pre id="out">([\s\S]*?)</pre>')
    $texto = [System.Net.WebUtility]::HtmlDecode($m.Groups[1].Value)
    [IO.File]::WriteAllText($archivo, $texto, (New-Object Text.UTF8Encoding $false))
    $lineas = ($texto -split "`n").Count
    "{0,-14} {1,7} elementos" -f $v, $lineas
}
