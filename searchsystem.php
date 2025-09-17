<link rel="stylesheet" href="searchsystem.css">

<div class="search">
    <h2 class="text" style="font-size: 25px; text-align: left; padding-top: 20px;padding-left: 0; font-weight: bold;">Find a Consultant Near You</h2>
    <div class="searchinputcontainers">
        <div class="inputgroup">
            <label for="enterlocale">Enter your postcode or town/city</label>
            <input type="text" id="enterlocale" placeholder="E.g. LE11 3TU" value="<?php echo isset($_GET['locale']) ? htmlspecialchars($_GET['locale']) : ''; ?>">
        </div>
        <div class="inputgroup">
            <label for="selectdate">Select a date</label>
            <input type="date" id="selectdate" placeholder="E.g. 24/04/2025" value="<?php echo isset($_GET['dates']) ? htmlspecialchars($_GET['dates']) : ''; ?>">
        </div>
        <div style="flex: 1;">
            <label for="choosespeciality">Area of Speciality/Concern</label>
            <select id="specialities">
                <option value="" disabled <?php if (!isset($_GET['speciality']) || $_GET['speciality'] == '') echo 'selected'; ?> hidden>Choose an option...</option>
                <?php
                    include "database.php";
                    $result = $conn->query("SELECT * FROM specialities");
                    if ($result->num_rows > 0) {
                        $specialityselected = isset($_GET['speciality']) ? $_GET['speciality'] : '';
                        while ($row = $result->fetch_assoc()) {
                            $speciality = $row['speciality'];
                            $selected = ($speciality == $specialityselected) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($speciality) . "' $selected>" . htmlspecialchars($speciality) . "</option>";
                        }
                    } else {
                        echo "<option value='' disabled>No options available</option>";
                    }
                ?>
            </select>
        </div>
        <button id="searchbutton">Search</button>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="searchsystem.js"></script>