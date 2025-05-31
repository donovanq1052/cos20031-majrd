<?php
require_once 'functions/settings.php';
$dbconn = mysqli_connect($host,$user,$pswd,$dbnm);

if(!$dbconn) {
    die("connection failed: " . mysqli_connect_error());
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

        <hr>

        <?php
            include_once 'footer.inc'; 
        ?>
        
        <script>
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