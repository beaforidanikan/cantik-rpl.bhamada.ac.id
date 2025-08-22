<?php
// ————————————————
// index.php (aman, tanpa cloaking)
// ————————————————


// 1) Header keamanan dasar
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
// CSP minimal; sesuaikan origin Anda
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https:; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:; connect-src 'self' https:; frame-ancestors 'self';");


// 2) Rate limit ringan untuk semua UA (bukan untuk memisahkan bot/manusia)
// Implementasi sederhana berbasis file; ganti dengan Redis/Middleware di produksi
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$tmp = sys_get_temp_dir() . '/ratelimit_' . md5($ip);
$now = time();
$window = 10; // detik
$maxReq = 60; // request per 10 detik
$bucket = ['ts'=>$now, 'cnt'=>0];
if (is_file($tmp)) {
$bucket = json_decode((string)@file_get_contents($tmp), true) ?: $bucket;
}
if ($now - ($bucket['ts'] ?? 0) > $window) {
$bucket = ['ts'=>$now, 'cnt'=>0];
}
$bucket['cnt']++;
file_put_contents($tmp, json_encode($bucket));
if ($bucket['cnt'] > $maxReq) {
http_response_code(429);
echo 'Too Many Requests';
exit;
}


// 3) OPTIONAL: layani landing page statis melalui route query yang eksplisit (tanpa cloaking)
// — Semua pengunjung mendapat konten yang sama bila mengakses ?landing=1
// — Jika landing tidak ingin diindeks, atur meta robots pada HTML landing (lihat file HTML di bawah)
if (isset($_GET['landing']) && $_GET['landing'] === '1') {
$lp = __DIR__ . '/../resources/landing/landingpage.html';
if (is_file($lp)) {
// Cache control ringan
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: public, max-age=600');
readfile($lp);
exit;
}
}


// 4) BOOTSTRAP aplikasi (contoh Laravel)
require __DIR__.'/../bootstrap/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
$request = Illuminate\Http\Request::capture()
);
$response->send();
$kernel->terminate($request, $response);
