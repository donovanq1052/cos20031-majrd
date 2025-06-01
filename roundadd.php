<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Create your own custom round">
    <meta name="keywords" content="Archery, Database, Rounds, Search, Add">
    <meta name="author" content="Donovan Quilty">
    <link rel="stylesheet" href="styles.css">
    <title>Add Round | MAJRD Archery</title>
</head>
<body>
    <?php
        include_once 'header.inc';
        if(isset($_SESSION['roundmsg']))
        {
            echo $_SESSION['roundmsg'];
            unset($_SESSION['roundmsg']);
        }
        if(isset($_SESSION['added']))
        {
            echo $_SESSION['added'];
            unset($_SESSION['added']);
        }
    ?>
    <main>
        <h2>Add a round</h2>
        <form method="post" action="rangesadd.php">

            <label for="roundname">Enter round name. Round name must be unique</label>
            <input type="text" name="roundname" id="roundname" placeholder="Enter Round Name">
            <br><br>
            <label for="ranges">How many ranges will be in this round?</label>
            <input type="number" name="ranges" id="ranges" min="1" max="7">
            <br><br>
            <button type="submit" name="action">Press here to input the ranges</button>
            <br><br>

        </form>

        <?php
            include_once 'footer.inc';
        ?>
    </main>
</body>
</html>