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

// connect to the database
$dbconn = mysqli_connect($host,$user,$pswd,$dbnm);
if(!$dbconn) {
    die("connection failed: " . mysqli_connect_error());
}

// query that gets existing list of archers and their details
$archerListQuery = "SELECT * FROM Archer ORDER BY LastName";
$stmtb = $dbconn->prepare($archerListQuery);
$stmtb->execute();
$archerListQueryResult = $stmtb->get_result();

// query to check if archer ID is getting duplicated [REMOVE AFTER DB UPDATE]
$stmtc = $dbconn->prepare($archerListQuery);
$stmtc->execute();
$archerIDListQueryResult = $stmtc->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View/Add Archers here">
    <meta name="keywords" content="Archery, Database, Archers, View, Add">
    <meta name="author" content="Donovan Quilty, Reeve Kariyawasam">
    <link rel="stylesheet" href="styles.css">
    <title>Manage Archers | MAJRD Archery</title>
</head>
<body>

<?php
    include_once 'header.inc';
?>
<main>

    <h2>Manage Archers</h2>
    <form method="post"><br>
        <h3>Add a new archer:</h3><br>

        <label for="archer-id">Archer ID: </label>
        <input type="text" name="archer-id" placeholder="Enter unique 11 digit ID" 
        pattern="^[0-9]*$" minlength="11" maxlength="11" required><br>
        <h6>To remove. DB structure needs slight update first.</h6><br>

        <label for="fname">First Name: </label>
        <input type="text" name="fname" placeholder="Enter your first given name"
            required><br>
        <label for="lname">Last Name: </label>
        <input type="text" name="lname" placeholder="Enter surname" required><br>

        <!--  Values allowed in four-digit format: 1901 to 2155, and 0000. -->
        <label for="birth-year">Birth Year: </label>
        <input type="text" name="birth-year" minlength="4" maxlength="4"
            pattern="^[0-9]*$" placeholder="YYYY" required><br>
        <label for="gender">Gender: </label>
        <select name="gender" required>
            <option value="">Please Select</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select><br><br>
        

        <?php
        // we show an error message if this page has been redirected to after a
        // failed attempt to add an archer
        if ($err_state == true && isset($_SESSION['archer-err-msg'])) {
            echo "<h6>" . $_SESSION['archer-err-msg'] . "</h6><br>";
        } else if ($err_state == true && !(isset($_SESSION['archer-err-msg']))) {
            echo "<h6>Oh no! Something went wrong!</h6>'";
        }
        ?>
        
        <button type="submit" name="add_archer_button">Add</button><br><br>
    </form>

    <?php
        // SUBMIT LOGIC (try to make this happen only if 'Create' button is pressed):
        if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
            isset($_POST['add_archer_button'])) {
            $archer_id = sanitise_input($_POST['archer-id']);
            $fname = sanitise_input($_POST['fname']);
            $lname = sanitise_input($_POST['lname']);
            $birth_year = sanitise_input($_POST['birth-year']);
            $gender = sanitise_input($_POST['gender']);

            // check if archer-id entered is not a duplicate
            while($query = $archerIDListQueryResult->fetch_assoc()) {
                if ($_POST['archer-id'] == $query['ArcherID']) {
                    // we set sesh var w/ dupl. comp name to show user on refresh
                    $_SESSION['archer-err-msg'] = "That ID already exists. Try again.";
                    $_SESSION['err_state'] = true;

                    // and we redirect
                    header('Location: managearchers.php');   // and we refresh
                    exit;
                }
            }

            // check if birth-year entered makes sense
            $year = (int)$birth_year;
            if (((int)date("Y") - $year) <= 5 || ((int)date("Y") - $year) >= 150) {
                // we set sesh var w/ age error to show user on refresh
                $_SESSION['archer-err-msg'] = "You're either too young or too old.";
                $_SESSION['err_state'] = true;

                // and we redirect
                header('Location: managearchers.php');   // and we refresh
                exit;
            }

            if ($fname != "" && $lname != "" && $gender != "") {
                $insert_archer_entry_query = "INSERT INTO archer (
                    ArcherID,
                    FirstName,
                    LastName,
                    BirthYear,
                    Gender
                    )
                    VALUES(
                        '$archer_id',
                        '$fname',
                        '$lname',
                        '$birth_year',
                        '$gender'
                    )";
                // and in to the db it goes!
                $result = mysqli_query($dbconn, $insert_archer_entry_query);

                // refresh the page and the table should update!?!?!
                if (!$result) {
                    $_SESSION['err_state'] = true;
                } else {
                    header('Location: managearchers.php');   // and we refresh
                }
            }
        }


        // TABLE BIT
        // lists existing archers in a table. doing it in php block entirely
        // [to add]: submit button above refreshes page, thereby refreshing table
        if ($archerListQueryResult->num_rows > 0) {
            echo "\n\n<h3>Existing Archers:</h3>";
            echo "\n\n<h6>Sorted by Last Name</h6>";
            echo "\n\t<table border='1'>\n\t\t<tr>";
            echo "<th>ID</th><th>First Name</th><th>Last Name</th>";
            echo "<th>BirthYear</th><th>Gender</th>";
            echo "</tr>\n";
            printArcherTableRow($archerListQueryResult);
            echo "\t</table><br><br>\n";
        } else {
            // if no archers exist already
            echo '<br>';
            echo "<h6>No archers were found! Is this a new club?</h6>";
            echo '<br>';
        }

        mysqli_close($dbconn);

    // prints a singular row for the table above.
    function printArcherTableRow($archListQueryResult) {
            while($row = $archListQueryResult->fetch_assoc()) {
                echo "\t\t<tr>";
                echo "<td>{$row['ArcherID']}</td>";
                echo "<td>{$row['FirstName']}</td>";
                echo "<td>{$row['LastName']}</td>";
                echo "<td>{$row['BirthYear']}</td>";
                echo "<td>{$row['Gender']}</td>";
                echo "</tr>\n";
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
</main>

</body>
</html>