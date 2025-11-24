<?php
require_once __DIR__ . '/../PHP/config.php';

$prices = KindoraDatabase::query('SELECT pp.price_usd, d.slug FROM package_prices pp JOIN packages p ON p.id = pp.package_id JOIN destinations d ON d.id = p.destination_id ORDER BY d.id LIMIT 10');
echo json_encode($prices);
?>