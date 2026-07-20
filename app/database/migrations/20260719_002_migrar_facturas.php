<?php
// CLI explícito. Mueve solo facturas registradas desde public a storage y actualiza ruta.
// No se ejecuta automáticamente ni sobrescribe destinos.
if (PHP_SAPI !== 'cli') { exit(1); }
require dirname(__DIR__, 2) . '/config/config.php';
$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$destDir = dirname(__DIR__, 3) . '/storage/facturas';
if (!is_dir($destDir) && !mkdir($destDir, 0750, true)) throw new RuntimeException('No se pudo crear storage/facturas');
$rows = $pdo->query("SELECT id_factura, ruta_pdf FROM facturas WHERE ruta_pdf LIKE 'assets/facturas/%'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $source = dirname(__DIR__, 3) . '/public/' . $row['ruta_pdf'];
    if (!is_file($source)) continue;
    $name = bin2hex(random_bytes(24)) . '.pdf';
    $dest = $destDir . '/' . $name;
    if (!rename($source, $dest)) throw new RuntimeException('No se pudo mover una factura');
    $stmt = $pdo->prepare('UPDATE facturas SET ruta_pdf = :ruta WHERE id_factura = :id AND ruta_pdf = :anterior');
    $stmt->execute([':ruta' => 'facturas/' . $name, ':id' => $row['id_factura'], ':anterior' => $row['ruta_pdf']]);
}
