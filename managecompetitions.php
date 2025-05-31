<?php
require_once 'functions/settings.php';
require_once 'functions/sanitise_input.php';
session_start();    // don't know where to end this rip

// NOTE: $err_state simply tracks whether the current session has been
// redirected to, by itself, due to an error. If so, we show the error and
// unset $err_state.
if (isset($_SESSION['err_state'])) {
    if ($_SESSION['err_state'] == true) {
        $err_state = true;
    } else {
        $err_state = false;
    }
} else {
    $err_state = false;
}

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
    <title>Manage Competitions | MAJRD Archery</title>
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

    <?php
    // we show an error message if this page has been redirected to after a
    // failed attempt to create a competition (if the entered competition name
    // already existed)
    if ($err_state == true && isset($_SESSION['duplicate-competition-name'])) {
        echo "<h6>A competition with the name '" . $_SESSION['duplicate-competition-name'] . "' already exists</h6><br>";
    } else if ($err_state == true && !(isset($_SESSION['duplicate-competition-name']))) {
        echo "<h6>Oh no! Something went wrong!</h6>'";
    }
    ?>
    
    <button type="submit" name="create_button">Create</button><br><br>
</form>

<?php
    // SUBMIT LOGIC (try to make this happen only if 'Create' button is pressed):
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_button'])) {
        $comp_name = sanitise_input($_POST['competition-name']);
        $round_type_name = sanitise_input($_POST['round-type']);

        // loop through the existing competition names and see if the entered
        // name already exists. if so, refresh with $err_state set
        while($query = $competitionListQueryResultCheck->fetch_assoc()) {
            if ($_POST['competition-name'] == $query['CompetitionName']) {
                // we set sesh var w/ dupl. comp name to show user on refresh
                $_SESSION['duplicate-competition-name'] = $_POST['competition-name'];
                $_SESSION['err_state'] = true;

                // and we redirect
                header('Location: managecompetitions.php');   // and we refresh
                exit;
            }
        }

        // if no duplicates are found, and if both competition-name and
        // round-names are set, then we build a query and try push to db
        if ($_POST['competition-name'] != "" && $_POST['round-type'] != "") {
            $insert_competition_entry_query = "INSERT INTO competition (
                CompetitionName,
                RoundName
                )
                VALUES(
                    '$comp_name',
                    '$round_type_name'
                )";

            // and in to the db it goes!
            $result = mysqli_query($dbconn, $insert_competition_entry_query);

            // refresh the page and the table should update!?!?!
            if (!$result) {
                $_SESSION['err_state'] = true;
            } else {
                header('Location: managecompetitions.php');   // and we refresh
            }
        }
    }

    // TABLE BIT
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
            echo '<option value="' . $r_name . '">';
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
// we unset the $err_state if we successfully reach the bottom of the document
$_SESSION['err_state'] = false;
$err_state = false;
?>

<?php
include_once 'footer.inc';
?>
</body>
</html>