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

// save another copy for checking against entered competition name
$stmtC = $dbconn->prepare($competitionListQuery);
$stmtC->execute();
$competitionListQueryResultCheck = $stmtC->get_result();

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
<form method="post"><br>
    <h3>Add a new competition:</h3><br>
    <label for="competition-name">Name: </label>
    <input type="text" name="competition-name" placeholder="Enter competition name"><br>
    <label for="round-type">Round Type: </label>
    <select name="round-type">
    <?php   // creates dropdown menu containing types of rounds
    echo "\t" . '<option value="">Please select</option>' . "\n";
    printDropDownValues($roundTypeQueryResult);
    ?>
    </select><br><br>
    
    <button type="submit" name="action" value="create_competition">Create</button><br><br>
</form>

<?php
    // the submit logic:
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        // print out the competition name entered:
        echo "entered name: " . $_POST['competition-name'] . "<br><br>\n";

        while($query = $competitionListQueryResultCheck->fetch_assoc()) {
            if ($_POST['competition-name'] == $query['CompetitionName']) {
                echo "similar comp exists already<br>";
            }
            //echo "\t\t<tr><td>{$query['CompetitionName']}</td></tr><br>\n";
        }

        /*
        echo "POSTING NOW";
        header('Location: viewcompetitions.php');
        exit;
        */
    }


    // lists existing competitions in a table. doing it in php block entirely
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

    mysqli_close($dbconn);

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
            echo "\t\t";
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