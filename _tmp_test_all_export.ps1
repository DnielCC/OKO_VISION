$ErrorActionPreference = "Stop"

function Build-Email([string]$prefix, [string]$domain) {
    return $prefix + '@' + $domain
}

function Q([string]$url) {
    return $url.Replace('&', [char]38)
}

$adminEmail = Build-Email "admin" "okovision.com"
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

# Login
$r1 = Invoke-WebRequest -Uri "http://localhost:8001/login" -Method Get -WebSession $session -UseBasicParsing
$csrf = ""
if ($r1.Content -match 'name="_token"\s+value="([^"]+)"') { $csrf = $Matches[1] }
$body = @{ email = $adminEmail; password = "12345678"; _token = $csrf }
Invoke-WebRequest -Uri "http://localhost:8001/login" -Method Post -Body $body -WebSession $session -UseBasicParsing -MaximumRedirection 5 | Out-Null
Write-Host "Login OK"

Write-Host ""
Write-Host "=== 1. Alertas Excel ==="
try {
    $rA = Invoke-WebRequest -Uri (Q "http://localhost:8001/alertas/export/excel") -Method Get -WebSession $session -UseBasicParsing -MaximumRedirection 0 -ErrorAction Stop
    Write-Host "Status: $($rA.StatusCode)"
    Write-Host "Content-Type: $($rA.Headers['Content-Type'])"
    Write-Host "Content-Disposition: $($rA.Headers['Content-Disposition'])"
    Write-Host "Length: $($rA.Content.Length)"
    if ($rA.Content.Length -gt 30) { Write-Host ("First chars: " + $rA.Content.Substring(0, [Math]::Min(100, $rA.Content.Length))) }
    Write-Host "✅ ALERTAS EXCEL OK"
} catch {
    $resp = $_.Exception.Response
    if ($resp) {
        Write-Host "Status: $([int]$resp.StatusCode)"
        Write-Host "Location: $($resp.Headers['Location'])"
        try {
            $sr = New-Object IO.StreamReader($resp.GetResponseStream())
            $txt = $sr.ReadToEnd()
            if ($txt.Length -gt 2000) { Write-Host ("Body: " + $txt.Substring(0,2000) + "...(truncated)") }
            else { Write-Host "Body: $txt" }
        } catch {}
    } else { Write-Host "Exception: $($_.Exception.Message)" }
}

Write-Host ""
Write-Host "=== 2. Reportes PDF ==="
try {
    $urlP = Q "http://localhost:8001/reportes/export/pdf?tipo=all&period=week"
    $rP = Invoke-WebRequest -Uri $urlP -Method Get -WebSession $session -UseBasicParsing -MaximumRedirection 0 -ErrorAction Stop
    Write-Host "Status: $($rP.StatusCode)"
    Write-Host "Content-Type: $($rP.Headers['Content-Type'])"
    Write-Host "Content-Disposition: $($rP.Headers['Content-Disposition'])"
    Write-Host "Length: $($rP.Content.Length)"
    Write-Host "✅ REPORTES PDF OK"
} catch {
    $resp = $_.Exception.Response
    if ($resp) {
        Write-Host "Status: $([int]$resp.StatusCode)"
        Write-Host "Location: $($resp.Headers['Location'])"
        try {
            $sr = New-Object IO.StreamReader($resp.GetResponseStream())
            $txt = $sr.ReadToEnd()
            if ($txt.Length -gt 2000) { Write-Host ("Body: " + $txt.Substring(0,2000) + "...(truncated)") }
            else { Write-Host "Body: $txt" }
        } catch {}
    } else { Write-Host "Exception: $($_.Exception.Message)" }
}

Write-Host ""
Write-Host "=== 3. Reportes CSV ==="
try {
    $urlC = Q "http://localhost:8001/reportes/export/csv?tables=1&tipo=all&period=week"
    $rC = Invoke-WebRequest -Uri $urlC -Method Get -WebSession $session -UseBasicParsing -MaximumRedirection 0 -ErrorAction Stop
    Write-Host "Status: $($rC.StatusCode)"
    Write-Host "Content-Type: $($rC.Headers['Content-Type'])"
    Write-Host "Content-Disposition: $($rC.Headers['Content-Disposition'])"
    Write-Host "Length: $($rC.Content.Length)"
    if ($rC.Content.Length -gt 50) { Write-Host ("First: " + $rC.Content.Substring(0, [Math]::Min(120, $rC.Content.Length))) }
    Write-Host "✅ REPORTES CSV OK"
} catch {
    $resp = $_.Exception.Response
    if ($resp) {
        Write-Host "Status: $([int]$resp.StatusCode)"
        Write-Host "Location: $($resp.Headers['Location'])"
        try {
            $sr = New-Object IO.StreamReader($resp.GetResponseStream())
            $txt = $sr.ReadToEnd()
            if ($txt.Length -gt 2000) { Write-Host ("Body: " + $txt.Substring(0,2000) + "...(truncated)") }
            else { Write-Host "Body: $txt" }
        } catch {}
    } else { Write-Host "Exception: $($_.Exception.Message)" }
}

Write-Host ""
Write-Host "=== 4. Reportes Excel ==="
try {
    $urlE = Q "http://localhost:8001/reportes/export/excel?tables=1&summary=1&tipo=all&period=week"
    $rE = Invoke-WebRequest -Uri $urlE -Method Get -WebSession $session -UseBasicParsing -MaximumRedirection 0 -ErrorAction Stop
    Write-Host "Status: $($rE.StatusCode)"
    Write-Host "Content-Type: $($rE.Headers['Content-Type'])"
    Write-Host "Content-Disposition: $($rE.Headers['Content-Disposition'])"
    Write-Host "Length: $($rE.Content.Length)"
    if ($rE.Content.Length -gt 50) { Write-Host ("First: " + $rE.Content.Substring(0, [Math]::Min(120, $rE.Content.Length))) }
    Write-Host "✅ REPORTES EXCEL OK"
} catch {
    $resp = $_.Exception.Response
    if ($resp) {
        Write-Host "Status: $([int]$resp.StatusCode)"
        Write-Host "Location: $($resp.Headers['Location'])"
        try {
            $sr = New-Object IO.StreamReader($resp.GetResponseStream())
            $txt = $sr.ReadToEnd()
            if ($txt.Length -gt 2000) { Write-Host ("Body: " + $txt.Substring(0,2000) + "...(truncated)") }
            else { Write-Host "Body: $txt" }
        } catch {}
    } else { Write-Host "Exception: $($_.Exception.Message)" }
}
