<?php
require_once 'functions/settings.php';
// connecting to db
$dbconn = mysqli_connect($host,$user,$pswd,$dbnm);

// catching connection error
if(!$dbconn) {
    die("connection failed: " . mysqli_connect_error());
}

$search_results_html = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_submit'])) { // checking form submitted
    $search_type = $_POST['search_type']; // retrieving radio button value (archerID/competitionName)
    $sql = "";
    $params = [];
    $param_types = "";

    // form processing logic
    if ($search_type == 'archer') {
        $archer_id = trim($_POST['archer_id_val']);
        if (!empty($archer_id)) {
            $sql = "SELECT sr.ScoreID, sr.Date, sr.RoundName, sr.Division, sr.Class, sr.CompetitionName, a.FirstName, a.LastName
                    FROM ShotRound sr
                    JOIN Archer a ON sr.ArcherID = a.ArcherID
                    WHERE sr.ArcherID = ?";
            $params[] = $archer_id;
            $param_types .= "s";
        } else {
            $error_message = "Please enter an Archer ID.";
        }
    } elseif ($search_type == 'competition') {
        $competition_name = trim($_POST['competition_name_val']);
        if (!empty($competition_name)) {
            $sql = "SELECT sr.ScoreID, sr.Date, sr.RoundName, sr.Division, sr.Class, sr.CompetitionName, a.FirstName, a.LastName
                    FROM ShotRound sr
                    JOIN Archer a ON sr.ArcherID = a.ArcherID
                    WHERE sr.CompetitionName = ?";
            $params[] = $competition_name;
            $param_types .= "s"; // 's' for string
        } else {
            $error_message = "Please enter a competition name.";
        }
    } else {
        $error_message = "Invalid search type selected.";
    }
    if (!empty($sql) && empty($error_message)) {
        $stmt = mysqli_prepare($dbconn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $param_types, ...$params); // Using splat operator for params

            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result) {
                if (mysqli_num_rows($result) > 0) {
                    $search_results_html .= "<h3>Search Results:</h3><table border='1'>
                                            <thead>
                                                <tr>
                                                    <th>Archer Name</th>
                                                    <th>Date</th>
                                                    <th>Score</th>
                                                    <th>Round Name</th>
                                                    <th>Division</th>
                                                    <th>Competition</th>
                                                </tr>
                                            </thead>
                                            <tbody>";
                    while ($row = mysqli_fetch_assoc($result)) {
                        $search_results_html .= "<tr>
                                                    <td>" . htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) . "</td>
                                                    <td>" . htmlspecialchars($row['Date']) . "</td>
                                                    <td>" . htmlspecialchars($row['ScoreID']) . "</td>
                                                    <td>" . htmlspecialchars($row['RoundName']) . "</td>
                                                    <td>" . htmlspecialchars($row['Division']) . "</td>
                                                    <td>" . htmlspecialchars($row['CompetitionName'] ?? 'N/A') . "</td>
                                                 </tr>";
                    }
                    $search_results_html .= "</tbody></table>";
                } else {
                    $search_results_html = "<p>No scores found matching your criteria. Perhaps scores hasn't been updated yet? </p>";
                }
            } else {
                $error_message = "Error retrieving results: " . mysqli_error($dbconn);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_message = "Error preparing statement: " . mysqli_error($dbconn);
        }
    }    

}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="Matthew Xu">
        <link rel="stylesheet" href="styles.css">
        <title>View Scores | MAJRD Archery</title>
    </head>
    <body>
        <?php
            include_once 'header.inc';
        ?>
        <main>
            <h2>View Scores</h2>
            <form method="POST">
                <div>
                    <input type="radio" id="search_type_archer" name="search_type" value="archer" checked>
                    <label for="search_type_archer">By Archer ID</label>

                    <input type="radio" id="search_type_competition" name="search_type" value="competition">
                    <label for="search_type_competition">By Competition Name</label>
                </div>

                <div id="archer_input_section">
                    <label for="archer_id_val">Archer ID:</label>
                    <input type="text" id="archer_id_val" name="archer_id_val" placeholder="Enter Archer ID">
                </div>

                <div id="competition_input_section" style="display:none;">
                    <label for="competition_name_val">Competition Name:</label>
                    <input type="text" id="competition_name_val" name="competition_name_val" placeholder="Enter competition name">
                </div>

                <div>
                    <button type="submit" name="search_submit">Search Scores</button>
                </div>
            </form>
            </form>
            <?php
                // Display the search results HTML if it's been populated
                if (!empty($search_results_html)) {
                    echo $search_results_html;
                }
            ?>
            <?php
                include_once 'footer.inc'; 
            ?>
        </main>
        
        <script>
            // hides elements based on radio buttons selected 
            document.addEventListener('DOMContentLoaded', function () {
                const searchTypeRadios = document.getElementsByName('search_type');
                const archerSection = document.getElementById('archer_input_section');
                const competitionSection = document.getElementById('competition_input_section');

                const archerInput = document.getElementById('archer_id_val');
                const competitionInput = document.getElementById('competition_name_val');

                function toggleSections() {
                    const selectedType = document.querySelector('input[name="search_type"]:checked').value;
        
                    archerSection.style.display = 'none';
                    competitionSection.style.display = 'none';

                    if (selectedType === 'archer') {
                        archerSection.style.display = 'block';
                    } else if (selectedType === 'competition') {
                        competitionSection.style.display = 'block';
                    }
                }

                searchTypeRadios.forEach(function (radio) {
                    radio.addEventListener('change', toggleSections);
                });
                toggleSections(); 
            });
        </script>
    </body>
</html>