<?php
define('BASEPATH', 'TRUE');
define('ENVIRONMENT', 'production');
require_once __DIR__ . '/application/config/database.php';
$dbcfg = $db['default'];
$conn = new mysqli($dbcfg['hostname'], $dbcfg['username'], $dbcfg['password'], $dbcfg['database']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}
$conn->query("DELETE t1 FROM master_tarif_ipl t1 INNER JOIN master_tarif_ipl t2 WHERE t1.id > t2.id AND t1.periode_mulai = t2.periode_mulai");
$res = $conn->query("SELECT id, nama_tarif, nominal, periode_mulai, periode_selesai FROM master_tarif_ipl ORDER BY id ASC");
echo "Data master_tarif_ipl setelah pembersihan:\n";
while ($row = $res->fetch_assoc()) {
    echo "ID: #" . $row['id'] . " | " . $row['nama_tarif'] . " | Rp " . number_format($row['nominal'], 0, ',', '.') . " | " . $row['periode_mulai'] . " s.d " . ($row['periode_selesai'] ?: 'Seterusnya') . "\n";
}
@unlink(__FILE__);
