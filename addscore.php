<?php
	session_start();
	if (isset ($_POST["reset"]) || isset ($_POST["roundscore"])) {
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
	$scoreadded = false;
	$validarcher = false;
	$validround = false;
	$validcompetition = true;	
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="description" content="COS20031 Add Score Page" />
	<meta name="keywords" content="HTML, PHP, MYSQL" />
	<meta name="author" content="Aiden Large" />
	<title>Add Score | MAJRD Archery</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" >
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" rel="stylesheet" >
</head>
<body>
	<header>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1>Add Score</h1>
                </div>
            </div>
        </div>
    </header>
	<?php
		require_once ("functions/settings.php");
		$conn = @mysqli_connect($host, $user, $pswd)
			or die('Failed to connect to server');
		@mysqli_select_db($conn, $dbnm)
			or die('Database not available');

		if (isset($_POST["roundscore"])) {
			$archer = $_POST["archer"];
			$round = $_POST["round"];
			$date = date("Y-m-d");
			$class = $_POST["class"];
			$division = $_POST["division"];
			$competition = $_POST["competition"];
			if ($competition === "") {
				$conn->query("INSERT INTO ShotRound (ArcherID, RoundName, Date, Division, Class, Verified) VALUES ('$archer', '$round', '$date', '$division', '$class', TRUE)");
			} else {
				$conn->query("INSERT INTO ShotRound (ArcherID, RoundName, Date, Division, Class, Verified, CompetitionName) VALUES ('$archer', '$round', '$date', '$division', '$class', TRUE, '$competition')");
			}
			$round_id = mysqli_insert_id($conn);
			$ranges = mysqli_query($conn, "SELECT * FROM RoundRange WHERE RoundName='$round'");
			$range = mysqli_fetch_row($ranges);
			$rangenum = 1;
			while ($range) {
				$conn->query("INSERT INTO ShotRange (ScoreID, RangeNum, RangeID) VALUES ($round_id, $rangenum, $range[0])");
				$totalends = $range[4] / 6;
				for ($endnum = 1; $endnum <= $totalends; $endnum++) {
					$endtotal = $_POST["$rangenum-$endnum"];
					$xtotal = $_POST["$rangenum-$endnum-x"];
					for ($arrownum = 1; $arrownum <= 6; $arrownum++) {
						$arrowscore[$arrownum] = $_POST["$rangenum-$endnum-$arrownum"];
					}
					$conn->query("INSERT INTO ShotEnd (ScoreID, RangeNum, EndNum, Arrow1, Arrow2, Arrow3, Arrow4, Arrow5, Arrow6, TotalEndScore, XTotal) 
						VALUES ($round_id, $rangenum, $endnum, '$arrowscore[1]', '$arrowscore[2]', '$arrowscore[3]', '$arrowscore[4]', '$arrowscore[5]', '$arrowscore[6]', $endtotal, $xtotal)");
				}
				$rangenum += 1;
				$range = mysqli_fetch_row($ranges);
			}
			$scoreadded = true;
		}
	
		if (isset($_POST["archerround"])) {
			if (isset($_POST["archer"])) {
				$archer = $_POST["archer"];
				$archercheck = mysqli_query($conn, "SELECT * FROM Archer WHERE ArcherId='$archer'");
				if (mysqli_num_rows($archercheck) != 0) {
					$validarcher = true;
				}
			}
			if (isset($_POST["round"])) {
				$round = $_POST["round"];
				$roundcheck = mysqli_query($conn, "SELECT * FROM RoundType WHERE RoundName='$round'");
				if (mysqli_num_rows($roundcheck) != 0) {
					$validround = true;
				}
			}
			if ($_POST["competition"] != "") {
				$competition = $_POST["competition"];
				$competitioncheck = mysqli_query($conn, "SELECT * FROM Competition WHERE CompetitionName='$competition'");
				if (mysqli_num_rows($competitioncheck) == 0) {
					$validcompetition = false;
				}
			}
		}
		if ($validarcher==true && $validround==true && $validcompetition==true) {
			$_SESSION["validarcherround"] = true;
			$_SESSION["archer"] = $_POST["archer"];
			$_SESSION["round"] = $_POST["round"];
			$_SESSION["class"] = $_POST["class"];
			$_SESSION["division"] = $_POST["division"];
			$_SESSION["competition"] = $_POST["competition"];
		}

		echo 
		"<section>";
			echo
        	"<div id='app' class='container'>";
				# Score entry page:
            	if ($_SESSION["validarcherround"] == true) {
					$archer = $_SESSION["archer"];
					$round = $_SESSION["round"];
					$class = $_SESSION["class"];
					$division = $_SESSION["division"];
					$competition = $_SESSION["competition"];
					$archerfname = mysqli_fetch_row(mysqli_query($conn, "SELECT FirstName FROM Archer WHERE ArcherID='$archer'"))[0];
					$archerlname = mysqli_fetch_row(mysqli_query($conn, "SELECT LastName FROM Archer WHERE ArcherID='$archer'"))[0];
					echo
					"<div class='row'>
						<div class='col-4'>
							<p>Archer: $archerfname $archerlname</p>
						</div>
						<div class='col-4'>
							<p>Round: $round</p>
						</div>
						<div class='col-4'>
							<p>Class: $class</p>
						</div>
					</div>
					<div class='row'>
						<div class='col-4'>
							<p>Archer ID: $archer</p>
						</div>
						<div class='col-4'>";
							if ($competition === "") {
								echo
								"<p>Competition: None</p>";
							} else {
								echo
								"<p>Competition: $competition</p>";
							}
						echo
						"</div>
						<div class='col-4'>
							<p>Division: $division</p>
						</div>
					</div>
					<form method='post' action='addscore.php'>
						<input type='hidden' name='archer' id='archer' value='$archer'>
						<input type='hidden' name='round' id='round' value='$round'>
						<input type='hidden' name='class' id='class' value='$class'>
						<input type='hidden' name='division' id='division' value='$division'>
						<input type='hidden' name='competition' id='competition' value='$competition'>";
						$ranges = mysqli_query($conn, "SELECT * FROM RoundRange WHERE RoundName='$round' ORDER BY 'Distance' DESC");
						$range = mysqli_fetch_row($ranges);
						$rangenum = 1;
						while ($range) {
							echo
							"<div class=form-group>
								<div class='row'>
									<div class='col-12 col-md-6'>
										<h3>Range $rangenum:</h3>
									</div>
								</div>
								<div class='row'>
									<div class='col-3'>
										<p>Distance: $range[2]m</p>
									</div>
									<div class='col-3'>
										<p>Target face: $range[3]cm</p>
									</div>
									<div class='col-3'>
										<p>Total arrows: $range[4]</p>
									</div>";
									$totalends = $range[4] / 6;
									echo
									"<div class='col-3'>
										<p>Number of ends: $totalends</p>
									</div>
								</div>
								<table class='table'>
									<thead>
										<tr>
											<th scope='col'>End</th>
											<th scope='col'>Scores</th>
											<th scope='col'>Total</th>
										</tr>
									</thead>
									<tbody>";
									for ($endnum = 1; $endnum <= $totalends; $endnum++) {
										echo 
										"<tr>
											<th>$endnum</th>
											<td>";
												for ($arrownum = 1; $arrownum <= 6; $arrownum++) {
													echo
													"<input type='hidden' v-model='r${rangenum}e${endnum}a${arrownum}score' name='$rangenum-$endnum-$arrownum' id='$rangenum-$endnum-$arrownum'>";
												}
												echo
												"<div class='row'>
													<div class='col-12 col-md-8'>
														<ul class='list-group list-group-horizontal'>";
														for ($arrownum = 1; $arrownum <= 6; $arrownum++) {
															echo
															"<li class='list-group-item flex-fill'>{{r${rangenum}e${endnum}a${arrownum}score}}</li>";
														}
														echo
														"</ul>
													</div>
													<div class='col-12 col-md-4'>
														<button type='button' class='btn btn-secondary btn-block float-right' @click='displayScoreInput = \"$rangenum-$endnum\"'>Edit Score</button>
													</div>";
												echo
												"</div>";
												for ($arrownum = 1; $arrownum <= 7; $arrownum++) {
													echo 
													"<div v-if='displayScoreInput === \"$rangenum-$endnum\" && r${rangenum}e${endnum}currentArrow == $arrownum'>
														<br />
														<div class='row'>
															<div class='col-4'>
																<button type='button' class='btn btn-primary btn-block' @click='insertArrowScore(\"X\", $rangenum, $endnum, $arrownum)' :disabled='disableScore(\"X\", $rangenum, $endnum, $arrownum)'>X</button>
															</div>
															<div class='col-4'>
																<button type='button' class='btn btn-primary btn-block' @click='insertArrowScore(\"10\", $rangenum, $endnum, $arrownum)' :disabled='disableScore(\"10\", $rangenum, $endnum, $arrownum)'>10</button>
															</div>
															<div class='col-4'>
																<button type='button' class='btn btn-primary btn-block' @click='insertArrowScore(\"9\", $rangenum, $endnum, $arrownum)' :disabled='disableScore(\"9\", $rangenum, $endnum, $arrownum)'>9</button>
															</div>
														</div>
														<br />
														<div class='row'>
															<div class='col-4'>
																<button type='button' class='btn btn-primary btn-block' @click='insertArrowScore(\"8\", $rangenum, $endnum, $arrownum)' :disabled='disableScore(\"8\", $rangenum, $endnum, $arrownum)'>8</button>
															</div>
															<div class='col-4'>
																<button type='button' class='btn btn-primary btn-block' @click='insertArrowScore(\"7\", $rangenum, $endnum, $arrownum)' :disabled='disableScore(\"7\", $rangenum, $endnum, $arrownum)'>7</button>
															</div>
															<div class='col-4'>
																<button type='button' class='btn btn-primary btn-block' @click='insertArrowScore(\"6\", $rangenum, $endnum, $arrownum)' :disabled='disableScore(\"6\", $rangenum, $endnum, $arrownum)'>6</button>
															</div>
														</div>
														<br />
														<div class='row'>
															<div class='col-4'>
																<button type='button' class='btn btn-primary btn-block' @click='insertArrowScore(\"5\", $rangenum, $endnum, $arrownum)' :disabled='disableScore(\"5\", $rangenum, $endnum, $arrownum)'>5</button>
															</div>
															<div class='col-4'>
																<button type='button' class='btn btn-primary btn-block' @click='insertArrowScore(\"4\", $rangenum, $endnum, $arrownum)' :disabled='disableScore(\"4\", $rangenum, $endnum, $arrownum)'>4</button>
															</div>
															<div class='col-4'>
																<button type='button' class='btn btn-primary btn-block' @click='insertArrowScore(\"3\", $rangenum, $endnum, $arrownum)' :disabled='disableScore(\"3\", $rangenum, $endnum, $arrownum)'>3</button>
															</div>
														</div>
														<br />
														<div class='row'>
															<div class='col-4'>
																<button type='button' class='btn btn-primary btn-block' @click='insertArrowScore(\"2\", $rangenum, $endnum, $arrownum)' :disabled='disableScore(\"2\", $rangenum, $endnum, $arrownum)'>2</button>
															</div>
															<div class='col-4'>
																<button type='button' class='btn btn-primary btn-block' @click='insertArrowScore(\"1\", $rangenum, $endnum, $arrownum)' :disabled='disableScore(\"1\", $rangenum, $endnum, $arrownum)'>1</button>
															</div>
															<div class='col-4'>
																<button type='button' class='btn btn-primary btn-block' @click='insertArrowScore(\"M\", $rangenum, $endnum, $arrownum)' :disabled='disableScore(\"M\", $rangenum, $endnum, $arrownum)'>M</button>
															</div>
														</div>
														<br />
														<div class='row justify-content-center'>
															<div class='col-4'>
																<button type='button' class='btn btn-danger btn-block' @click='deleteArrowScore($rangenum, $endnum, $arrownum)' :disabled='r${rangenum}e${endnum}currentArrow == 1'>Delete</button>
															</div>
														</div>
													</div>";
												}
											echo 
											"</td>
											<td>{{r${rangenum}e${endnum}total}}</td>
											<input type='hidden' v-model='r${rangenum}e${endnum}total' name='$rangenum-$endnum' id='$rangenum-$endnum'>
											<input type='hidden' v-model='r${rangenum}e${endnum}xtotal' name='$rangenum-$endnum-x' id='$rangenum-$endnum-x'>
										</tr>";
									}
									echo "</tbody>
								</table>
							</div>";
							$rangenum += 1;
							$range = mysqli_fetch_row($ranges);
						}
						echo
						"<div class='row'>
							<div class='col-12 col-md-6'>
								<button class='btn btn-success btn-block' type='submit' name='roundscore' value='Add Score' :disabled='validRoundEnds != totalRoundEnds'>Add Score</button>
							</div>
							<div class='col-12 col-md-6'>
								<button class='btn btn-danger btn-block' type='submit' name='reset' value='Abort Score'>Abort Score</button>
							</div>
						</div>
						<br />
					</form>";
				# Archer/Round entry page:
				} else {
					if ($scoreadded) {
						echo
						"<div class='alert alert-success' role='alert'>
  							Score successfully added to database
						</div>";
					}
					if (isset($_POST["archerround"]) && $validarcher==false) {
						echo
						"<div class='alert alert-danger' role='alert'>
  							Invalid archer ID
						</div>";
					}
					if (isset($_POST["archerround"]) && $validround==false) {
						echo
						"<div class='alert alert-danger' role='alert'>
  							Invalid round
						</div>";
					}
					if (isset($_POST["archerround"]) && isset($_POST["competition"]) && $validcompetition==false) {
						echo
						"<div class='alert alert-danger' role='alert'>
  							Invalid competition
						</div>";
					}
					echo
					"<form method='post' action='addscore.php'>
						<div class='form-row'>
							<div class='form-group col-12'>
								<label for='archer'>Archer ID:</label>
								<input type='text' class='form-control' name='archer' id='archer'>
							</div>
						</div>
						<div class='form-row'>
							<div class='form-group col-12'>
								<label for='round'>Round:</label>
								<select class='form-control' name='round' id='round'>";
									$roundnames = mysqli_query($conn, "SELECT RoundName FROM RoundType");
									$roundname = mysqli_fetch_row($roundnames);
									while ($roundname) {
										echo
										"<option value='$roundname[0]'>$roundname[0]</option>";
										$roundname = mysqli_fetch_row($roundnames);
									}
								echo
								"</select>
							</div>
						</div>
						<div class='form-row'>
							<div class='form-group col-12 col-md-6'>
								<label for='class'>Class:</label>
								<select class='form-control' name='class' id='class'>";
									$classnames = mysqli_query($conn, "SELECT ClassName FROM Class");
									$classname = mysqli_fetch_row($classnames);
									while ($classname) {
										echo
										"<option value='$classname[0]'>$classname[0]</option>";
										$classname = mysqli_fetch_row($classnames);
									}
								echo
								"</select>
							</div>
							<div class='form-group col-12 col-md-6'>
								<label for='division'>Division:</label>
								<select class='form-control' name='division' id='division'>
									<option value='Recurve'>Recurve</option>
									<option value='Compound'>Compound</option>
									<option value='Recurve Barebow'>Recurve Barebow</option>
									<option value='Compound Barebow'>Compound Barebow</option>
									<option value='Longbow'>Longbow</option>
								</select>
							</div>
						</div>
						<div class='form-row'>
							<div class='form-group col-12'>
								<label for='competition'>Competition (optional):</label>
								<input type='text' class='form-control' name='competition' id='competition'>
							</div>
						</div>
						<div class='form-row'>
							<div class='form-group col-12'>
								<button type='submit' class='btn btn-success btn-block' name='archerround' value='Add Score'>Add Score</button>
							</div>
						</div>
					</form>";
				}
			echo
			"</div>
		</section>";
	?>
	<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
    <script>
        new Vue({
            el: '#app',
            data: {
				<?php
					$totalRoundEnds = 0;
					$ranges = mysqli_query($conn, "SELECT * FROM RoundRange WHERE RoundName='$round'");
					$range = mysqli_fetch_row($ranges);
					$rangenum = 1;
					while ($range) {
						$totalends = $range[4] / 6;
						for ($endnum = 1; $endnum <= $totalends; $endnum++) {
							for ($arrownum = 1; $arrownum <= 6; $arrownum++) {
								echo 
								"r${rangenum}e${endnum}a${arrownum}score: '--',
								";
							}
							echo
							"r${rangenum}e${endnum}currentArrow: 1,
							r${rangenum}e${endnum}total: '--',
							r${rangenum}e${endnum}xtotal: 0,
							";
							$totalRoundEnds += 1;
						}
						$rangenum += 1;
						$range = mysqli_fetch_row($ranges);
					}
					echo "totalRoundEnds: $totalRoundEnds,"
				?>
				validRoundEnds: 0,
				displayScoreInput: '',
				scoreValues: ['M', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'X']
            },
			methods: {
				insertArrowScore(value, rangenum, endnum, arrownum) {
					if (this["r" + rangenum + "e" + endnum + "currentArrow"] == 6) {
						this["r" + rangenum + "e" + endnum + "a" + arrownum + "score"] = value,
						this["r" + rangenum + "e" + endnum + "currentArrow"] += 1,
						this["r" + rangenum + "e" + endnum + "total"] = 0,
						this["r" + rangenum + "e" + endnum + "xtotal"] = 0,
						this.updateEndTotal(rangenum, endnum),
						this.validRoundEnds += 1;
					} else {
						this["r" + rangenum + "e" + endnum + "a" + arrownum + "score"] = value,
						this["r" + rangenum + "e" + endnum + "currentArrow"] += 1
					}
				},
				deleteArrowScore(rangenum, endnum, arrownum) {
					if (this["r" + rangenum + "e" + endnum + "currentArrow"] == 7) {
						this["r" + rangenum + "e" + endnum + "a" + (arrownum - 1) + "score"] = '--',
						this["r" + rangenum + "e" + endnum + "currentArrow"] -= 1,
						this["r" + rangenum + "e" + endnum + "total"] = '--',
						this["r" + rangenum + "e" + endnum + "xtotal"] = 0,
						this.validRoundEnds -= 1;
					} else {
						this["r" + rangenum + "e" + endnum + "a" + (arrownum - 1) + "score"] = '--',
						this["r" + rangenum + "e" + endnum + "currentArrow"] -= 1
					}
				},
				disableScore(value, rangenum, endnum, arrownum) {
					if (this["r" + rangenum + "e" + endnum + "currentArrow"] == 1) {
						return false
					} else if (this["r" + rangenum + "e" + endnum + "currentArrow"] <= 6) {
						if (this.scoreValues.indexOf(value) <= this.scoreValues.indexOf(this["r" + rangenum + "e" + endnum + "a" + (arrownum - 1) + "score"])) {
							return false
						} else {
							return true
						}
					} else {
						return true
					}
				},
				updateEndTotal(rangenum, endnum) {
					for (let arrow = 1; arrow <= 6; arrow++) {
  						if (this["r" + rangenum + "e" + endnum + "a" + arrow + "score"] === "X") {
							this["r" + rangenum + "e" + endnum + "total"] += 10,
							this["r" + rangenum + "e" + endnum + "xtotal"] += 1
						} else {
							this["r" + rangenum + "e" + endnum + "total"] += this.scoreValues.indexOf(this["r" + rangenum + "e" + endnum + "a" + arrow + "score"])
						}
					}
				}
			}
        })
    </script>
	<?php
		mysqli_close($conn);
	?>
</body>
</html>