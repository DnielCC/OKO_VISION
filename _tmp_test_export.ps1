$ErrorActionPreference = "Stop"

function Build-Email([string]$prefix, [string]$domain) {
    return $prefix + '@' + $domain
}

$adminEmail = Build-Email "admin" "okovision.com"
Write-Host "Using email: $adminEmail"

$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$response1 = Invoke-WebRequest -Uri "http://localhost:8001/login" -Method Get -WebSession $session -UseBasicParsing

$csrf = ""
if ($response1.Content -match 'name="_token"\s+value="([^"]+)"') {
    $csrf = $Matches[1]
    Write-Host "CSRF token OK"
}

$body = @{
    email    = $adminEmail
    password = "12345678"
    _token   = $csrf
}

Write-Host "Logging in..."
try {
    $r2 = Invoke-WebRequest -Uri "http://localhost:8001/login" -Method Post -Body $body -WebSession $session -UseBasicParsing -MaximumRedirection 5 -ErrorAction Stop
    Write-Host "Final URL after login: $($r2.BaseResponse.ResponseUri.AbsoluteUri)"
} catch {
    Write-Host "Login redirected, continuing..."
}

Write-Host ""
Write-Host "=== Probando exportar Alertas Excel (GET sin filtros) ==="
try {
    $exportUrl = "http://localhost:8001/alertas/export/excel"
    Write-Host "URL: $exportUrl"
    $r3 = Invoke-WebRequest -Uri $exportUrl -Method Get -WebSession $session -UseBasicParsing -MaximumRedirection 3 -ErrorAction Stop
    Write-Host "Status: $($r3.StatusCode)"
    Write-Host "Content-Type: $($r3.Headers['Content-Type'])"
    Write-Host "Content-Disposition: $($r3.Headers['Content-Disposition'])"
    if ($r3.Content) {
        $len = $r3.Content.Length
        Write-Host "Response length: $len chars"
        if ($len -gt 100) {
            Write-Host "First 500 chars:"
            Write-Host $r3.Content.Substring(0, [Math]::Min(500, $len))
        }
    }
    Write-Host ""
    Write-Host "EXPORTACION ALERTAS EXITOSA!"
} catch {
    Write-Host ""
    Write-Host "=== ERROR EN EXPORT ==="
    $resp = $_.Exception.Response
    if ($resp) {
        Write-Host "Response status: $([int]$resp.StatusCode)"
        Write-Host "Location: $($resp.Headers['Location'])"
        Write-Host "Content-Type: $($resp.Headers['Content-Type'])"
    }
    if ($_.ErrorDetails.Message) {
        $errText = $_.ErrorDetails.Message
        Write-Host "Response body ($($errText.Length) chars):"
        if ($errText.Length -gt 2000) {
            Write-Host $errText.Substring(0, 2000)
            Write-Host "... (truncated)"
        } else {
            Write-Host $errText
        }
    } else {
        Write-Host "Exception: $($_.Exception.Message)"
        Write-Host $_.Exception.ToString()
    }
}

Write-Host ""
Write-Host "=== Probando exportar Reportes PDF ==="
try {
    $url4 = "http://localhost:8001/reportes/export/pdf?tipo=all&period=week"
    Write-Host "URL: $url4"
    $r4 = Invoke-WebRequest -Uri $url4 -Method Get -WebSession $session -UseBasicParsing -MaximumRedirection 3 -ErrorAction Stop
    Write-Host "Status: $($r4.StatusCode)"
    Write-Host "Content-Type: $($r4.Headers['Content-Type'])"
    Write-Host "Content-Disposition: $($r4.Headers['Content-Disposition'])"
    if ($r4.Content) { Write-Host "Response length: $($r4.Content.Length) chars" }
    Write-Host "REPORTES PDF EXITOSO!"
} catch {
    Write-Host "=== ERROR EN REPORTES PDF ==="
    if ($_.ErrorDetails.Message) {
        $errText = $_.ErrorDetails.Message
        if ($errText.Length -gt 2000) {
            Write-Host $errText.Substring(0, 2000)
            Write-Host "... (truncated)"
        } else {
            Write-Host $errText
        }
    } else {
        Write-Host "Exception: $($_.Exception.Message)"
        Write-Host $_.Exception.ToString()
    }
}
