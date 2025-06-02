<?php
require_once 'functions/settings.php';
require_once 'functions/sanitise_input.php';
session_start();
$dbconn = mysqli_connect($host,$user,$pswd,$dbnm);
$input_correct = true;
$_SESSION['roundmsg'] = ""; // error message
//Check to see if roundname was inputted correctly before moving to the next step
if(!isset($_POST['roundname']) || $_POST['roundname'] === "")
{
    $_SESSION['roundmsg'] .= "<p>Round name must not be empty.</p>";
    $input_correct = false;
} else
{
    $query = "SELECT RoundName FROM RoundType";
    $result = $dbconn->query($query);
    while($row = $result->fetch_assoc()) {
        if(strtolower($row['RoundName']) === strtolower($_POST['roundname']))
        {
            $_SESSION['roundmsg'] .= "<p>Round name must be unique. Please enter a unique round name.";
            $input_correct = false;
        } else {
            $_SESSION['roundname'] = sanitise_input($_POST['roundname']);
        }
    }
}
// Check to see if ranges was inputted correctly before moving to the next step
if(!isset($_POST['ranges']))
{
    $_SESSION['roundmsg'] .= "<p>You must input a number of ranges.</p>";
    $input_correct = false;
} else
{
    if(!is_numeric($_POST['ranges']))
    {
        $_SESSION['roundmsg'] .= "<p>Ranges must be a number.</p>";
        $input_correct = false;
    }
    else
    {
        $ranges = intval($_POST['ranges']);
        if($ranges < 1 || $ranges > 7)
        {
            $_SESSION['roundmsg'] .= "<p>Ranges must be between 1 and 7</p>";
            $input_correct = false;
        } else
        {
            $_SESSION['ranges'] = $ranges;
        }
    }
}
if(!$input_correct)
{
    header("Location: roundadd.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Create your own custom round">
    <meta name="keywords" content="Archery, Database, Rounds, Search, Add">
    <meta name="author" content="Donovan Quilty">
    <title>Add Round | MAJRD Archery</title>
</head>
<body>
    
<?php
    include_once 'header.inc';
    if(isset($_SESSION['msg']))
    {
        echo $_SESSION['msg'];
        unset($_SESSION['msg']);
    }
?>

<h2>Enter Ranges for round</h2>
<form method="post" action="submitround.php">
<?php
    for($i = 1; $i <= $ranges; $i++)
    {
        echo "<h3>Enter values for range {$i}</h3>
        <p>Note: only 1 range per distance can be inputted. Total arrows must be divisible by 6.</p>
        <label for='distance{$i}'>Enter Distance</label>
        <select name='distance{$i}' id='distance{$i}' required>
            <option value=''>Please Select</option>
            <option value='20'>20m</option>
            <option value='30'>30m</option>
            <option value='40'>40m</option>
            <option value='50'>50m</option>
            <option value='60'>60m</option>
            <option value='70'>70m</option>
            <option value='90'>90m</option>
        </select><br><br>
        <input type='radio' name='targetface{$i}' id='targetface{$i}80' value='80' checked>
        <label for='targetface{$i}80'>80cm</label>
        <input type='radio' name='targetface{$i}' id='targetface{$i}120' value='120'>
        <label for='targetface{$i}120'>120cm</label><br><br>
        <label for='totalarrows{$i}'>Total Arrows</label>
        <input type='number' name='totalarrows{$i}' id='totalarrows{$i}' required><br><br>";
    }
?>
    <button type="submit" name="action">Press here to create round</button>

</form>

<?php
    include_once 'footer.inc';
?>
</body>
</html>
