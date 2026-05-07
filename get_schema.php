<?php
$mysqli = new mysqli("127.0.0.1", "root", "root", "ads_aggregator", 3306);
if ($mysqli->connect_errno) { echo "Failed: " . $mysqli->connect_error; exit; }
$res = $mysqli->query("DESCRIBE clients");
while ($row = $res->fetch_assoc()) { echo $row["Field"] . " " . $row["Type"] . "\n"; }
echo "--- \n";
$res2 = $mysqli->query("DESCRIBE accounts");
while ($row = $res2->fetch_assoc()) { echo $row["Field"] . " " . $row["Type"] . "\n"; }

