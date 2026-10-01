<?php
$sql = file_get_contents(__DIR__ . '/database/farmersbd_production.sql');
$sql = preg_replace('/CREATE TABLE IF NOT EXISTS (`[^`]+`)/', "DROP TABLE IF EXISTS $1;\nCREATE TABLE IF NOT EXISTS $1", $sql);
file_put_contents(__DIR__ . '/database/farmersbd_production_drop.sql', $sql);
echo "Done\n";
