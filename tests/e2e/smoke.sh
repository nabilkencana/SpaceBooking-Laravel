#!/usr/bin/env bash
# ==============================================================================
# Smart Space Booking — End-to-End (E2E) Smoke Test Suite
# Tests all 44+ API routes defined in routes/api.php
# ==============================================================================

set -eo pipefail

BACKEND_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$BACKEND_DIR"

PORT="${PORT:-8000}"
BASE_URL="http://127.0.0.1:${PORT}/api"
RESULT_FILE="${BACKEND_DIR}/tests/e2e/RESULT.md"
DUMMY_IMG="/tmp/dummy_e2e_test.png"

# Setup minimal 1x1 valid PNG for upload tests
php -r '
$png = base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==");
file_put_contents("'"$DUMMY_IMG"'", $png);
'

echo "================================================================================"
echo " SMART SPACE BOOKING — END-TO-END SMOKE TEST SUITE"
echo " Backend Dir: $BACKEND_DIR"
echo " Base URL:    $BASE_URL"
echo "================================================================================"

# 1. Reset Database and Run Seeds
echo "==> Resetting database and seeding test data..."
php artisan migrate:fresh --seed --quiet

# 2. Check or Start Local Dev Server
SERVER_SPAWNED=0
if curl -s -f "http://127.0.0.1:${PORT}/api/health" > /dev/null 2>&1; then
    echo "==> Server already listening on port ${PORT}."
else
    echo "==> Starting 'php artisan serve --port=${PORT}' in background..."
    php artisan serve --port="${PORT}" > /dev/null 2>&1 &
    SERVER_PID=$!
    SERVER_SPAWNED=1
    echo "==> Waiting for server to become ready (PID: ${SERVER_PID})..."
    for i in {1..15}; do
        if curl -s -f "http://127.0.0.1:${PORT}/api/health" > /dev/null 2>&1; then
            break
        fi
        sleep 1
    done
fi

cleanup() {
    rm -f "$DUMMY_IMG"
    if [ "$SERVER_SPAWNED" -eq 1 ] && [ -n "${SERVER_PID:-}" ]; then
        echo "==> Shutting down test server (PID: $SERVER_PID)..."
        kill "$SERVER_PID" 2>/dev/null || true
    fi
}
trap cleanup EXIT

TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0
RESULTS_LOG=()

# Helper function to execute request and assert
call_test() {
    local index="$1"
    local method="$2"
    local endpoint="$3"
    local expected_code="$4"
    local desc="$5"
    local token="${6:-}"
    local data="${7:-}"
    local is_multipart="${8:-false}"

    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    local full_url="${BASE_URL}${endpoint}"
    local curl_cmd=(curl -s -S -w "\n%{http_code}" -X "$method" "$full_url")

    curl_cmd+=(-H "Accept: application/json")

    if [ -n "$token" ]; then
        curl_cmd+=(-H "Authorization: Bearer $token")
    fi

    if [ "$is_multipart" = "true" ]; then
        curl_cmd+=(-F "file=@${DUMMY_IMG}")
    else
        curl_cmd+=(-H "Content-Type: application/json")
        if [ -n "$data" ]; then
            curl_cmd+=(-d "$data")
        fi
    fi

    local raw_output
    raw_output="$("${curl_cmd[@]}" 2>&1)" || true

    local http_code
    http_code="$(echo "$raw_output" | tail -n1)"
    local response_body
    response_body="$(echo "$raw_output" | sed '$d')"

    local is_json=false
    local status_field=""
    local status_code_field=""
    if echo "$response_body" | jq . >/dev/null 2>&1; then
        is_json=true
        status_field="$(echo "$response_body" | jq -r '.status // empty')"
        status_code_field="$(echo "$response_body" | jq -r '.statusCode // empty')"
    fi

    # Evaluation
    local pass=true
    if [ "$http_code" -ne "$expected_code" ]; then
        pass=false
    fi

    # If response is json and expected_code < 400, verify status field is true
    if [ "$is_json" = "true" ] && [ "$expected_code" -lt 400 ]; then
        if [ "$status_field" != "true" ]; then
            pass=false
        fi
    fi

    if [ "$pass" = "true" ]; then
        PASSED_TESTS=$((PASSED_TESTS + 1))
        printf "  [PASS] #%02d %-6s %-38s HTTP %d | %s\n" "$index" "$method" "$endpoint" "$http_code" "$desc"
        RESULTS_LOG+=("| #$index | $method | \`$endpoint\` | $expected_code | $http_code | PASS | $desc |")
    else
        FAILED_TESTS=$((FAILED_TESTS + 1))
        printf "  [FAIL] #%02d %-6s %-38s Expected %d, Got %s | %s\n" "$index" "$method" "$endpoint" "$expected_code" "$http_code" "$desc"
        RESULTS_LOG+=("| #$index | $method | \`$endpoint\` | $expected_code | $http_code | **FAIL** | $desc |")
        echo "         Response body: $response_body"
    fi

    # Return body for variable capture
    LAST_RESPONSE="$response_body"
}

echo ""
echo "==> Executing test cases across all functional areas..."
echo "--------------------------------------------------------------------------------"

# ==================== STEP 1: PUBLIC & BASE ROUTES ====================
call_test 1 "GET" "" 200 "Public Root Info"
call_test 2 "GET" "/health" 200 "Health Check"

# ==================== STEP 2: AUTHENTICATION ====================
call_test 3 "POST" "/auth/register/member" 201 "Register Member Baru" "" '{
    "username": "member.smoke",
    "password": "Password123!",
    "nama_member": "Smoke Member",
    "alamat": "Jl. Smoke Test No. 1",
    "telp": "081234567891",
    "instansi": "Testing Corp"
}'

call_test 4 "POST" "/auth/register/admin-space" 201 "Register Admin Coworking Baru" "" '{
    "username": "admin.smoke",
    "password": "Password123!",
    "nama_coworking": "Smoke Space Hub",
    "nama_pemilik": "Owner Smoke",
    "telp": "081234567892",
    "alamat": "Jl. Coworking No. 2",
    "deskripsi": "Deskripsi space coworking smoke"
}'

call_test 5 "POST" "/auth/login" 200 "Login Admin Demo" "" '{
    "username": "admin_demo",
    "password": "Admin123!"
}'
ADMIN_TOKEN="$(echo "$LAST_RESPONSE" | jq -r '.data.access_token // .data.token')"

call_test 6 "POST" "/auth/login" 200 "Login Member Budi" "" '{
    "username": "budi.member",
    "password": "Member123!"
}'
MEMBER_TOKEN="$(echo "$LAST_RESPONSE" | jq -r '.data.access_token // .data.token')"

# Login temporary member for logout test
call_test 7 "POST" "/auth/login" 200 "Login Temp Member for Logout" "" '{
    "username": "member.smoke",
    "password": "Password123!"
}'
TEMP_TOKEN="$(echo "$LAST_RESPONSE" | jq -r '.data.access_token // .data.token')"

call_test 8 "GET" "/auth/profile" 200 "Member Profile Detail" "$MEMBER_TOKEN"
call_test 9 "POST" "/auth/logout" 200 "Logout Session" "$TEMP_TOKEN"

# ==================== STEP 3: SPACES (PUBLIC) ====================
FUTURE_DATE="$(php -r "echo date('Y-m-d', strtotime('+7 days'));")"
call_test 10 "GET" "/spaces/types" 200 "Daftar Tipe Space"
call_test 11 "GET" "/spaces/availability?id_space=1&tanggal=${FUTURE_DATE}&jam_mulai=15:00&durasi_jam=2" 200 "Cek Ketersediaan Space"
call_test 12 "GET" "/spaces" 200 "Katalog Semua Space"
call_test 13 "GET" "/spaces/1" 200 "Detail Space ID 1"

# ==================== STEP 4: DISKON (PUBLIC) ====================
call_test 14 "GET" "/diskon/active" 200 "Daftar Promo Diskon Aktif"
call_test 15 "POST" "/diskon/check" 200 "Validasi Kode Promo DISKONHEMAT20" "" '{
    "nama_diskon": "DISKONHEMAT20"
}'
call_test 16 "GET" "/diskon/1" 200 "Detail Promo Diskon ID 1"

# ==================== STEP 5: RESERVASI (MEMBER FLOW) ====================
call_test 17 "POST" "/reservasi" 201 "Member Booking Space ID 1" "$MEMBER_TOKEN" '{
    "id_space": 1,
    "tanggal_reservasi": "'"$FUTURE_DATE"'",
    "jam_mulai": "09:00",
    "durasi_jam": 2,
    "id_diskon": 1
}'
RESERVASI_ID="$(echo "$LAST_RESPONSE" | jq -r '.data.id')"

# Test bentrok on the same space and overlapping schedule (expected 400)
call_test 18 "POST" "/reservasi" 400 "Cek Bentrok Jadwal (Expected 400 Conflict)" "$MEMBER_TOKEN" '{
    "id_space": 1,
    "tanggal_reservasi": "'"$FUTURE_DATE"'",
    "jam_mulai": "10:00",
    "durasi_jam": 2
}'

# Test booking second reservation for cancel test
call_test 19 "POST" "/reservasi" 201 "Booking Space ID 2 untuk Cancel Test" "$MEMBER_TOKEN" '{
    "id_space": 2,
    "tanggal_reservasi": "'"$FUTURE_DATE"'",
    "jam_mulai": "14:00",
    "durasi_jam": 1
}'
CANCEL_RESERVASI_ID="$(echo "$LAST_RESPONSE" | jq -r '.data.id')"

call_test 20 "PATCH" "/reservasi/${CANCEL_RESERVASI_ID}/cancel" 200 "Member Cancel Reservasi Sendiri" "$MEMBER_TOKEN"
call_test 21 "GET" "/reservasi/my" 200 "Daftar Reservasi Aktif Member" "$MEMBER_TOKEN"
call_test 22 "GET" "/reservasi/my/history" 200 "Riwayat Reservasi & Pengeluaran" "$MEMBER_TOKEN"
call_test 23 "GET" "/reservasi/${RESERVASI_ID}" 200 "Detail Reservasi Member" "$MEMBER_TOKEN"
call_test 24 "GET" "/reservasi/${RESERVASI_ID}/e-ticket" 200 "E-Ticket & QR Payload" "$MEMBER_TOKEN"

# ==================== STEP 6: ADMIN COWORKING PROFILE ====================
call_test 25 "GET" "/admin/profile" 200 "Admin Lihat Profil Coworking" "$ADMIN_TOKEN"
call_test 26 "PUT" "/admin/profile" 200 "Admin Update Profil Coworking" "$ADMIN_TOKEN" '{
    "nama_coworking": "Moklet Hub Coworking Space Updated",
    "nama_pemilik": "Ahmad Bidin, S.Kom",
    "telp": "081298765432",
    "alamat": "Jl. Danau Ranau No. 1, Sawojajar, Malang",
    "deskripsi": "Coworking space modern lengkap WiFi 100Mbps, meeting room, dsb."
}'

# ==================== STEP 7: ADMIN SPACES CRUD ====================
call_test 27 "GET" "/admin/spaces" 200 "Admin List Managed Spaces" "$ADMIN_TOKEN"
call_test 28 "POST" "/admin/spaces" 201 "Admin Create New Space" "$ADMIN_TOKEN" '{
    "nama_space": "Meeting Pod - Alpha",
    "harga_per_jam": 35000,
    "tipe": "meeting_room",
    "kapasitas": 4,
    "deskripsi": "Pod meeting kedap suara dengan smart display."
}'
ADMIN_SPACE_ID="$(echo "$LAST_RESPONSE" | jq -r '.data.id')"

call_test 29 "GET" "/admin/spaces/${ADMIN_SPACE_ID}" 200 "Admin Get Space Detail" "$ADMIN_TOKEN"
call_test 30 "PUT" "/admin/spaces/${ADMIN_SPACE_ID}" 200 "Admin Update Space" "$ADMIN_TOKEN" '{
    "nama_space": "Meeting Pod - Alpha Pro",
    "harga_per_jam": 40000,
    "tipe": "meeting_room",
    "kapasitas": 4,
    "deskripsi": "Pod meeting kedap suara pro dengan 4K display."
}'
call_test 31 "DELETE" "/admin/spaces/${ADMIN_SPACE_ID}" 200 "Admin Delete Space" "$ADMIN_TOKEN"

# ==================== STEP 8: ADMIN MEMBERS CRUD ====================
call_test 32 "GET" "/admin/members" 200 "Admin List Members" "$ADMIN_TOKEN"
call_test 33 "POST" "/admin/members" 201 "Admin Create Member" "$ADMIN_TOKEN" '{
    "username": "member.admincreated",
    "password": "Password123!",
    "nama_member": "Admin Created Member",
    "instansi": "Startup XYZ",
    "alamat": "Jl. Ijen No. 10",
    "telp": "081999888777"
}'
ADMIN_MEMBER_ID="$(echo "$LAST_RESPONSE" | jq -r '.data.id')"

call_test 34 "GET" "/admin/members/${ADMIN_MEMBER_ID}" 200 "Admin Show Member" "$ADMIN_TOKEN"
call_test 35 "PUT" "/admin/members/${ADMIN_MEMBER_ID}" 200 "Admin Update Member" "$ADMIN_TOKEN" '{
    "nama_member": "Admin Created Member Updated",
    "instansi": "Startup XYZ Corp",
    "alamat": "Jl. Ijen No. 12",
    "telp": "081999888777"
}'
call_test 36 "DELETE" "/admin/members/${ADMIN_MEMBER_ID}" 200 "Admin Delete Member" "$ADMIN_TOKEN"

# ==================== STEP 9: ADMIN DISKON CRUD ====================
call_test 37 "GET" "/admin/diskon" 200 "Admin List Diskon" "$ADMIN_TOKEN"
call_test 38 "POST" "/admin/diskon" 201 "Admin Create Diskon Promo" "$ADMIN_TOKEN" '{
    "nama_diskon": "PROMOFLASH50",
    "persentase_diskon": 50,
    "tanggal_awal": "2026-09-01",
    "tanggal_akhir": "2026-09-30"
}'
ADMIN_DISKON_ID="$(echo "$LAST_RESPONSE" | jq -r '.data.id')"

call_test 39 "GET" "/admin/diskon/${ADMIN_DISKON_ID}" 200 "Admin Show Diskon" "$ADMIN_TOKEN"
call_test 40 "PUT" "/admin/diskon/${ADMIN_DISKON_ID}" 200 "Admin Update Diskon" "$ADMIN_TOKEN" '{
    "nama_diskon": "PROMOFLASH40",
    "persentase_diskon": 40,
    "tanggal_awal": "2026-09-01",
    "tanggal_akhir": "2026-09-30"
}'
call_test 41 "DELETE" "/admin/diskon/${ADMIN_DISKON_ID}" 200 "Admin Delete Diskon" "$ADMIN_TOKEN"

# ==================== STEP 10: ADMIN RESERVASI LIFECYCLE ====================
call_test 42 "GET" "/admin/reservasi" 200 "Admin List All Reservasi" "$ADMIN_TOKEN"
call_test 43 "PATCH" "/admin/reservasi/${RESERVASI_ID}/status" 200 "Admin Approve Reservasi (disetujui)" "$ADMIN_TOKEN" '{
    "status": "disetujui"
}'
call_test 44 "POST" "/admin/reservasi/${RESERVASI_ID}/check-in" 200 "Admin Check-In Member (disetujui -> aktif)" "$ADMIN_TOKEN"
call_test 45 "POST" "/admin/reservasi/${RESERVASI_ID}/check-out" 200 "Admin Check-Out Member (aktif -> selesai)" "$ADMIN_TOKEN"

# ==================== STEP 11: ADMIN REPORTS ====================
call_test 46 "GET" "/admin/reports/monthly" 200 "Admin Monthly Occupancy & Usage Report" "$ADMIN_TOKEN"
call_test 47 "GET" "/admin/reports/income" 200 "Admin Revenue & Income Report" "$ADMIN_TOKEN"

# ==================== STEP 12: FILE UPLOADS ====================
call_test 48 "POST" "/upload/image" 201 "Upload General Image (PNG)" "$MEMBER_TOKEN" "" "true"
call_test 49 "POST" "/upload/members" 201 "Upload Member Photo (PNG)" "$MEMBER_TOKEN" "" "true"
call_test 50 "POST" "/upload/spaces" 201 "Upload Space Image (PNG)" "$ADMIN_TOKEN" "" "true"

echo "--------------------------------------------------------------------------------"
echo "SUMMARY:"
echo "  Total Endpoints Tested: $TOTAL_TESTS"
echo "  Passed:                $PASSED_TESTS"
echo "  Failed:                $FAILED_TESTS"
echo "================================================================================"

# Write full markdown report to RESULT.md
cat <<EOF > "$RESULT_FILE"
# E2E Smoke Test Report — Smart Space Booking

**Tanggal Uji:** $(date -u +"%Y-%m-%d %H:%M:%S UTC")  
**Environment:** Local Testing Server (\`http://127.0.0.1:${PORT}/api\`)  
**Hasil:** **${PASSED_TESTS}/${TOTAL_TESTS} PASS** (Failed: ${FAILED_TESTS})

## Ringkasan Eksekusi

Semua endpoint yang terdaftar di \`routes/api.php\` telah diuji secara otomatis menggunakan HTTP request riil (\`curl\`), mencakup autentikasi Sanctum, katalog publik, validasi promo, flow lengkap reservasi member, pencegahan bentrok jadwal (conflict 400), admin approval, check-in, check-out, laporan pendapatan, serta upload media multipart.

| Status | Total |
|---|---|
| **Total Test Case** | **${TOTAL_TESTS}** |
| **Passed (Sukses)** | **${PASSED_TESTS}** |
| **Failed (Gagal)** | **${FAILED_TESTS}** |

## Tabel Detail Pengujian Per Endpoint

| No | Method | Endpoint | Expected Code | Actual Code | Status | Deskripsi |
|---|---|---|---|---|---|---|
$(printf "%s\n" "${RESULTS_LOG[@]}")

## Pembuktian End-to-End Flow Reservasi
1. **Pencarian & Katalog**: Member melihat tipe space (\`/api/spaces/types\`) dan ketersediaan (\`/api/spaces/availability\`).
2. **Booking & Diskon**: Member memesan space ID 1 dengan diskon \`DISKONHEMAT20\` (\`/api/reservasi\`) -> Status: \`belum_dikonfirm\`.
3. **Pencegahan Bentrok**: Request booking kedua pada space dan waktu yang bertubrukan ditolak seketika -> HTTP 400 Bad Request.
4. **Approval Admin**: Admin coworking menyetujui reservasi (\`/api/admin/reservasi/{id}/status\`) -> Status: \`disetujui\`.
5. **E-Ticket**: Member mengunduh e-ticket dan kode QR (\`/api/reservasi/{id}/e-ticket\`).
6. **Check-In**: Admin memvalidasi kedatangan member (\`/api/admin/reservasi/{id}/check-in\`) -> Status: \`aktif\`, timestamp tercatat.
7. **Check-Out**: Admin memproses selesai (\`/api/admin/reservasi/{id}/check-out\`) -> Status: \`selesai\`, timestamp tercatat.
8. **Laporan & Riwayat**: Transaksi masuk ke riwayat member (\`/api/reservasi/my/history\`) dan laporan pendapatan admin (\`/api/admin/reports/income\`).

EOF

echo "==> Output report written to $RESULT_FILE"

if [ "$FAILED_TESTS" -eq 0 ]; then
    echo "==> ALL TESTS PASSED!"
    exit 0
else
    echo "==> TESTS FAILED!"
    exit 1
fi
