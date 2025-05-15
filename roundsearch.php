<?php

require_once 'settings.php';

$dbconn = mysqli_connect($host,$user,$pwd,$sql_db);

if(!$dbconn) {
    die("connection failed: " . mysqli_connect_error());
}
$tableheader = "<table border='1'><tr><th>Round Name</th><th>Total Arrows</th><th>Max Score</th></tr>";
$tablefooter = "</table>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Search through different rounds here, or create your own">
    <meta name="keywords" content="Archery, Database, Rounds, Search, Add">
    <meta name="author" content="Donovan Quilty">
    <title>Rounds | MAJRD Archery</title>
</head>
<body>
    
<?php
    include_once 'header.inc';
?>

<h2>Search Rounds</h2>
<p>Search through rounds to see the specifications of them here</p>

<form method="post">

    <label for="roundname">Search by Round Name:</label>
    <input type="text" name="roundname" id="roundname" placeholder="Enter Round Name">
    <button type="submit" name="action" value="search_by_roundname">Search by Round Name</button>
    <br><br>
     
    <button type="submit" name="action" value="list_all">See all rounds</button>

</form>

<?php

if ($_SERVER['REQUETS_METHOD'] === 'POST')
{
    $action = $_POST['action'];

    switch($action) {
        case 'list_all':
            listAllRounds($dbconn);
            break;
        case 'search_by_roundname':
            $roundname = isset($_POST['roundname']) ? $_POST['roundname'] : '';
            listByRoundName($dbconn, $roundname);
            break;
    }
    mysqli_close($dbconn);
}

//List all rounds names function
function listAllRounds($dbconn)
{
    $query = "SELECT * FROM RoundTypes";
    $result = $dbconn->query($query);
    // Table created to list all rounds
    if($result->num_rows > 0) {
        echo $tableheader;
        while($row = $result->fetch_assoc()) {
            $roundName = htmlspecialchars($row['RoundName']);
            echo "<tr><td><a href='rounddetails.php?id=$roundName>{$roundName}</a></td><td>{$row['TotalArrows']}</td><td>{$row['MaxScore']}</td></tr>";
        }
        echo $tablefooter;} else {
        echo "<p>No rounds found. There may be an issue with the SQL connection</p>";
    }   
}

//List round names by name
function listByRoundName($dbconn, $roundname)
{
    //prepared statement to avoid SQL injection
    $stmt = $dbconn->prepare("SELECT * FROM RoundTypes WHERE RoundName LIKE ?");
    $stmt->bind_param("s", $roundname);
    $stmt->execute();
    $result = $stmt->get_result();

    if($reuslt->num_rows > 0) {
        echo $tableheader;
        while($row = $result->fetch_assoc()) {
            echo $tablerow;
        }
        echo $tablefooter;}
    else {
        echo "<p>No rounds found with a name: " . $roundname . "</p>";
    }
    $stmt->close();
}

include_once 'footer.inc';
?>
</body>
</html>