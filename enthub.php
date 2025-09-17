<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | ENT Care Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="icon" type="image/png" href="entlogo.png">
    <link rel="stylesheet" href="enthub.css">
    <style>
        .introductionimage {
            background-image: url(homepageimage1.jpg);
        }
    </style>
</head>
<body>
    <?php
        include 'header.php';
    ?>
    <div class="introductionimage">
        <div class="text">
            <h1>Welcome to ENT Care Hub</h1>
            <p>Shaping healthcare in the East Midlands since 1921</p>
        </div>
    </div>
    <?php 
        include 'searchsystem.php';
    ?>
    <div class="main-content">
        <div class="text" style="text-align: left; padding-bottom: 0;">
            <h3>Explore</h3>
            <p>Learn more about our clinics, consultants, and who we are</p>
        </div>
        <div class="explore-wrapper d-flex align-items-center justify-content-center gap-2">
            <button class="scrollbuttons leftbutton">&#10094;</button>
            <div id="exploretabs" class="d-flex flex-nowrap gap-3 px-4">
                <a href="clinics.php" class="btn explore-button d-flex align-items-center text-decoration-none">
                    <img src="clinicimage.png" alt="Clinic Icon" class="icon">
                    <div class="text-start">
                        <h2 class="h5 mb-1">Our Clinics</h2>
                        <p class="mb-0">Planning a visit to us? Take a look at which clinic is the nearest to your location.</p>
                    </div>
                </a>
                <a href="consultants.php" class="btn explore-button d-flex align-items-center text-decoration-none">
                    <img src="consultants.jpg" alt="Consultant Icon" class="icon">
                    <div class="text-start">
                        <h2 class="h5 mb-1">Meet our Consultants</h2>
                        <p class="mb-0">Learn more about our diverse team of highly skilled consultants at ENT Care Hub.</p>
                    </div>
                </a>
                <a href="about.php" class="btn explore-button d-flex align-items-center text-decoration-none">
                    <img src="aboutusimage.jpg" alt="Search Icon" class="icon">
                    <div class="text-start">
                        <h2 class="h5 mb-1">About us</h2>
                        <p class="mb-0">Read about who we are and how we operate, as well as why you should choose us.</p>
                    </div>
                </a>
            </div>
            <button class="scrollbuttons rightbutton">&#10095;</button>
        </div>
        <div style="background-color: rgb(230, 230, 230); padding-bottom: 10px">
            <div class="text" style="text-align: left; padding-bottom: 0;">
            <h3>Featured Consultants</h3>
            <p>1 Featured consultant from each speciality. Refreshes when you visit this page</p>
            </div>
            <?php
            $result = $conn->query("
            WITH ranked_consultants AS (
                SELECT id, speciality_id, ROW_NUMBER() OVER ( PARTITION BY speciality_id ORDER BY RAND() ) AS specialty_rank
                FROM consultants
            )

            SELECT consultants.id, consultants.name AS consultant_name, consultants.consultation_fee, clinics.name AS clinic_name, specialities.speciality,
                AVG(reviews.score) AS average_score
            FROM ranked_consultants
            JOIN consultants ON ranked_consultants.id = consultants.id
            JOIN clinics ON clinics.id = consultants.clinic_id
            JOIN specialities ON specialities.id = consultants.speciality_id
            LEFT JOIN reviews ON consultants.id = reviews.consultant_id
            WHERE ranked_consultants.specialty_rank = 1 
            GROUP BY consultants.id, consultants.name, consultants.consultation_fee, clinics.name, specialities.speciality
            ORDER BY RAND();
            ");

            if ($result && $result->num_rows > 0) {
                echo '<div class="featuredconsultantswrapper">
                        <button class="scrollbuttons leftbutton2">&#10094;</button>
                        <div class="featuredconsultantsinfo">';
                while ($row = $result->fetch_assoc()) {
                    echo '
                        <div class="featuredconsultantcard">
                            <div style="display: flex; gap: 5px; justify-content: end">
                                <h3 id="ratingscore">★ '. round($row['average_score'], 2) .'/5</h3>
                                <h3 id="fees">£' . htmlspecialchars($row["consultation_fee"]) . '</h3>
                            </div>
                            <div class="consultantinfo">
                                <h3 style="color: white;">' . htmlspecialchars($row["consultant_name"]) . '</h3>
                                <p><img src="locationicon.png" style="width: 20px; height: auto; filter: invert(1)"> ' . htmlspecialchars($row["clinic_name"]) . '<p>
                                <p><img src="'. $row["speciality"] .'.png" style="width: 20px; height: auto; filter: invert(1)"> ' . htmlspecialchars($row["speciality"]) . '<p>
                                <div class="learnmorebutton">
                                    <a class="detailsbutton" href="consultantinfo.php?id=' . $row['id'] . '&name=' . urlencode($row['consultant_name']) . '">Learn More</a>
                                </div>
                            </div>
                        </div>';
                }
                echo '  </div>
                        <button class="scrollbuttons rightbutton2">&#10095;</button>
                    </div>
                ';
            }
            ?>
        </div>
        <div>
            <div class="text" style="text-align: left; padding-bottom: 0;">
                <h3>Featured Clinic</h3>
                <p>We feature one of our clinics. Refreshes when you visit this page</p>
            </div>
            <?php
                $fetchfeaturedclinic = $conn->query("
                    SELECT * FROM clinics
                    ORDER BY RAND()
                    LIMIT 1
                ");
                $clinicinfo = $fetchfeaturedclinic->fetch_assoc();

                $carparkingavailable = $clinicinfo['car_parking'] === 'Yes' ? "<span style='color:rgb(6, 180, 0)'> available</span>" : "<span style='color:rgb(255, 58, 58)'> not available</span>";
                $carparkingavailableimage = $clinicinfo['car_parking'] === 'Yes' ? "yescarparking.png" : "noparking.png";
                $disabledaccessavailable = $clinicinfo['disabled_access'] === 'Yes' ? "<span style='color:rgb(6, 180, 0)'> available</span>" : "<span style='color:rgb(255, 58, 58)'> not available</span>";
                $disabledaccessavailableimage = $clinicinfo['disabled_access'] === 'Yes' ? "disabledavailable.png" : "disabledunavailable.png";
                echo '
                <div style="padding: 10px 20px 20px">
                    <div class="featuredclinictab" data-latitude='.$clinicinfo['latitude'].' data-longitude='.$clinicinfo['longitude'].'>
                        <div id="clinicmap"></div>
                        <div class="clinicinfo">
                            <h2 style="font-family: Lexend">'. $clinicinfo['name'] .'</h2>
                            <h3 style="font-size: 20px; margin-top: 30px;">Location</h3>
                            <h3><span id="h3span"> | </span> <span id="clinicaddress">Loading address</span></h3>
                            <h3><span id="h3span"> | </span> Coordinates: '. $clinicinfo['latitude'] .','. $clinicinfo['longitude'] .'</h3>
                            <h3 style="font-size: 20px; margin-top: 30px">Facilities and Accessibility</h3>
                            <div style="display:flex; flex-wrap: wrap">
                                <div class="clinicinfotext" style="padding-right: 20px">
                                    <h3><span id="h3span"> | </span> <img src="';echo $carparkingavailableimage; echo'">Car parking is<strong>'; echo $carparkingavailable; echo '</strong></h3>
                                    </div>
                                <div class="clinicinfotext">
                                    <h3><span id="h3span"> | </span> <img src="'; echo $disabledaccessavailableimage; echo '">Disabled access is<strong>'; echo $disabledaccessavailable; echo '</strong></h3>
                                </div>
                            </div>
                            <hr>
                            <div style="display: flex; gap: 10px">
                                <div class="learnmorebutton lmbuttons" style="margin-top: 5px">
                                    <a class="detailsbutton" href="clinicinfo.php?id='. $clinicinfo['id'] .'&name=' . $clinicinfo['name'] .'">Learn More</a>
                                </div>
                                <div class="learnmorebutton lmbuttons" style="margin-top: 5px">
                                    <a class="detailsbutton" href="https://www.google.com/maps/dir/?api=1&destination=' . $clinicinfo['latitude'] . ',' . $clinicinfo['longitude'] . '"target="_blank">Get Directions</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                '
            ?>
        </div>
        <div class="accrediations" style="background-color: rgb(230, 230, 230); padding-bottom: 20px;">
            <div class="text" style="text-align:left">
                <h3>Accreditation and Awards</h3>
                <p>We have received accreditations and awards from several governing health bodies</p>
            </div>
            <div class="accrediationswrapper">
                <div id="accrediationstab">
                    <img src="nhsem.svg">
                </div>
                <div id="accrediationstab">
                    <img src="cqclogo.png">
                </div>
                <div id="accrediationstab">
                    <img src="disabilityconfidentlogo.png">
                </div>
                <div id="accrediationstab">
                    <img src="gmclogo.png">
                </div>
            </div>
        </div>
    </div>
    <?php
        include 'footer.php';
    ?>
    <script>
        var clinicinfo = <?php echo json_encode($clinicinfo); ?>;
    </script>
    <script src="enthub.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>