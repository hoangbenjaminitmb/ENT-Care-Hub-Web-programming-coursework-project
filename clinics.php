<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinics | ENT Care Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="icon" type="image/png" href="entlogo.png">
    <link rel="stylesheet" href="clinics.css">
    <style>
        .introductionimage {
            background-image: url(clinicimage.png);
        }
    </style>
</head>
<body>
    <?php
        include 'database.php';
        include 'header.php';
        $countclinic = $conn->query("SELECT * FROM clinics");
        $totalclinics = $countclinic->num_rows;
    ?>
    <div class="introductionimage" style="background-position: 100%;">
        <div class="text">
            <h1>Our Clinics</h1>
            <p>Learn more about our comprehensive network of clinics</p>
        </div>
    </div>
    <div class="main-content">
        <div class="text" style="text-align: left;">
            <h2 class="subheadings">Overview</h2>
            <p>
                With our significant presence in the East Midlands, you will have no problem finding one of our nearest clinics. 
                We at ENT Care Hub have established a network of <?php echo $totalclinics?> clinics across the
                region, from Derbyshire to Leicestershire, each specialising in different areas of ENT, so that we can cater
                for the population.
            </p>
        </div>
        <div class="clinicinformationwrapper">
            <div id="clinicinfotab">
                <img src="clinicicon.png">
                <p>We have a network of <?php echo $totalclinics?> clinics established across the East Midlands region.</p>
            </div>
            <div id="clinicinfotab">
                <img src="specialisationicon.png">
                <p>Our clinics specialise in various ENT areas, and we plan on bringing them more specialities.</p>
            </div>
            <div id="clinicinfotab">
                <img src="cqclogo.png">
                <p>Our clinics have met the standards set out by the CareQuality Commission.</p>
            </div>
        </div>
        <?php
            $cliniclocations = [];
            $gettingcliniclocation = $conn->query("
                SELECT * FROM clinics
            ");
            if ($gettingcliniclocation -> num_rows > 0) {
                while ($row = $gettingcliniclocation -> fetch_assoc()) {
                    $cliniclocations[] = [
                        'clinicid' => $row['id'],
                        'clinicname' => $row['name'],
                        'cliniclatitude' => floatval($row['latitude']),
                        'cliniclongitude' => floatval($row['longitude'])
                    ];
                }
            } 
        ?>
        <div class="clinicslocations">
            <div style="text-align: left;">
                <div class="text" style="padding-bottom: 0;">
                    <h2 class="subheadings" >Find us</h2>
                    <p>We have established <?php echo $totalclinics?> clinics across the East Midlands. You can take a look at where they are located in.</p>
                </div>
                <div class="findusmap">
                    <div class="showmap"></div>
                </div>
            </div>
            <div class="clinicssection">
                <div class="filterclinicssection text" style="text-align: left;">
                    <div>
                        <h4 style="font-size: 22px;">Filter clinics by:</h4>
                    </div>
                    <div class="clinicfilters">
                        <div class="selectclinicfilter" style="margin: 0;">
                            <label for="disability">Disabled Accessibility</label>
                            <input id="disability" type="checkbox">
                        </div>
                        <div class="selectclinicfilter" style="margin: 0;">
                            <label for="carparking">Car Parking available</label>
                            <input id="carparking" type="checkbox">
                        </div>
                        <div class="selectclinicfilter">
                            <label for="areasofspeciality">Speciality</label>
                            <select id="areasofspeciality">
                                <option value="">All specialities</option>
                                <?php
                                    include "database.php";
                                    $sql = "SELECT * FROM specialities";
                                    $result = $conn->query($sql);
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row['speciality'] . "'>" . $row['speciality'] . "</option>";
                                        }
                                    } else {
                                        echo "<option value='' disabled>No options available</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <button class="resetfilterbutton">Reset filters</button>
                    </div>
                </div>
                <div class="text" style="padding-top: 0; padding-bottom: 0;">
                    <p style="text-align: left;">Currently showing <span id="countclinics"><?php echo $totalclinics?></span> of <?php echo $totalclinics?> clinics</p>
                </div>
                <div class="showingclinicssection">
                    <?php
                    $fetchclinicinformation = $conn->query("                        
                    SELECT clinics.*, GROUP_CONCAT(DISTINCT specialities.speciality ORDER BY specialities.speciality) AS specialities
                    FROM clinics
                    JOIN consultants ON clinics.id = consultants.clinic_id
                    JOIN specialities ON consultants.speciality_id = specialities.id
                    GROUP BY clinics.id
                    ");

                    if ($fetchclinicinformation && $fetchclinicinformation->num_rows > 0) {
                        while ($clinicinformation = $fetchclinicinformation->fetch_assoc()) {
                        $specialities = explode(",", $clinicinformation['specialities']);

                        $carparkingavailable = $clinicinformation['car_parking'] === 'Yes' ? "<span style='color:rgb(6, 180, 0)'> available</span>" : "<span style='color:rgb(255, 58, 58)'> not available</span>";
                        $carparkingavailableimage = $clinicinformation['car_parking'] === 'Yes' ? "yescarparking.png" : "noparking.png";
                        $disabledaccessavailable = $clinicinformation['disabled_access'] === 'Yes' ? "<span style='color:rgb(6, 180, 0)'> available</span>" : "<span style='color:rgb(255, 58, 58)'> not available</span>";
                        $disabledaccessavailableimage = $clinicinformation['disabled_access'] === 'Yes' ? "disabledavailable.png" : "disabledunavailable.png";
                        echo '
                        <div class="showingclinics" 
                            data-disabledaccess="'. $clinicinformation['disabled_access'] .'" 
                            data-carparking="'. $clinicinformation['car_parking'] .'"
                            data-specialities="'. htmlspecialchars(implode(",",$specialities)).'"
                        ><a href="clinicinfo.php?id='. $clinicinformation['id'] .'&name=' . $clinicinformation['name'] .'">
                            <div id="map-'.$clinicinformation['id'].'" class="clinicmap" style="width: 100%; height: 200px; object-fit: cover"></div>
                            <div class="clinicinformation" data-latitude='.$clinicinformation['latitude'].' data-longitude='.$clinicinformation['longitude'].'>
                                <div class="clinicinformationheader">
                                    <h3>'. htmlspecialchars($clinicinformation['name']).'</h3>
                                    <p id="showlearnmore">Learn more</p>
                                </div>
                                <p style="margin: 8px"><img src="locationicon.png"><span class="clinicaddress">Loading Address...</span></p>
                                <p style="margin: 8px"><img src="';echo $carparkingavailableimage; echo'">Car parking is<strong>'; echo $carparkingavailable; echo '</strong></p>
                                <p style="margin: 8px"><img src="'; echo $disabledaccessavailableimage; echo '">Disabled access is<strong>'; echo $disabledaccessavailable; echo '</strong></p>
                                <div class="showingspecialities">';
                                foreach($specialities as $speciality) {
                                    echo '<div class="specialitytype">
                                        <img src="'. htmlspecialchars($speciality).'.png">
                                        <p>'. htmlspecialchars($speciality).' </p>
                                    </div>'; 
                                }
                                echo '</div>
                            </div>
                        </div></a>';
                        }
                    } else {
                        echo '<h3>Unavailable</h3>';
                    }
                    ?>
                </div><br>
            </div>
        </div>
    </div>
    <?php
        include 'footer.php';
        $conn->close();
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        const locations = <?php echo json_encode($cliniclocations); ?>;
    </script>
    <script src="clinics.js"></script>
</body>
</html>