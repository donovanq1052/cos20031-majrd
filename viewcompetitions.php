<?php

// competition name unique
// round name should be from the ones created in the roundtype table

require_once 'functions/settings.php';

$dbconn = mysqli_connect($host,$user,$pswd,$dbnm);

if(!$dbconn) {
    die("connection failed: " . mysqli_connect_error());
}

// query to get the list of competitions to present in table later
$competitionListQuery = "SELECT * FROM Competition ORDER BY CompetitionName";
$stmt = $dbconn->prepare($competitionListQuery);
$stmt->execute();
$competitionListQueryResult = $stmt->get_result();

// query that gets the round types from a 'RoundType' table to show in drop
// down list when creating a new competition.
$roundTypeQuery = "SELECT * FROM RoundType ORDER BY RoundName";
$stmtb = $dbconn->prepare($roundTypeQuery);
$stmtb->execute();
$roundTypeQueryResult = $stmtb->get_result();
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
    <title>View Competitions | MAJRD Archery</title>
</head>
<body>

<?php
    include_once 'header.inc';
?>

<h2>Manage Competitions</h2>

<?php
    // text boxes and stuff to add a new competition. Doing it inside php block
    echo "\n<br><h3>Add a new competition:</h3><br>";
    echo "\n" . '<label for="competition-name">Name: </label>';
    echo "\n" . '<input type="text" name="competition-name" placeholder="Enter competition name"><br>';
    echo "\n" . '<label for="round-type">Round Type: </label>';
    echo "\n" .'<select name="round-type">
    <option value="">Please select</option>' . "\n";
    printDropDownValues($roundTypeQueryResult);
    echo "</select><br><br>\n";

    // here, on submit, we will check if entered competition name is unique etc.
    echo '<button type="submit" name="action" value="search_by_roundname">Create</button>';
    echo '<br><br>';

    // lists existing competitions in a table.
    // [to add]: submit button above refreshes page, thereby refreshing table w/ new
    if ($competitionListQueryResult->num_rows > 0) {
        echo "\n\n<h3>Existing Competitions:</h3><br>";
        echo "\n\t<table border='1'>\n\t\t<tr><th>Competition Name</th><th>Round Type</th></tr>\n";
        printCompetitionTableRow($competitionListQueryResult);
        echo "\t</table><br><br>\n";
    } else {
        // if no competitions were created previously OR ___
        echo '<br>';
        echo "<p>No competitions were found!</p>";
        echo '<br>';
    }

// prints a singular row for the table above. filling in competition name and
// the associated round type for that competition.
function printCompetitionTableRow($competitionListQueryResult) {
        while($row = $competitionListQueryResult->fetch_assoc()) {
            echo "\t\t<tr><td>{$row['CompetitionName']}</td><td>{$row['RoundName']}</td></tr>\n";
        }
}

// prints drop down values for the round-type dropdown on the page. cant reuse!
function printDropDownValues($roundTypeQueryResult) {
    if ($roundTypeQueryResult->num_rows > 0) {
        while($row = $roundTypeQueryResult->fetch_assoc()) {
            $r_name = $row['RoundName'];
            $t_arrows = $row['TotalArrows'];
            
            // each dropdown item is created here:
            echo "\t";
            echo '<option value="';
            echo $r_name;
            echo '" selected="selected">';
            echo $r_name . " - ". strval($t_arrows) . " arrows";
            echo '</option>';
            echo "\n";
        }
    } else {
        // pretty sure this should never happen
        echo "<br><p>Oh no, no types of rounds were found on our end!</p><br>";
    }
}

?>

<?php
include_once 'footer.inc';
?>
</body>
</html>