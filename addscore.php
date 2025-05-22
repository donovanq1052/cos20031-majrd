<?php
	session_start();
	if (isset ($_POST["reset"])) {
		session_unset();
	}
	if (!isset ($_SESSION["validarcherround"])) {
		$_SESSION["validarcherround"] = false;
	}
	if (!isset ($_SESSION["archer"])) {
		$_SESSION["archer"] = "";
	}
	if (!isset ($_SESSION["round"])) {
		$_SESSION["round"] = "";
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="description" content="COS20031 Add Score Page" />
	<meta name="keywords" content="HTML, PHP, MYSQL" />
	<meta name="author" content="Aiden Large" />
	<title>Add Score | MAJRD Archery</title>
</head>
<body>
	<h2>Add Score</h2>
	<?php
		require_once ("functions/settings.php");
		$conn = @mysqli_connect($host, $user, $pswd)
			or die('Failed to connect to server');
		@mysqli_select_db($conn, $dbnm)
			or die('Database not available');

		if (isset($_POST["roundscore"])) {

		}
	
		$validarcher = false;
		$validround = false;
		if (isset($_POST["archerround"])) {
			if (isset($_POST["archer"])) {
				$archer = $_POST["archer"];
				$archerquery = "SELECT * FROM Archers WHERE ArcherId='$archer'";
				$archercheck = mysqli_query($conn, $archerquery);
				if (mysqli_num_rows($archercheck) != 0) {
					$validarcher = true;
				}
			}
			if (isset($_POST["round"])) {
				$round = $_POST["round"];
				$roundquery = "SELECT * FROM RoundTypes WHERE RoundName='$round'";
				$roundcheck = mysqli_query($conn, $roundquery);
				if (mysqli_num_rows($roundcheck) != 0) {
					$validround = true;
				}
			}
		}
		if ($validarcher==true && $validround==true) {
			$_SESSION["validarcherround"] = true;
			$_SESSION["archer"] = $archer;
			$_SESSION["round"] = $round;
		}

		if ($_SESSION["validarcherround"] == true) {
			$archer = $_SESSION["archer"];
			$round = $_SESSION["round"];
			echo
			'<form method="post" action="addscore.php">';
				echo
				"<p>Archer ID: $archer</p>
				<p>Round: $round</p>";
				$rangesquery = "SELECT * FROM RoundRanges WHERE RoundName='$round'";
				$ranges = mysqli_query($conn, $rangesquery);
				$range = mysqli_fetch_row($ranges);
				$rangenum = 1;
				while ($range) {
					echo "<h3>Range $rangenum:</h3>";
					echo "<p>Distance: $range[2]</p>";
					echo "<p>Target face: $range[3]</p>";
					echo "<p>Total arrows: $range[4]</p>";
					$totalends = $range[4] / 6;
					echo "<p>Number of ends: $totalends</p>";
					echo "<table>";
					for ($endnum = 1; $endnum <= $totalends; $endnum++) {
						echo "<tr>";
						echo "<th>End $endnum</th>";
						for ($arrownum = 1; $arrownum <= 6; $arrownum++) {
							echo "<td>
								<select name='range$rangenum-end$endnum-arrow$arrownum' id='range$rangenum-end$endnum-arrow$arrownum'>
									<option value='M'>M</option>
									<option value='1'>1</option>
									<option value='2'>2</option>
									<option value='3'>3</option>
									<option value='4'>4</option>
									<option value='5'>5</option>
									<option value='6'>6</option>
									<option value='7'>7</option>
									<option value='8'>8</option>
									<option value='9'>9</option>
									<option value='10'>10</option>
									<option value='X'>X</option>
								</select>
							</td>";
						}
						echo "</tr>";
					}
					echo "</table>";
					$rangenum += 1;
					$range = mysqli_fetch_row($ranges);
				}
				echo
				"<button type='submit' name='roundscore' value='Add Score'>Add Score</button>
				<button type='submit' name='reset' value='Abort Score'>Abort Score</button>";
			echo
			"</form>";
		} else {
			echo
			"<form method='post' action='addscore.php'>";
    			echo
				"<label for='archer'>Archer ID:</label>
    			<input type='text' name='archer' id='archer'>
				<br/>
				<label for='round'>Round:</label>
    			<select name='round' id='round'>";
					$roundsquery = "SELECT RoundName FROM RoundTypes";
					$roundnames = mysqli_query($conn, $roundsquery);
					$roundname = mysqli_fetch_row($roundnames);
					while ($roundname) {
						echo
						"<option value='$roundname[0]'>$roundname[0]</option>";
						$roundname = mysqli_fetch_row($roundnames);
					}
				echo
				"</select>
				<br/>
				<p>Competition:</p>
    			<button type='submit' name='archerround' value='Add Score'>Add Score</button>";
			echo
			"</form>";
			if (isset($_POST["archerround"]) && $validarcher==false) {
				echo"<p>Invalid archer ID</p>";
			}
			if (isset($_POST["archerround"]) && $validround==false) {
				echo"<p>Invalid round</p>";
			}
		}

		mysqli_close($conn);
	?>
</body>
</html>