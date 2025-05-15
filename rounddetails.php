<?php

require_once 'settings.php';

// will redirect to roundsearch.php if user accesses this page without selecting a round
if(isset($_GET['name'])) {
    $roundName = $_GET['name'];
} else {
    header('Location: roundsearch.php');
}
if(!$dbconn) {
    die("connection failed: " . mysqli_connect_error());
}

$dbconn = mysqli_connect($host,$user,$pwd,$sql_db);
$roundQuery = "SELECT Distance, TotalArrows, TargetFace FROM RoundRanges WHERE RoundName = $roundName ORDER BY Distance";
$result = $dbconn->query($roundQuery);

$tableheader = "<table border='1'><tr><th>Distance</th><th>Total Arrows</th><th>Target Face</th></tr>";
$tablefooter = "</table>";
$tablerow = "<tr><td>{$row['Distance']}</td><td>{$row['TotalArrows']}</td><td>{$row['TargetFace']}</td></tr>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View the details of a specific round">
    <meta name="keywords" content="Archery, Database, Rounds, Search, Add">
    <meta name="author" content="Donovan Quilty">
    <title>Rounds | MAJRD Archery</title>
</head>
<body>
<?php
    include_once 'header.inc';
    if($result->num_rows > 0) {
        echo "<h2>Round Details for {$roundName}</h2>";
        echo $tableheader;
        while($row = $result->fetch_assoc()) {
            echo $tablerow;
        }
        echo $tablefooter;
    } else {
        // this should never happen
        echo "<p>No details for the round were found.</p>";
    }
    include_once 'footer.inc';
?>


</body>
</html>
