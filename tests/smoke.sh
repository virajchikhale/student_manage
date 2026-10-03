#!/usr/bin/env bash
# Black-box smoke + security test against a running stack (DEMO=true, so the demo accounts exist).
#   BASE_URL=http://localhost:8080 tests/smoke.sh
# Run it against a fresh database (docker compose down -v): the rate-limit checks use up allowances.
set -u
BASE="${BASE_URL:-http://localhost:8080}"
DEMO_PW="${DEMO_PASSWORD:-demo12345}"
TMP="$(mktemp -d)"; trap 'rm -rf "$TMP"' EXIT
pass=0; fail=0

ok()   { pass=$((pass+1)); printf '  ok    %s\n' "$1"; }
bad()  { fail=$((fail+1)); printf '  FAIL  %s\n' "$1"; }
check() { # description, expected, actual
  if [ "$2" = "$3" ]; then ok "$1"; else bad "$1 (expected $2, got $3)"; fi
}
code()  { curl -s -o /dev/null -w '%{http_code}' "$@"; }
token() { # jar -> CSRF token of a page rendered for that session
  curl -s -c "$1" -b "$1" "$BASE/admin/index.php" | sed -n 's/.*name="csrf-token" content="\([^"]*\)".*/\1/p' | head -1
}
api() { # jar token path [curl args...] -> prints "status body"
  local jar="$1" tok="$2" path="$3"; shift 3
  curl -s -c "$jar" -b "$jar" -H "X-CSRF-Token: $tok" -w ' %{http_code}' "$BASE/$path" "$@"
}
fresh_login() { # email password role [ip-less] -> "status"; uses a brand-new session every call
  local jar="$TMP/j$RANDOM$RANDOM"; local t; t="$(token "$jar")"
  api "$jar" "$t" api/login.php -d "role=$3" --data-urlencode "email=$1" --data-urlencode "password=$2" | awk '{print $NF}'
}

echo "== availability"
check "admin login page loads" 200 "$(code "$BASE/admin/")"
check "landing page loads"     200 "$(code "$BASE/index.php")"

echo "== files that must not be served"
for p in .env .git/config Dockerfile docker-compose.yml docker-compose.prod.yml .github/workflows/ci.yml database/schema.sql database/seed.php email/phpmailer/src/PHPMailer.php docker/php.ini README.md; do
  c="$(code "$BASE/$p")"; case "$c" in 403|404) ok "/$p -> $c";; *) bad "/$p -> $c (should be 403/404)";; esac
done
c="$(code "$BASE/includes/")"; case "$c" in 403|404) ok "directory listing off (/includes/ -> $c)";; *) bad "/includes/ -> $c";; esac

echo "== response headers"
H="$(curl -s -D - -o /dev/null "$BASE/admin/")"
for h in 'x-content-type-options: nosniff' 'x-frame-options: DENY' 'referrer-policy:' 'content-security-policy:' 'permissions-policy:'; do
  echo "$H" | grep -qi "^$h" && ok "header $h" || bad "missing header $h"
done
echo "$H" | grep -qi '^server: Apache/' && bad "Server header leaks version" || ok "Server header hides version"
echo "$H" | grep -qi '^x-powered-by' && bad "X-Powered-By leaks PHP" || ok "no X-Powered-By"
echo "$H" | grep -i '^set-cookie: SMSSESSID' | grep -qi 'httponly' && ok "session cookie is HttpOnly" || bad "session cookie not HttpOnly"
echo "$H" | grep -i '^set-cookie: SMSSESSID' | grep -qi 'samesite=lax' && ok "session cookie is SameSite=Lax" || bad "session cookie lacks SameSite"

echo "== API guards"
check "POST without CSRF token is refused" 403 "$(code -X POST "$BASE/api/login.php" -d 'role=admin&email=a@b.c&password=x')"
check "GET on an API endpoint is refused"  405 "$(code "$BASE/api/login.php")"
J="$TMP/anon"; T="$(token "$J")"
check "unauthenticated attendance call -> 401" 401 "$(api "$J" "$T" api/attendance.php -d 'course_id=1' | awk '{print $NF}')"

echo "== login"
check "demo admin can sign in" 200 "$(fresh_login admin@demo.local "$DEMO_PW" admin)"
check "wrong password -> 401"  401 "$(fresh_login teacher.cs1@demo.local wrong-password teacher)"

echo "== authorization (student must not manage students)"
J="$TMP/stu"; T="$(token "$J")"
api "$J" "$T" api/login.php -d role=student --data-urlencode email=student.cs1@demo.local --data-urlencode "password=$DEMO_PW" >/dev/null
check "student calling the student-admin API -> 403" 403 "$(api "$J" "$T" api/student.php -d action=delete -d id=1 | awk '{print $NF}')"
check "student calling the admin API -> 403"          403 "$(api "$J" "$T" api/admin.php -d action=new_code | awk '{print $NF}')"

echo "== brute-force lockout survives a new session each time"
victim=hod.cs@demo.local; last=0
for i in 1 2 3 4 5 6; do last="$(fresh_login "$victim" "guess-$i" hod)"; done
check "6th wrong attempt (fresh session every time) is rate limited" 429 "$last"
check "even the right password is refused while locked" 429 "$(fresh_login "$victim" "$DEMO_PW" hod)"
check "another account from the same address still works" 200 "$(fresh_login admin@demo.local "$DEMO_PW" admin)"

echo "== OTP mail flood is capped per address"
last=0
for i in 1 2 3 4 5 6; do
  J="$TMP/o$i"; T="$(token "$J")"
  last="$(api "$J" "$T" api/otp.php -d role=teacher -d purpose=reg --data-urlencode email=flood@example.com | awk '{print $NF}')"
done
check "6th OTP request for one address -> 429" 429 "$last"

echo
echo "passed: $pass, failed: $fail"
[ "$fail" -eq 0 ]
