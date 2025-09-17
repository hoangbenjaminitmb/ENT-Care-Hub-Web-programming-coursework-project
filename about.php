<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About us | ENT Care Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="icon" type="image/png" href="entlogo.png">
    <link rel="stylesheet" href="about.css">
    <style>
        .introductionimage {
            background-image: url(aboutusimage.jpg);
        }
    </style>
</head>
<body>
    <?php
        include 'header.php';
        include 'database.php';
        $countclinic = $conn->query("SELECT * FROM clinics");
        $totalclinics = $countclinic->num_rows;

        $countconsultants = $conn->query("SELECT * FROM consultants");
        $totalconsultants = $countconsultants->num_rows;

        $countspecialities = $conn->query("SELECT * FROM specialities");
        $totalspecialities = $countspecialities->num_rows;

        $countreviews = $conn->query("SELECT * FROM reviews");
        $totalreviews = $countreviews->num_rows;
    ?>
    <div class="introductionimage">
        <div class="text" style="text-align: center; margin: 0 auto;">
            <h1 style="margin: 100px 0 25px;">We are ENT Care Hub</h>
            <h2 style="font-size: 18px;">And we aim to shape healthcare in the East Midlands, for the population and you</h2>
        </div>
    </div>
    <div class="main-content">
        <div style="background-color: rgb(43, 104, 162); color: white;">
            <div class="text" style="max-width: 800px; padding-top: 50px; padding-bottom: 50px;">
                <p>
                    Founded in 1921, ENT Care Hub is a leading healthcare provider and a privately established healthcare
                    system in the East Midlands, specialising in various areas of Ear, Nose, and Throat health services. 
                </p>
                <p>
                    With a diverse and collective team of highly trained and skilled consultants across many ENT specialities, 
                    we are committed to providing high quality healthcare through our network of clinics to 
                    ensure that our patients feel supported. With many challenges that are infront of us, together we face this head on.
                </p>
            </div>
        </div>
        <div class="weareent">
            <div class="text">
                <h3 style="font-family: Lexend;"><span>\\ </span>What makes us unique?<span> //</span></h3>
                <p>We are one of the largest private healthcare providers in the East Midlands, with:</p>
            </div>
            <div class="aboutustabs">
                <div id="autab">
                    <div id="autabimg">
                        <img src="clinicicon.png">
                    </div>
                    <p>A network of <?php echo $totalclinics?> clinics established in the East Midlands, including Nottingham & Leicester</p>
                </div>
                <div id="autab">
                    <div id="autabimg">
                        <img src="specialisationicon.png">
                    </div>
                    <p>Over <?php echo $totalspecialities?> specialities within ENT are practised by our consultants, with more in the future</p>
                </div>
                <div id="autab">
                    <div id="autabimg">
                        <img src="consultantteamicon.png">
                    </div>
                    <p>A highly rated, skilled and diverse team of <?php echo $totalconsultants?> active consultants at ENT Care Hub.</p>
                </div>
                <div id="autab">
                    <div id="autabimg">
                        <img src="reviewsicon.png">
                    </div>
                    <p>Over <?php echo $totalreviews?> reviews were made to our team of consultants by our patients</p>
                </div>
            </div>
            <div class="text">
                <h4>And accreditation and awards from the:</h4>
            </div>
            <div class="accredtabs">
                <div id="acctab">
                    <img src="nhsem.svg">
                    <p>NHS East Midlands Clinical Senate</p>
                </div>
                <div id="acctab">
                    <img src="cqclogo.png" style="filter: invert(0)">
                    <p>CareQuality Commission</p>
                </div>
                <div id="acctab">
                    <img src="gmclogo.png">
                    <p>General Medical Council</p>
                </div>
            </div>
        </div>
    </div>
    <div class="consultantgrid">
        <div class="consultantgridleftside"></div>
        <div class="consultantgridrightside">
            <div class="text" style="padding-bottom: 20;">
                <div style="background-color: rgb(39, 81, 121); color: white;">
                    <h4 style="padding: 20px;">Consultants with a difference</h4>
                </div>
                <p style="padding-top: 10px;">
                    We are a diverse team of highly skilled consultants committed to hearing our patients,
                    as well as providing treatment and professional advice to ensure best outcomes. Many of our patients have 
                    rated our consultants in a positive light.
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
            <p class="text" style="padding: 10px 20px;">
                We are constantly improving with the feedback provided by our patients, and our consultants are always open to queries.
                We aim to pioneer the future of ENT with cutting edge healthcare and your feedback is always deserved.
            </p>
            <div class="consultantsgridahref text" style="padding-bottom: 30px;">
                <a href="consultants.php">Learn more about our consultants</a>
            </div>
        </div>
    </div>
    <div class="clinicgrid">
        <div class="clinicgridleftside">
            <div class="text" style="padding-bottom: 20;">
                <div style="background-color: rgb(255, 255, 255); color: rgb(25, 82, 129);">
                    <h4 style="padding: 20px;">Well-being within reach</h4>
                </div>
                <p style="padding-top: 10px;">
                    ENT Care Hub has a significant presence across the East Midlands region, meaning you will have no problem finding one of our nearest
                    ENT clinics when you want book an appointment with our consultants.
                </p>
                <p>
                    We have established a network of <?php echo $totalclinics?> ENT clinics across major areas in the East Midlands, particularly in
                    Derby and Nottingham, with other locations in Leicester and Loughborough, where each clinic specialises in various areas of ENT.
                </p>
                <br>
                <div class="clinicgridahref" style="padding-bottom: 10px">
                    <a href="clinics.php">Learn more about our clinics</a>
                </div>
            </div>
        </div>
        <?php
            $cliniclocations = [];
            $gettingcliniclocation = $conn->query("
                SELECT name, latitude, longitude FROM clinics
            ");
            if ($gettingcliniclocation -> num_rows > 0) {
                while ($row = $gettingcliniclocation -> fetch_assoc()) {
                    $cliniclocations[] = [
                        'clinicname' => $row['name'],
                        'cliniclatitude' => floatval($row['latitude']),
                        'cliniclongitude' => floatval($row['longitude'])
                    ];
                }
            } 
        ?>
        <div class="clinicgridrightside">
            <div id="staticclinicmap"></div>
        </div>
    </div>
    <div class="main-content">
        <div class="specialisationarea">
            <div class="text" style="text-align: left;">
                <div class="h4spec">
                    <h4>Specialising from Otology to Surgery</h4>
                </div>
                <p style="max-width: 800px;">
                    At ENT Care Hub, what makes us unique is that we specialise in various areas of 
                    ENT, and many of our consultants practise them with skill and commitment. Many of our specialities range from:
                </p>
            </div>
            <div class="specialitywrapper">
                <?php
                $specialitytab = "SELECT * FROM specialities";
                $typeofspeciality = $conn->query($specialitytab);
                if ($typeofspeciality->num_rows > 0) {
                    while ($row = $typeofspeciality->fetch_assoc()) {
                        echo 
                    "<div class='specialitytabs'>
                        <h4>" . $row['speciality'] . "</h4>
                        <img src='".$row['speciality'].".png'>
                        <p>" . $row['description'] . "</p>
                    </div>
                        ";
                    }
                }
                ?>
            </div>
            <div class="text">
                <p>We have intentions to include many more upcoming ENT specialities in the future, such as sleep surgery and skull base surgery.</p>
            </div>
        </div>
    </div>
    <div class="comevisitusgrid">
        <div class="cvuleftside"></div>
        <div class="cvurightside">
            <div class="text">
                <div style="background-color: rgb(39, 81, 121); color: white;">
                    <h4 style="padding: 20px;">Why book with us</h4>
                </div>
                <p style="padding-top: 10px;">
                    With over <?php echo $totalconsultants?> consultants to book in with, <?php echo $totalclinics?>
                    clinics to visit across the East Midlands, and <?php echo $totalspecialities?> ENT specialities to choose from, ENT Care Hub delivers 
                    unparalleled wealth of clinical expertise, where we make a difference.
                </p>
                <p>
                    We deliver a unique and unforgettable experience and specialise in areas tailored to your concerns. 
                    In addition, our clinics are located a reasonable distance away from your location, so you will have no trouble finding us
                </p>
                <p>
                    What are you waiting for? Book with us now!
                </p>
            </div>
        </div>
    </div>
    <div class="main-content" style="background-color:rgb(22, 60, 91)">
        <div>
            <?php
                include 'searchsystem.php';
            ?>
            <div class="logo">
                <img src="entlogo.png">
                <div class="logotext">
                    <h1><span style="color: rgb(139, 218, 218)">ENT</span> Care Hub</h1>
                    <p>Shaping healthcare in the East Midlands since 1921</p>
                </div>
            </div>
        </div>
    </div>
    <?php
        include 'footer.php';
        $conn->close()
    ?>
    <style>
        .search {
            color: white;
        }

        .search h2 {
            color: white
        }
    </style>
    <script>
        var clinicLocations = <?php echo json_encode($cliniclocations); ?>;
    </script>
    <script src="about.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</body>
</html>