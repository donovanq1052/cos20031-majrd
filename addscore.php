<?php
	session_start();
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
	
		$validarcher = true;
		$validround = true;	
		
		if (isset($_POST["archerround"])) {
			if (isset($_POST["archer"])) {
				$archer = $_POST["archer"];
				$archerquery = "SELECT * FROM Archers WHERE ArcherId='$archer'";
				$archercheck = mysqli_query($conn, $archerquery);
				if (mysqli_num_rows($archercheck) == 0) {
					$validarcher = false;
				}
			} else {
				$validarcher = false;
			}
			if (isset($_POST["round"])) {
				$round = $_POST["round"];
				$roundquery = "SELECT * FROM RoundTypes WHERE RoundName='$round'";
				$roundcheck = mysqli_query($conn, $roundquery);
				if (mysqli_num_rows($roundcheck) == 0) {
					$validround = false;
				}
			} else {
				$validround = false;
			}
		}

		if (isset($_POST["archerround"]) && $validarcher==true && $validround==true) {
			echo
			"<p>Archer ID: $archer</p>
			<p>Round: $round</p>";
			$rangesquery = "SELECT * FROM RoundRanges WHERE RoundName='$round'";
			$ranges = mysqli_query($conn, $rangesquery);
			$range = mysqli_fetch_row($ranges);
			$rangenum = 1;
			while ($range) {
				echo "<h3>Range $rangenum:</h3>";
				echo "<p>Distance: $range[4]</p>";
				echo "<p>Target face: $range[2]</p>";
				echo "<p>Total arrows: $range[3]</p>";
				$totalends = $range[3] / 6;
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
		} else {
			echo
			'<form method="post" action="addscore.php">
    			<label for="archer">Archer ID:</label>
    			<input type="text" name="archer" id="archer">
				<br/>
				<label for="round">Round:</label>
    			<input type="text" name="round" id="round">
				<br/>
				<p>Competition:</p>
    			<button type="submit" name="archerround" value="Add Score">Add Score</button>
			</form>';
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