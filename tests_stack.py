import urllib.request, urllib.error, json, ssl, re
from http.cookiejar import CookieJar
import urllib.parse
ctx = ssl._create_unverified_context()
post = json.dumps({"email":"admin@okovision.com","password":"12345678"}).encode()
hj = {"Content-Type":"application/json"}

def get(url):
    try:
        r = urllib.request.urlopen(url, context=ctx, timeout=15)
        return r.status, r.read().decode()
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode()
    except Exception as e:
        return "ERR", str(e)[:250]

def post_req(url, data):
    try:
        req = urllib.request.Request(url, data=data, headers=hj, method="POST")
        r = urllib.request.urlopen(req, context=ctx, timeout=15)
        return r.status, r.read().decode()
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode()
    except Exception as e:
        return "ERR", str(e)[:250]

print("=" * 70)
print("OKO VISION STACK INTEGRATION TEST")
print("=" * 70)
print("1) Gateway HTTPS /health ->", *get("https://localhost/health"))
s, b = get("https://localhost/api/health"); print("2) FastAPI GW /api/health ->", s, b[:200])
s, b = post_req("https://localhost/api/auth/login", post); print("3) FastAPI GW /api/auth/login ->", s); print("   Body[0:500]:", b[:500])
s, b = post_req("https://localhost/mobile-api/auth/login", post); print("4) Flask GW /mobile-api/auth/login ->", s); print("   Body[0:500]:", b[:500])
s, b = get("https://localhost/login"); print("5) Laravel HTTPS /login ->", s, "len=", len(b), "CSRF_ok=", 'name="_token"' in b)
s, b = get("https://localhost/prometheus/-/healthy"); print("6) Prometheus /-/healthy ->", s, b[:100])
s, b = get("https://localhost/grafana/api/health"); print("7) Grafana /api/health ->", s, b[:200].replace("\n",""))
print()
# MOBILE ENDPOINTS
try:
    r = urllib.request.Request("https://localhost/mobile-api/auth/login", data=post, headers=hj, method="POST")
    resp = json.loads(urllib.request.urlopen(r, context=ctx, timeout=15).read().decode())
    tok = resp.get("access_token") or ""
    print("8) Flask mobile login successful, token_len=", len(tok), "user_email=", resp.get("email"))
    if tok:
        auth = {"Authorization":"Bearer "+tok}
        endpoints = [
            ("GET /mobile-api/auth/me", "GET", "https://localhost/mobile-api/auth/me"),
            ("GET /mobile-api/vehiculos/", "GET", "https://localhost/mobile-api/vehiculos/"),
            ("GET /mobile-api/accesos/", "GET", "https://localhost/mobile-api/accesos/"),
            ("GET /mobile-api/qr/payload", "GET", "https://localhost/mobile-api/qr/payload"),
            ("GET /mobile-api/alerts/", "GET", "https://localhost/mobile-api/alerts/"),
        ]
        for label, method, url in endpoints:
            try:
                req = urllib.request.Request(url, headers=auth)
                r = urllib.request.urlopen(req, context=ctx, timeout=10)
                body = r.read().decode()
                print(f"   {label}: {r.status} body[:200]={body[:200]}")
            except urllib.error.HTTPError as e:
                print(f"   {label}: {e.code} {e.read().decode()[:200]}")
            except Exception as e:
                print(f"   {label}: ERR {str(e)[:120]}")
except Exception as e:
    print("\n8) Mobile test skipped:", e)
print()
# LARAVEL LOGIN SUBMIT TEST
print("9) Laravel login flow (GET login, submit POST)...")
try:
    from http.cookiejar import CookieJar
    cj = CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPSHandler(context=ctx), urllib.request.HTTPCookieProcessor(cj))
    r = opener.open("https://localhost/login", timeout=15)
    html = r.read().decode()
    import re
    tok_m = re.search(r'name="_token"\s+value="([^"]+)"', html)
    csrf = tok_m.group(1) if tok_m else ""
    print(f"   GET /login: {r.status}, CSRF_found={bool(csrf)}")
    # Login credenciales admin Laravel (según seeder)
    post_body = urllib.parse.urlencode({"_token": csrf, "email":"admin@okovision.com", "password":"12345678"}).encode()
    req = urllib.request.Request("https://localhost/login", data=post_body, method="POST",
        headers={"Content-Type":"application/x-www-form-urlencoded", "Referer":"https://localhost/login"})
    try:
        r2 = opener.open(req, timeout=20)
        html2 = r2.read().decode()
        ok = "dashboard" in html2.lower() or "report" in html2.lower() or "alerta" in html2.lower() or "usuario" in html2.lower()
        print(f"   POST /login: {r2.status}, len_html={len(html2)}, dashboard_like={ok}, redirect_url={r2.geturl()}")
    except urllib.error.HTTPError as e:
        print(f"   POST /login: {e.code}, body[:300]={e.read().decode()[:300]}")
except Exception as e:
    print(f"   Laravel login ERR: {str(e)[:300]}")
print()
print("="*70)
print("TEST FINISHED")
print("="*70)
