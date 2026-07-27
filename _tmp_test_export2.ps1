$ErrorActionPreference = "Stop"

function Build-Email([string]$prefix, [string]$domain) {
    return $prefix + '@' + $domain
}

$adminEmail = Build-Email "admin" "okovision.com"
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

# 1. Login
$r1 = Invoke-WebRequest -Uri "http://localhost:8001/login" -Method Get -WebSession $session -UseBasicParsing
$csrf = ""
if ($r1.Content -match 'name="_token"\s+value="([^"]+)"') { $csrf = $Matches[1] }
$body = @{ email = $adminEmail; password = "12345678"; _token = $csrf }
Invoke-WebRequest -Uri "http://localhost:8001/login" -Method Post -Body $body -WebSession $session -UseBasicParsing -MaximumRedirection 5 -ErrorAction SilentlyContinue | Out-Null

# 2. Test /alertas page first
Write-Host "=== GET /alertas ==="
try {
    $rA = Invoke-WebRequest -Uri "http://localhost:8001/alertas" -Method Get -WebSession $session -UseBasicParsing -MaximumRedirection 0 -ErrorAction Stop
    Write-Host "Status: $($rA.StatusCode)  Len: $($rA.Content.Length)"
    if ($rA.Content -match "<title>([^<]+)</title>") { Write-Host "Title: $($Matches[1])" }
} catch {
    $resp = $_.Exception.Response
    if ($resp) {
        Write-Host "Status: $([int]$resp.StatusCode)"
        Write-Host "Location: $($resp.Headers['Location'])"
    } else {
        Write-Host "Exception: $($_.Exception.Message)"
    }
}

# 3. Test export excel with NO redirects
Write-Host ""
Write-Host "=== GET /alertas/export/excel (sin redirecciones) ==="
try {
    $rE = Invoke-WebRequest -Uri "http://localhost:8001/alertas/export/excel" -Method Get -WebSession $session -UseBasicParsing -MaximumRedirection 0 -ErrorAction Stop
    Write-Host "Status: $($rE.StatusCode)"
    Write-Host "Content-Type: $($rE.Headers['Content-Type'])"
    Write-Host "Content-Disposition: $($rE.Headers['Content-Disposition'])"
    Write-Host "Body len: $($rE.Content.Length)"
    if ($rE.Content.Length -gt 10) {
        $start = $rE.Content.Substring(0, [Math]::Min(200, $rE.Content.Length))
        Write-Host "Body start: $start"
    }
} catch {
    $resp = $_.Exception.Response
    if ($resp) {
        Write-Host "Status: $([int]$resp.StatusCode)"
        Write-Host "Location: $($resp.Headers['Location'])"
        Write-Host "Content-Type: $($resp.Headers['Content-Type'])"
        try {
            $sr = New-Object IO.StreamReader($resp.GetResponseStream())
            $bodyText = $sr.ReadToEnd()
            Write-Host "Body ($($bodyText.Length) chars):"
            if ($bodyText.Length -gt 2000) { Write-Host $bodyText.Substring(0, 2000) "...(truncated)" }
            else { Write-Host $bodyText }
        } catch { Write-Host "Cannot read body: $($_.Exception.Message)" }
    } else {
        Write-Host "Exception: $($_.Exception.Message)"
    }
}
