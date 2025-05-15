<?php

require_once 'setting.php';

$dbconn = mysqli_connect($host,$user,$pwd,$sql_db);

if(!$dbconn) {
    die("connection failed: " . mysqli_connect_error());
}
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

if ($_SERVER['REQUETS_METHOD'] === 'POST'){
    $action = $_POST['action'];

    switch($action) {
        case 'list_all':
            listAllRounds($dbconn);
            break;
        case 'search_by_roundname':
            $roundname = isset($_POST['roundname']) ? $_POST['roundname'] : '';
            listByJobRef($dbconn, $roundname);
            break;
    }
    mysqli_close($dbconn);
}

?>
</body>
</html>