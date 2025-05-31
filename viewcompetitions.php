<?php

require_once 'settings.php';

$dbconn = mysqli_connect($host,$user,$pswd,$dbnm);

if(!$dbconn) {
    die("connection failed: " . mysqli_connect_error());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View competitions here">
    <meta name="keywords" content="Archery, Database, Competitions, View">
    <meta name="author" content="Donovan Quilty, Reeve Kariyawasam">
    <link rel="stylesheet" href="styles.css">
    <title>Rounds | MAJRD Archery</title>
</head>
<body>

<?php
    include_once 'header.inc';
?>

<h2>View Competitions</h2>
<p>View previously created competitions and their details here:</p>

<br><br><br><br>

<?php
include_once 'footer.inc';
?>
</body>
</html>