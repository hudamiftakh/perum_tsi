<?php
require 'application/config/database.php';
$db_conn = mysqli_connect($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);
$res = mysqli_query($db_conn, 'DESCRIBE master_koordinator_blok');
$cols = [];
while ($row = mysqli_fetch_assoc($res)) {
    $cols[] = $row['Field'];
}
echo json_encode($cols);
