<?php

require_once 'settings.php';
$dbconn = mysqli_connect($host,$user,$pswd,$dbnm);

// will redirect to roundsearch.php if user accesses this page without selecting a round
if(isset($_GET['name'])) {
    $roundName = $_GET['name'];
} else {
    header('Location: roundsearch.php');
}
if(!$dbconn) {
    die("connection failed: " . mysqli_connect_error());
}

$roundQuery = "SELECT Distance, TotalArrows, TargetFace FROM RoundRange WHERE RoundName = ? ORDER BY Distance DESC";
$stmt = $dbconn->prepare($roundQuery);
$stmt->bind_param("s", $roundName);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View the details of a specific round">
    <meta name="keywords" content="Archery, Database, Rounds, Search, Add">
    <meta name="author" content="Donovan Quilty">
    <link rel="stylesheet" href="styles.css">
    <title>Rounds | MAJRD Archery</title>
</head>
<body>
<?php
    include_once 'header.inc';
    if($result->num_rows > 0) {
        echo "<h2>Round Details for {$roundName}</h2>";
        echo "<table border='1'><tr><th>Distance</th><th>Total Arrows</th><th>Target Face</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['Distance']}m</td><td>{$row['TotalArrows']}</td><td>{$row['TargetFace']}cm</td></tr>";
        }
        echo "</table>";
    } else {
        // this should never happen
        echo "<p>No details for the round were found.</p>";
    }
    include_once 'footer.inc';
?>

<h3><a href="roundsearch.php">View more rounds</a></h3>
</body>
</html>
