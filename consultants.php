<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultants | ENT Care Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="entlogo.png">
    <link rel="stylesheet" href="consultants.css">
    <style>
        .introductionimage {
            background-image: url(consultants.jpg);
        }
    </style>
</head>
<body>
    <?php
        include 'header.php';
        include 'database.php';
    ?>
    <div class="introductionimage">
        <div class="text">
            <h1>Our consultants</h1>
            <p>Meet our diverse team of highly skilled consultants</p>
        </div>
    </div>
    <?php 
        include 'searchsystem.php';
    ?>
    <div class="main-content">
        <div class="text" style="text-align: left">
            <h2 class="subheadings">Overview</h2>
            <p style="margin: 0 auto; padding-bottom: 50px;">
                ENT Care Hub has a collective and diverse team of consultants committed to delivering comprehensive care and
                wealth of clinical expertise to ensure best outcomes for our patients. Our healthcare is like no other and as 
                consultants, where challenges emerge in the future, we face this head on together.
            </p>
            <div class="consultantsinformationwrapper">
                <div id="consultantinfotab">
                    <img src="consultantteamicon.png">
                    <p>We have over 30+ consultants operating at our network of clinics across the East Midlands.</p>
                </div>
                <div id="consultantinfotab">
                    <img src="specialisationicon.png">
                    <p>Our consultants at ENT Care Hub specialise in over 6+ areas of ENT, including paediatrics and surgery. </p>
                </div>
                <div id="consultantinfotab">
                    <img src="nhsem.svg">
                    <p>Our consultants are accredited by the NHS East Midlands Clinical Senate</p>
                </div>
            </div>
        </div>
        <div class="specialityplusconsultants" style="background-color: rgb(16, 55, 86); color: white;">
            <div class="text" style="text-align: left; ">
                <h2>Meet our consultants</h2>
                <p>From Otology to Head and Neck Surgery, our consultants specialise in several ENT areas.</p>
            </div>
            <?php
            $specialitysections = "SELECT * FROM specialities";
            $spectab = $conn->query($specialitysections);

            $colorofspeciality = [];
            $colorofspeciality2 = [];
            $colorofspeciality3 = [];
            $specialitytype = $conn->query("SELECT DISTINCT speciality FROM specialities ORDER BY speciality ASC");

            if ($specialitytype && $specialitytype->num_rows > 0) {
                $listofspeciality = [];
                while ($row = $specialitytype->fetch_assoc()) {
                    $listofspeciality[] = $row['speciality'];
                }

                $total = count($listofspeciality);
                foreach ($listofspeciality as $index => $name) {
                    $hue = round(($index / $total) * 360);
                    $colorofspeciality[$name] = "hsl($hue, 40%, 40%)";
                    $colorofspeciality2[$name] = "hsl($hue, 40%, 50%)";
                    $colorofspeciality3[$name] = "hsl($hue, 40%, 30%)";
                }
            }
            if ($spectab->num_rows > 0) {
                while ($specialitysection = $spectab->fetch_assoc()) {
                    $speciality = $specialitysection['speciality'];
                    $color = isset($colorofspeciality[$speciality]) ? $colorofspeciality[$speciality] : 'hsl(209, 58.00%, 40.20%)';
                    $hovercolor = isset($colorofspeciality2[$speciality]) ? $colorofspeciality2[$speciality] : 'hsl(209, 59.00%, 32.50%)';
                    $gradientcolor = isset($colorofspeciality3[$speciality]) ? $colorofspeciality3[$speciality] : 'hsl(209, 59.00%, 32.50%)';
                    echo 
                "<div class='specialitysections' style='background-color: ". $color."; color: white'>
                    <div class='text specialitysectionsheader' onclick='togglesection(this)'>
                        <div class='specialitysectionsheadertext'>
                            <img src='".$specialitysection['speciality'].".png'>
                            <div style='padding-left: 10px'>
                                <h2>" . $specialitysection['speciality'] . " Consultants</h2>
                                <p>" . $specialitysection['description'] . "</p>
                            </div>
                        </div>
                        <button>⯆</button>
                    </div>
                    <div>";
                        $fetchconsultants = $conn->prepare("
                            SELECT
                                consultants.id, consultants.name AS consultant_name,
                                consultants.consultation_fee, clinics.name AS clinic_name,
                                specialities.speciality,
                                AVG(reviews.score) AS average_score
                            FROM consultants
                            JOIN clinics ON consultants.clinic_id = clinics.id
                            JOIN specialities ON consultants.speciality_id = specialities.id
                            LEFT JOIN reviews ON consultants.id = reviews.consultant_id
                            WHERE specialities.speciality = ?
                            GROUP BY consultants.id
                        ");
                        $fetchconsultants->bind_param("s", $speciality);
                        $fetchconsultants->execute();
                        $consultants = $fetchconsultants->get_result();

                        if ($consultants->num_rows > 0) {
                        echo '<div class="consultantsection">
                        <div class="consultantswrapper">
                                <button class="scrollbuttons leftbutton">&#10094;</button>
                                <div class="consultantinfosection">';
                        while ($row = $consultants->fetch_assoc()) {
                            echo '
                                <div class="consultantcard" style="background: linear-gradient(30deg, '. $gradientcolor .', rgb(190, 190, 190)); border: 5px solid '. $gradientcolor .'">
                                    <div style="display: flex; gap: 5px; justify-content: end">
                                        <h3 id="ratingscore">★ '. round($row['average_score'], 2) .'/5</h3>
                                        <h3 id="fees" style="background-color: '. $color. '; border: 10px solid ' .$color . ';">£' . htmlspecialchars($row["consultation_fee"]) . '</h3>
                                    </div>
                                    <div class="consultantinfo">
                                        <h3 style="color: white;">' . htmlspecialchars($row["consultant_name"]) . '</h3>
                                        <p><img src="locationicon.png" style="width: 20px; height: auto; filter: invert(1)"> ' . htmlspecialchars($row["clinic_name"]) . '<p>
                                        <p><img src="'. $row["speciality"] .'.png" style="width: 20px; height: auto; filter: invert(1)"> ' . htmlspecialchars($row["speciality"]) . '<p>
                                        <div class="learnmorebutton" style="background-color: '. $color .'">
                                            <a class="detailsbutton" href="consultantinfo.php?id=' . $row['id'] . '&name=' . urlencode($row['consultant_name']) . '">Learn More</a>
                                        </div>
                                    </div>
                                </div>';}
                        echo '  </div>
                            <button class="scrollbuttons rightbutton">&#10095;</button>
                        </div></div>';}
                    echo "</div>
                </div>
                    ";
                }
            }
            ?>
            <div class="text" style="text-align: right; ">
                <p>We plan to introduce more areas of ENT specialities outside our current scope.</p>
            </div>
        </div>
        <div class="whybookwithus">
            <div class="wbwuleftside">
                <div class="text" style="text-align: left; padding-bottom: 0;">
                    <h2 class="subheadings">Why choose our consultants?</h2>
                    <p>
                        Our consultants deliver unparalleled wealth of clinical expertise and are committed to providing you
                        treatment and professional advice to ensure best outcomes. Many patients previously booked
                        in with our consultants have rated them in a positive light.
                    </p>
                </div>
                <?php
                    $reviewssample = $conn->query("
                    SELECT consultants.id AS consultant_id, consultants.name, specialities.speciality, reviews.id AS patientid,
                        reviews.feedback, reviews.recommend, reviews.score
                    FROM consultants 
                    JOIN specialities ON specialities.id = consultants.speciality_id
                    JOIN reviews ON reviews.consultant_id = consultants.id
                    WHERE reviews.id IN (SELECT MIN(reviews.id)
                        FROM reviews WHERE reviews.consultant_id IN (SELECT consultant_id
                            FROM reviews GROUP BY consultant_id HAVING AVG(score) >= 4) AND reviews.score >= 4
                        GROUP BY reviews.consultant_id)
                    ORDER BY RAND()
                    LIMIT 4;
                    ");
                    echo '<div class="samplereviews">';
                    while($reviewsampletab = $reviewssample->fetch_assoc()) {
                        echo '
                        <div class="reviewtab">
                            <div class="starrating3" style="padding-left: 10px">';
                                $rating3 = (int) $reviewsampletab['score'];
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $rating3) {
                                        echo '<span style="color: gold; font-size: 30px">★</span>';
                                    } else {
                                        echo '<span style="color: gold; font-size: 30px">☆</span>';
                                    } 
                            } echo '</div>
                            <p>"'. htmlspecialchars($reviewsampletab['feedback']).'"<br>
                            <strong>- Patient #'. htmlspecialchars($reviewsampletab['patientid']).'</strong></p>
                            <p><a href="consultantinfo.php?id=' . $reviewsampletab['consultant_id'] . '&name=' . urlencode($reviewsampletab['name']) . '">'. htmlspecialchars($reviewsampletab['name']).' | '. htmlspecialchars($reviewsampletab['speciality']).'</a></p>
                        </div>
                        ';
                    } 
                    echo '</div>';
                    echo '<p style="font-size: 12px; color: rgb(160, 160, 160); text-align: center">(Updates every time you refresh/visit this page)</p>';
                ?>
                <div class="text" style="padding-top: 0; text-align: left;">
                    <p>
                        Booking in will allow us to deliver a unique and unforgettable experience, ensuring that you
                        are left satisfied after your appointment with our consultants is complete.
                    </p>
                </div>
            </div>
            <div class="wbwurightside"></div>
        </div>
        <div class="joinourteam">
            <div class="jotleftside"></div>
            <div class="jotrightside">
                <div class="text" style="text-align: left;">
                    <h2 class="subheadings" style="background-color: white; border: 15px solid white; color: rgb(43, 104, 162)">Join our team</h2>
                    <p>
                        With demand growing within the sector of ENT, as well as the influx in patients booking in with
                        our existing team of consultants, we present you the opportunity to become a part of our team
                        of highly skilled consultants.
                    </p>
                    <p>
                        We offer many benefits whilst you work and serve our patients, plus you'll have the opportunity to 
                        specialise in an area of ENT tailored to your interests, as well as operate in one of our many
                        established clinics in the East Midlands region. 
                    </p>
                    <p>We are always open to hiring. Join us!</p>
                </div>
            </div>
        </div>
    </div>
    <?php
        $conn->close();
        include 'footer.php';
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="consultants.js"></script>
</body>
</html>