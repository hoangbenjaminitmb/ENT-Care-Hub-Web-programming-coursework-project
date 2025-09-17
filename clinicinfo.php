<?php
include 'database.php';

if (isset($_GET['id'])) {
    $clinicid = (int) $_GET['id'];

    $clinicname = '';

    $fetchclinicname = $conn->prepare("
        SELECT clinics.name AS clinicname 
        FROM clinics 
        WHERE clinics.id = ?
    ");

        $fetchclinicname->bind_param("i", $clinicid);
        $fetchclinicname->execute();
        $clinicnamefound = $fetchclinicname->get_result();

        if ($clinicnamefound && $clinicnamefound->num_rows > 0) {
            $rowname = $clinicnamefound->fetch_assoc();
            $clinicname = htmlspecialchars($rowname['clinicname']).' | Clinic Info';
        } else {
            $clinicname = 'Unknown Clinic';
        }
    }
else {
    $clinicname = 'Unknown Clinic';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $clinicname ?> | ENT Care Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="entlogo.png">
    <link rel="stylesheet" href="clinicinfo.css">
</head>
<body>
    <?php
        include 'header.php';
        $cliniccolors = [];
        $cliniccolors2 = [];
        $cliniccolors3 = [];
        $clinicarea = $conn->query("SELECT DISTINCT clinics.name FROM clinics ORDER BY clinics.name ASC");

        if ($clinicarea && $clinicarea->num_rows > 0) {
            $listofclinics = [];
            while ($row = $clinicarea->fetch_assoc()) {
                $listofclinics[] = $row['name'];
            }

            $total = count($listofclinics);
            foreach ($listofclinics as $index => $name) {
                $hue = round(($index / $total) * 360);
                $cliniccolors[$name] = "hsl($hue, 40%, 40%)";
                $cliniccolors2[$name] = "hsl($hue, 40%, 30%)";
                $cliniccolors3[$name]= "hsl($hue, 40%, 35%)";
            }
        }

    if (isset($_GET['id'])) {
        $clinicid = (int) $_GET['id'];

        $fetchclinicinfo = $conn->prepare("
            SELECT clinics.name AS clinicname, clinics.latitude, clinics.longitude, clinics.car_parking, clinics.disabled_access
            FROM clinics
            WHERE clinics.id = ?
        ");
        $fetchclinicinfo->bind_param("i", $clinicid);
        $fetchclinicinfo->execute();
        $clinicresult = $fetchclinicinfo->get_result();

        if ($clinicresult->num_rows>0) {
            while ($clinicrow = $clinicresult -> fetch_assoc()) {
                $clinicname = $clinicrow['clinicname'];
                $color = isset($cliniccolors[$clinicname]) ? $cliniccolors[$clinicname] : 'rgb(30, 71, 109)';
                $hovercolor = isset($cliniccolors2[$clinicname]) ? $cliniccolors2[$clinicname] : 'rgb(22, 51, 79)';
                $buttoncolor = isset($cliniccolors3[$clinicname]) ? $cliniccolors3[$clinicname] : 'rgb(31, 64, 95)';
                
                echo "
                    <div class='toppart'>
                        <div class='leftside' data-latitude=".$clinicrow['latitude']." data-longitude=".$clinicrow['longitude']." style='background-color: ". $color ."'>
                            <div>
                                <h1>". htmlspecialchars($clinicrow['clinicname']) ."</h1>
                                <p id=\"clinicaddress\">Loading Address...</p>
                                <div class='learnmorebutton' style='margin-top: 5px'>
                                    <a class='detailsbutton' href='https://www.google.com/maps/dir/?api=1&destination=" . $clinicrow['latitude'] . "," . $clinicrow['longitude'] . "'target='_blank'>Get Directions</a>
                                </div>
                            </div>
                        </div>
                        <div class='rightside'>
                            <iframe
                                frameborder=\"0\"
                                src=\"https://www.google.com/maps?q=". $clinicrow['latitude'] .",". $clinicrow['longitude'] ."&hl=es;z=8&output=embed\"
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                ";
                $fetchspecialities = $conn->prepare("
                    SELECT specialities.speciality, specialities.description FROM consultants
                    JOIN specialities ON consultants.speciality_id = specialities.id
                    WHERE consultants.clinic_id = ?
                    GROUP BY specialities.speciality
                ");
                $fetchspecialities->bind_param("i", $clinicid);
                $fetchspecialities->execute();
                $specialityresults = $fetchspecialities->get_result();

                $clinicspecialities = [];
                while ($specialityrow = $specialityresults->fetch_assoc()) {
                    $clinicspecialities[] = $specialityrow;
                };

                $clinicconsultants = $conn->prepare("
                    SELECT consultants.clinic_id FROM consultants
                    WHERE consultants.clinic_id = ?
                ");
                $clinicconsultants->bind_param("i",$clinicid);
                $clinicconsultants->execute();
                $countconsultants = $clinicconsultants->get_result();
                $noofconsultants = $countconsultants->num_rows;

                echo '
                <div class="main-content">
                    <div class="informationsection">
                        <div class="infosecleftside">
                            <button class="dropdownmenu" onclick="swaptext()"><div id="dropdown">Jump to section</div></button>
                            <div class="infosecbuttons">
                                <h4 style="font-size: 16px">Click to jump to a section of this page</h4>
                                <button class="navigablebutton" onclick="scrolltosection(event)" id="overviewbutton">Overview</button>
                                <button class="navigablebutton" onclick="scrolltosection(event)" id="specialitiesbutton">Specialities</button>
                                <button class="navigablebutton" onclick="scrolltosection(event)" id="consultantsbutton">Our Consultants</button>
                                <button class="navigablebutton" onclick="scrolltosection(event)" id="visitusbutton">Visit Us</button>
                            </div>
                        </div>
                        <div class="infosecrightside" data-latitude='.$clinicrow['latitude']." data-longitude=".$clinicrow['longitude'].'>
                            <div id="overviewtab" class="infosection">
                                <h2 id="detailstitle">Overview</h2>';
                                    $listedspecs = array_map(function($spec) {return $spec['speciality'];},$clinicspecialities);
                                    if (count($listedspecs) > 1) {
                                        $lastspec = array_pop($listedspecs);
                                        $specstring = implode(', ', $listedspecs) . ' and ' . $lastspec;
                                    } else {
                                        $specstring = implode('', $listedspecs);
                                    }
                                echo'
                                <p>
                                    Located in <strong><span id="cityname">(loading city)</span></strong>, '. htmlspecialchars($clinicrow['clinicname']) .' is one of our many clinics
                                    operating within the East Midlands region. 
                                </p>
                                <p> 
                                    Part of the ENT Care Hub network of clinics, this clinic has a team of '. $noofconsultants.' consultants that operate there, 
                                    each of them specialising in '. $specstring.' respectively. 
                                </p>
                                <p>    
                                    You can learn more about our clinic\'s facilities, areas of specialities and consultants by scrolling down.
                                </p>
                                <div style="background-color: rgb(200, 200, 200)">
                                    <h3 class="text" style="font-size: 20px; padding-bottom: 40px">Quick info:</h3>
                                    <div class="generalclinicinfo" data-latitude='.$clinicrow['latitude']." data-longitude=".$clinicrow['longitude'].'>';
                                        $carparkingavailable = $clinicrow['car_parking'] === 'Yes' ? "<span style='color:rgb(6, 180, 0)'> available</span>" : "<span style='color:rgb(255, 58, 58)'> not available</span>";
                                        $carparkingavailableimage = $clinicrow['car_parking'] === 'Yes' ? "yescarparking.png" : "noparking.png";
                                        echo '<div id="gcitab">
                                            <img src="'. $carparkingavailableimage .'">
                                            <h3>Car parking is <strong>'. $carparkingavailable .'</strong></h3>
                                        </div>';
                                        $disabledaccessavailable = $clinicrow['disabled_access'] === 'Yes' ? "<span style='color:rgb(6, 180, 0)'> available</span>" : "<span style='color:rgb(255, 58, 58)'> not available</span>";
                                        $disabledaccessavailableimage = $clinicrow['disabled_access'] === 'Yes' ? "disabledavailable.png" : "disabledunavailable.png";
                                        echo '<div id="gcitab">
                                            <img src="'. $disabledaccessavailableimage .'">
                                            <h3>Disabled access is <strong>'. $disabledaccessavailable .'</strong></h3>
                                        </div>
                                        <div id="gcitab">
                                            <img src="consultantteamicon.png">
                                            <h3>Over <strong>'. $noofconsultants.'</strong> consultants</h3>
                                        </div>
                                        <div id="gcitab">
                                            <img src="locationicon.png">
                                            <h3 id="clinicaddress2">Loading Address...</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div id="specialitiestab" class="infosection">
                                <h2 id="detailstitle">Specialities</h2>
                                <p>'. htmlspecialchars($clinicrow['clinicname']) .' specialises in the following ENT areas:</p>
                                <div class="spectabwrapper">';
                                foreach($clinicspecialities as $clinicspecialitytype) {
                                echo '
                                    <div id="spectab" style="background-color: '. $hovercolor .'; border: 5px solid '. $hovercolor .'">
                                        <img src="'. htmlspecialchars($clinicspecialitytype['speciality']).'.png">
                                        <div>
                                            <h4>'. htmlspecialchars($clinicspecialitytype['speciality']).' </h4> 
                                            <p>'. htmlspecialchars($clinicspecialitytype['description']).' </p>
                                        </div>
                                    </div>';} 
                                echo '
                                </div>
                                <br>
                                <p>We intend to introduce more specialities at this clinic in the future to ensure everyone receives treatment or advice.</p>
                            </div>
                            <div id="consultantstab" class="infosection">
                                <h2 id="detailstitle">Consultants</h2>
                                <p>
                                    We have over '. $noofconsultants.' consultants that operate at '. htmlspecialchars($clinicrow['clinicname']) .', each specialising in
                                    different areas of ENT. You can take a look at who operates there and what they specialise in below.
                                </p>';
                                $fetchconsultantsatclinic = $conn->prepare("
                                    SELECT
                                        consultants.id, consultants.name AS consultant_name,
                                        consultants.consultation_fee, clinics.id AS clinic_id,
                                        specialities.speciality,
                                        AVG(reviews.score) AS average_score
                                    FROM consultants
                                    JOIN clinics ON consultants.clinic_id = clinics.id
                                    JOIN specialities ON consultants.speciality_id = specialities.id
                                    LEFT JOIN reviews ON consultants.id = reviews.consultant_id
                                    WHERE consultants.clinic_id = ?
                                    GROUP BY consultants.id
                                ");
                                $fetchconsultantsatclinic->bind_param("i", $clinicid);
                                $fetchconsultantsatclinic->execute();
                                $consultants = $fetchconsultantsatclinic->get_result();

                                if ($consultants->num_rows > 0) {
                                    echo '<div class="consultantsection">';
                                    while ($row = $consultants->fetch_assoc()) {
                                        echo '
                                    <a href="consultantinfo.php?id=' . $row['id'] . '&name=' . urlencode($row['consultant_name']) . '">
                                    <div class="consultantcard" style="border-left: 10px solid '.$color.'">
                                        <div class="consultantcardinfo">
                                            <div class="consultantimage" style="text-align: center">
                                                <img src="healthconsultanticon.png" width="100px" height="100px" style="border:1px none; border-radius: 8px;">
                                            </div>
                                            <div class="consultantinfo">
                                                <div id="topcontent">
                                                    <h3>'. htmlspecialchars($row['consultant_name']) .'</h3>
                                                </div>
                                                <div class="consultantgeneralinfo">
                                                    <p><strong>Speciality:</strong> ' . htmlspecialchars($row["speciality"]) . '</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="display: flex; align-items: center">
                                            <div>
                                                <h3 id="ratingscore">★ '. round($row['average_score'], 2) .'/5</h3> 
                                                <h3 id="fees">£' . htmlspecialchars($row["consultation_fee"]) . '</h3>
                                            </div>
                                            <div class="learnmorebutton2" style="margin-bottom: 10px; margin-right: 10px">
                                                <div style="padding: 40px 20px; " class="detailsbutton">&#10095;</div>
                                            </div>
                                        </div>
                                    </div></a>';}
                                echo '  </div>';}
                                echo '<p>We aim to recruit more consultants at this clinic to accommodate growing demand within the area.</p>
                            </div>
                            <div id="visitustab" class="infosection">
                                <h2 id="detailstitle">Visit Us</h2>
                                <div class="visitustabcontent">
                                    <iframe style="border-left: 10px solid '. $color .'"
                                    frameborder=\"0\"
                                    src=\'https://www.google.com/maps?q='. $clinicrow['latitude'] .",". $clinicrow['longitude'] .'&hl=es;z=8&output=embed\'
                                    allowfullscreen>
                                    </iframe>
                                    <div class="visitustextsection">
                                        <div class="visitustext">
                                            <h4 style="border-left: 6px solid '. $color .'"><span style="padding-left: 8px">Location</span></h4>
                                            <p>
                                                The address for this clinic is <strong><span id="clinicaddress3">Loading address...</span></strong>, which is located in the 
                                                <strong><span id="cityname2">Loading city...</span></strong> area.
                                            </p>
                                            <h4 style="border-left: 6px solid '. $color .'"><span style="padding-left: 8px">Before visiting</span></h4>
                                            <p>
                                                Our clinics may or may not have any disability access or available car parking. Please refer to below
                                                what is available.
                                            </p>
                                        </div>
                                        <div class="visitusclinicinfotext">
                                            <div class="clinicinfotext">
                                                <h3><img src="';echo $carparkingavailableimage; echo'">Car parking is<strong>'; echo $carparkingavailable; echo '</strong></h3>
                                            </div>
                                            <div class="clinicinfotext">
                                                <h3><img src="'; echo $disabledaccessavailableimage; echo '">Disabled access is<strong>'; echo $disabledaccessavailable; echo '</strong></h3>
                                            </div>
                                        </div>
                                        <div class="learnmorebutton" style="margin-top: 5px">
                                            <a class="detailsbutton" href="https://www.google.com/maps/dir/?api=1&destination=' . $clinicrow['latitude'] . "," . $clinicrow['longitude'] . '" target="_blank">Get Directions</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div>
                                <div class="learnmorebutton" style="margin-top: 5px">
                                    <a class="detailsbutton" href="clinics.php">See other clinics</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                ';
            }
        } else {
            echo 
            "<div class='text' style='text-align: center; padding-top: 20px; margin: 0 auto;padding-bottom: 60px'>
                <h3 style=\"padding-top: 50px; font-size: 20px\">It seems like this clinic does not exist or we don't operate there</h3>
                <img src=\"unknownclinic.png\" style=\"padding: 50px; width: 350px; height: 350px\">
                <h3 style=\"padding-bottom: 10px; font-size: 20px\">What would you like to do?</h3>
                <div class=\"unknownclinicshyperlinks\">
                    <a href=\"enthub.php\">Return to home</a><br>
                    <a href=\"clinics.php\">See the clinics page</a>
                </div>
            </div>";
        }
    } else {
        echo 
        "<div class='text' style='text-align: center; padding-top: 20px; margin: 0 auto;padding-bottom: 60px'>
            <h3 style=\"padding-top: 50px; font-size: 20px\">It looks like you did not put in a clinic ID</h3>
            <img src=\"unknownclinic.png\" style=\"padding: 50px; width: 350px; height: 350px\">
            <h3 style=\"padding-bottom: 10px; font-size: 20px\">What would you like to do?</h3>
            <div class=\"unknownclinicshyperlinks\">
                <a href=\"enthub.php\">Return to home</a><br>
                <a href=\"clinics.php\">See the clinics page</a>
            </div>
        </div>";
    } 
        include 'footer.php';
    ?>
    <style>
        #fees, #detailstitle {
            background-color: <?php echo $color?>;
            border: <?php echo $color?> 10px solid;
        }
        .consultantcard:hover #fees {
            background-color: <?php echo $hovercolor?>;
            border: <?php echo $hovercolor?> 10px solid
        }
        .detailsbutton {
            background-color: <?php echo $buttoncolor?>;
            transition: background-color 0.2s ease;
        }
        .detailsbutton:hover {
            background-color: <?php echo $hovercolor?>;
        }
        .consultantcard:hover .detailsbutton {
            background-color: <?php echo $hovercolor?>;
        }
        .consultantcard:hover .consultantinfo h3 {
            color: <?php echo $color?>;
        }
        .dropdownmenu {
            background-color: <?php echo $color?>;
        }
        .dropdownmenu:hover {
            background-color: <?php echo $hovercolor?>;
        }
        .navigablebutton {
            transition: all 0.3s ease;
        }
        .navigablebutton:hover {
            color: <?php echo $color?>;
        }
        .navigablebutton.active {
            border-bottom: 5px solid <?php echo $color?>;
            color: <?php echo $color?>;
            font-weight: bold;
        }
    </style>
    <script src="clinicinfo.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>