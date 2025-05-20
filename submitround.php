<?php
require_once 'settings.php';
require_once 'sanitise_input.php';
session_start();
$dbconn = mysqli_connect($host,$user,$pswd,$dbnm);
$input_correct = true;
$_SESSION['msg'] = ""; // error message
$distancearray = [];
$targetfacearray = [];
$totalarrowsarray = [];
if(isset($_SESSION['ranges']) && isset($_SESSION['roundname']))
{
    $ranges = $_SESSION['ranges'];
    $roundname = $_SESSION['roundname'];
} else
{
    $_SESSION['msg'] = "<p>Ranges can not be added without a round name and number of ranges set</p>";
    header("Location: rangesadd.php");
    exit();
}
for($i = 1; $i <= $ranges; $i++)
{
    $distancestr = "distance{$i}";
    $targetfacestr = "targetface{$i}";
    $totalarrowsstr = "totalarrows{$i}";
    if(isset($_POST[$distancestr]))
    {
        $distancearray[$i] = sanitise_input($_POST[$distancestr]);
    } else
    {
        $_SESSION['msg'] .= "<p>Distance must be specified for all ranges.</p>";
        $input_correct = false;
        break;
    }
    if(isset($_POST[$targetfacestr]))
    {
        $targetfacearray[$i] = sanitise_input($_POST[$targetfacestr]);
    } else
    {
        $_SESSION['msg'] .= "<p>Target face must be specified for all ranges.</p>";
        $input_correct = false;
        break;
    }
    if(isset($_POST[$totalarrowsstr]))
    {
        if(!is_numeric($_POST[$totalarrowsstr]) || 
        intval($_POST[$totalarrowsstr]) > 999 ||
        intval($_POST[$totalarrowsstr]) < 6 ||
        intval($_POST[$totalarrowsstr]) % 6 !== 0)
        {
            $_SESSION['msg'] .= "<p>Total arrows must be a number less than 999, more than 5, and divisible by 6</p>";
            $input_correct = false;
            break;
        } else
        {
            $totalarrowsarray[$i] = intval(sanitise_input($_POST[$totalarrowsstr]));   
        }
    } else
    {
        $_SESSION['msg'] .= "<p>Total arrows must be specified for all ranges.</p>";
        $input_correct = false;
        break;
    }
}
if(!$input_correct)
{
    header("Location: rangesadd.php");
    exit();
}
if(count($distancearray) !== count(array_unique($distancearray)))
{
    $_SESSION['msg'] .= "<p>Distances must be unique for each range</p>";
    $input_correct = false;
    header("Location: rangesadd.php");
    exit();
}
$total_arrows = array_sum($totalarrowsarray);
$max_score = $total_arrows * 10;
mysqli_autocommit($dbconn, false);
$all_good = true;
$roundname_escaped = mysqli_real_escape_string($dbconn, $roundname);
try{
    $querybase = "INSERT INTO RoundTypes VALUES ('$roundname_escaped', $total_arrows, $max_score)";
    if(!mysqli_query($dbconn, $querybase)) {
        $all_good = false;
    }
    $query = [];
    for($i = 1; $i <= $ranges; $i++)
    {
        $targetface_escaped = mysqli_real_escape_string($dbconn, $targetfacearray[$i]);
        $query[$i] = "INSERT INTO RoundRanges (RoundName, TargetFace, TotalArrows, Distance)
        VALUES ('$roundname_escaped', '$targetface_escaped', {$totalarrowsarray[$i]}, {$distancearray[$i]})";

        if(!mysqli_query($dbconn, $query[$i])) {
            $all_good = false;
            break;
        }
    }
    if($all_good) {
        mysqli_commit($dbconn);
        unset($_SESSION['roundmsg']);
        $_SESSION['added'] = "<p>Round successfully added!</p>";
    } else {
        mysqli_rollback($dbconn);
        $_SESSION['msg'] = "<p>Transaction failed. All changes rolled back. Round not successfully added.</p>";
        header("Location: rangesadd.php");
        exit();
    }
} catch(mysqli_sql_exception $e) {
    mysqli_rollback($dbconn);
    $_SESSION['msg'] = "<p>Error: {$e->getMessage()}</p>";
    header("Location: rangesadd.php");
    exit();
} finally {
    mysqli_autocommit($dbconn, true);
    mysqli_close($dbconn);
}
unset($_SESSION['roundname']);
unset($_SESSION['ranges']);
header("Location: roundadd.php");
exit();