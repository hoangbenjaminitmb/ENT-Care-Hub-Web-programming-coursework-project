<?php
include 'database.php';

if (isset($_GET['id'])) {
    $consultantId = (int) $_GET['id'];

    $consultantname = '';

    $fetchconsultantname = $conn->prepare("
        SELECT consultants.name AS consultant_name 
        FROM consultants 
        WHERE consultants.id = ?
    ");

        $fetchconsultantname->bind_param("i", $consultantId);
        $fetchconsultantname->execute();
        $consultantnamefound = $fetchconsultantname->get_result();

        if ($consultantnamefound && $consultantnamefound->num_rows > 0) {
            $rowname = $consultantnamefound->fetch_assoc();
            $consultantname = htmlspecialchars($rowname['consultant_name']).' | Consultant Info';
        } else {
            $consultantname = 'Unknown Consultant';
        }
    }
else {
    $consultantname = 'Unknown Consultant';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $consultantname?> | ENT Care Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="entlogo.png">
    <link rel="stylesheet" href="consultantinfo.css">
</head>
<body>
    <?php
        include 'header.php';
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
                $colorofspeciality2[$name] = "hsl($hue, 40%, 30%)";
                $colorofspeciality3[$name] = "hsl($hue, 40%, 30%)";
            }
        }

        $selecteddate = null;
        if(isset($_GET['dates'])) {
            $selecteddate = date('Y-m-d', strtotime($_GET['dates']));
        }

        if (isset($_GET['id'])) {
            $consultantId = (int) $_GET['id'];

            $fetchotherconsultants = $conn->prepare("
                SELECT
                    consultants.id, consultants.name AS consultant_name,
                    consultants.consultation_fee, clinics.name AS clinic_name,
                    specialities.speciality, 
                    AVG(reviews.score) AS average_score
                FROM consultants
                JOIN clinics ON consultants.clinic_id = clinics.id
                JOIN specialities ON consultants.speciality_id = specialities.id
                LEFT JOIN reviews ON consultants.id = reviews.consultant_id
                WHERE consultants.speciality_id = (
                    SELECT speciality_id FROM consultants WHERE id = ?
                ) AND consultants.id != ?
                GROUP BY consultants.id
            ");
            $fetchotherconsultants->bind_param("ii", $consultantId, $consultantId);
            $fetchotherconsultants->execute();
            $otherconsultants = $fetchotherconsultants->get_result();

            $fetchconsultantinfo = $conn->prepare("
                SELECT 
                    consultants.name AS consultant_name,consultants.consultation_fee, clinics.*,
                    specialities.speciality, specialities.description AS speciality_description,
                    AVG(reviews.score) AS average_score, COUNT(reviews.id) AS total_reviews
                FROM consultants
                JOIN clinics ON consultants.clinic_id = clinics.id
                JOIN specialities ON consultants.speciality_id = specialities.id
                LEFT JOIN reviews ON consultants.id = reviews.consultant_id
                WHERE consultants.id = ?
                GROUP BY consultants.id
            ");
            $fetchconsultantinfo->bind_param("i", $consultantId);
            $fetchconsultantinfo->execute();
            $consultantresult = $fetchconsultantinfo->get_result();

            $fetchconsultantschedule = $conn->prepare("
                SELECT weekday FROM consultant_schedule WHERE consultant_id = ?
            ");
            $fetchconsultantschedule->bind_param("i", $consultantId);
            $fetchconsultantschedule->execute();
            $scheduleresults = $fetchconsultantschedule->get_result();
            $weekdays = [
                0 => 'Monday',
                1 => 'Tuesday',
                2 => 'Wednesday',
                3 => 'Thursday',
                4 => 'Friday',
                5 => 'Saturday',
                6 => 'Sunday'
            ];

            $availabledays = [];
            $availableweekdays = [];
            while ($weekdayrow = $scheduleresults->fetch_assoc()) {
                $daynum = (int)$weekdayrow['weekday'];
                if (isset($weekdays[$daynum])) {
                    $availabledays[] = $weekdays[$daynum] . "s";
                }
                    
                $availableweekdays[] = $daynum;
            }

            $fetchbookings = $conn->prepare("
                SELECT booking_date FROM bookings WHERE consultant_id = ?
            ");
            $fetchbookings->bind_param("i", $consultantId);
            $fetchbookings->execute();
            $bookingresult = $fetchbookings->get_result();

            $alreadybooked = [];
            while ($bookingrow = $bookingresult->fetch_assoc()) {
                $alreadybooked[] = $bookingrow['booking_date'];
            }

            $fetchreviews = $conn->prepare("
                SELECT id, feedback, score, recommend FROM reviews WHERE consultant_id = ?
            ");
            $fetchreviews->bind_param("i", $consultantId);
            $fetchreviews->execute();
            $reviewresult = $fetchreviews->get_result();

            $listofreviews = [];
            while ($rv = $reviewresult->fetch_assoc()) {
                $listofreviews[] = $rv;
            }

            $countrecommended = 0;
            $countnotrecommended = 0;
            
            foreach ($listofreviews as $review) {
                if (strtolower($review['recommend']) === 'yes') {
                    $countrecommended++;
                } elseif (strtolower($review['recommend']) === 'no') {
                    $countnotrecommended++;
                }  
            }
        if ($consultantresult->num_rows > 0) {
            while ($row = $consultantresult->fetch_assoc()) {
                $speciality = $row['speciality'];
                $color = isset($colorofspeciality[$speciality]) ? $colorofspeciality[$speciality] : 'hsl(209, 58.00%, 40.20%)';
                $hovercolor = isset($colorofspeciality2[$speciality]) ? $colorofspeciality2[$speciality] : 'hsl(209, 59.00%, 32.50%)';
                $gradientcolor = isset($colorofspeciality3[$speciality]) ? $colorofspeciality3[$speciality] : 'hsl(209, 59.00%, 32.50%)';
                echo "<div class='toppage'>
                    <div class='leftside'>
                        <img src=\"healthconsultanticon.png\">
                    </div>
                    <div class='rightside' style='text-align: left; background-color: $color;'>
                        <h1>". htmlspecialchars($row['consultant_name']) ."</h1>
                        <p>". htmlspecialchars($row['speciality']). " <span style=\"font-weight: 900; font-size: 20px;\"> | </span> ". htmlspecialchars($row['name']). "</p>
                    </div>
                </div>";
    
                echo '
                <div class="main-content">
                    <div class="starter"></div>
                    <button class="dropdownmenu" onclick="swaptext1()"><div id="dropdown">Jump to Section</div></button>
                    <div class="infonavigation">
                        <button class="navigablebutton" onclick="showinfo(event, \'overview\');" id="showfirst">Overview</button>
                        <button class="navigablebutton" onclick="showinfo(event, \'availability\')">Fees & Availability</button>
                        <button class="navigablebutton" onclick="showinfo(event, \'reviews\')">Reviews</button>
                        <button class="navigablebutton" onclick="showinfo(event, \'clinicinfo\')">Clinic Info</button>
                        <button class="navigablebutton" onclick="showinfo(event, \'otherconsultants\')">See others</button>
                    </div>
                    <div class="text">
                        <div id="overview" class="details"> 
                            <h2 id="detailstitle">Overview</h2>
                            <p class="text" style="padding: 10px 20px 20px">
                                '. htmlspecialchars($row['consultant_name']).' is one of our many consultants that is a part of ENT Care Hub. Our consultant operates at 
                                <strong>'. htmlspecialchars($row['name']). '</strong>.
                            </p>
                            <p class="text" style="padding: 0 20px 20px">
                                Our consultant specialises in <strong>'. htmlspecialchars($row['speciality']). '</strong>, 
                                which involves working with patients with <strong>'. htmlspecialchars($row['speciality_description']). '.
                                </strong>Our consultant charges a consultation fee of <strong>£'. htmlspecialchars($row['consultation_fee']). '</strong>. 
                                ' . htmlspecialchars($row["total_reviews"]). ' verified patients who have booked in with
                                '. htmlspecialchars($row['consultant_name']).' have given an average review score of 
                                <span style="font-weight: bold">'. round($row['average_score'],2). ' out of 5</span>.
                            </p>
                            <p class="text" style="padding: 0 20px 20px">
                                You can learn more about our consultant using the navigation menu above.
                            </p>
                            <div class="overviewtabs" style="background-color: #ebebeb">
                                <div id="ovtab">
                                    <h2>Speciality</h2>
                                    <img src="'. htmlspecialchars($row['speciality']). '.png">
                                    <h1>'. htmlspecialchars($row['speciality']). '</h1>
                                    <p>'. htmlspecialchars($row['speciality_description']). '</p>
                                </div>
                                <div id="ovtab">
                                    <h2>Clinic</h2>                 
                                    <img src="locationicon.png">       
                                    <h1>'. htmlspecialchars($row['name']). '</h1>
                                    <button class="clinicinfobutton" 
                                        style="font-weight: bold; color: '. $color .'; background-color: rgba(0,0,0,0); border: none" 
                                        onclick="showinfo(event, \'clinicinfo\'); ">Learn More
                                    </button>
                                </div>
                                <div id="ovtab">
                                    <h2>Consultation Fees</h2>
                                    <img src="costicon.png">
                                    <h1>£'. htmlspecialchars($row['consultation_fee']). '</h1>
                                    <p>As of 2025</p>
                                </div>
                                <div id="ovtab">
                                    <h2>Overall Rating</h2>
                                    <h3 id="ratingscore2" style="margin-top: 35px; margin-bottom: 35px">★ '. round( $row['average_score'], 2) .'/5</h3>
                                    <h1>'. round($row['average_score'],2). '/5</h1>
                                    <button class="reviewsbutton" 
                                        style="font-weight: bold; color: '. $color .'; background-color: rgba(0,0,0,0); border: none" 
                                        onclick="showinfo(event, \'reviews\')">
                                        From '. htmlspecialchars($row['total_reviews']). ' reviews
                                    </button>
                                </div>
                            </div>                        
                        </div>
                        <div id="availability" class="details">
                            <div class="availabilitywrapper">
                                <div class="availabilitytext">
                                    <div>
                                        <h2 id="detailstitle">Fees</h2>
                                        <p style="padding-top: 10px">As of 2025, our consultant currently charges a fee of:</p>
                                        <div class="feestab">
                                            <img src="costicon.png">
                                            <h3 style="font-size: 30px; padding-left: 10px">£'. htmlspecialchars($row['consultation_fee']). '</h3>
                                        </div>
                                    </div>
                                    <div>
                                        <h2 id="detailstitle">Availability</h2>
                                        <p style="padding-top: 10px">';
                                            $days = $availabledays;
                                            if (count($availabledays) > 1) {
                                                $lastday = array_pop($days);
                                                $daystring = implode(', ', $days) . ' and ' . $lastday;
                                            } else {
                                                $daystring = implode('', $days);
                                            } echo htmlspecialchars($row['consultant_name']). ' is typically available on <strong>' . $daystring . '</strong>.
                                        </p>
                                        <hr>
                                        <p>
                                            Key:
                                        </p>
                                        <div class="calendarguidance">
                                            <p><span id="keyinfo">16</span>  Booking Available</p>
                                            <p><span class="alreadybooked" style="border-radius: 10px; border: 10px solid rgb(130, 130, 130); ">18</span>  Already booked</p>
                                            <p><span class="notavailableonthatday" style="border-radius: 10px; border: 10px solid rgb(220, 220, 220)">25</span>  Not available</p>';
                                            if (!empty($selecteddate)) : 
                                                echo '<p><span id="highlighteddate">23</span>  Date selected</p>';
                                            endif;
                                        echo '</div>
                                    </div>
                                </div>
                                <div class="availabilitycalendar">';
                                    echo '<script>';
                                        if (!empty($selecteddate)) {
                                            echo 'var selectedDate = "' . htmlspecialchars($selecteddate) . '";';
                                        } else {
                                            echo 'var selectedDate = null;';
                                        } echo 'var availableweekdays = ' .json_encode($availableweekdays) .';';
                                    echo '</script>';
                                    if (!empty($selecteddate)) :
                                        echo '<p>
                                            Selected date: 
                                            <span id="highlighteddate"><strong>' . htmlspecialchars($selecteddate) .'</strong>
                                            <a href="consultantinfo.php?id=' . $consultantId . '&name=' . urlencode($row['consultant_name']) . '" title="Opt out">⨉</a>
                                            </span></p>';
                                    endif;
                                    echo '<h3>Showing dates where '. htmlspecialchars($row['consultant_name']). ' is available</h3>
                                    <div class="calendarheader">
                                        <h3 id="currentmonth"></h3>
                                        <div>
                                            <button id="prevmonth">&lt;</button>
                                            <button id="nextmonth">&gt;</button>
                                        </div>
                                    </div>
                                    <div class="calendargrid" id="calendar-grid"></div>
                                </div>
                            </div>
                            
                        </div>
                        <div id="reviews" class="details">
                            <h2 id="detailstitle">Reviews for ' . htmlspecialchars($row['consultant_name']) .'</h2>';
                            if (empty($listofreviews)) {
                                echo "<p class=\"text\" style=\"text-align: center; padding-top: 10px\">" . htmlspecialchars($row['consultant_name']) . " has no available reviews</p>";
                            } else {
                                echo '
                                    <div class="reviewscontainer" style="padding-top: 10px">
                                        <div class="reviewinfoandfilter">
                                            <div style="text-align: center; padding: 0; height: 200px; ">
                                                <h3>Overall Rating</h3>
                                                <h3 id="ratingscore2">★ '. round( $row['average_score'], 2) .'/5</h3>
                                            <h3 style="text-align: center; padding-top: 5px">(' . htmlspecialchars($row["total_reviews"]). ' reviews)</h3>
                                            <div class="recnotrec">
                                                <div id="recommended">
                                                    <h3 style="text-align: center"><span>'; echo $countrecommended . '/' . htmlspecialchars($row["total_reviews"]). '</span><br>patients would recommend</h3>
                                                </div>';
                                                if (!empty($countnotrecommended)) :
                                                echo '<div id="notrecommended">
                                                    <h3 style="text-align: center"><span>'; echo $countnotrecommended . '/' . htmlspecialchars($row["total_reviews"]). '</span><br>patients wouldn\'t recommended</h3>
                                                </div>';
                                                endif;
                                            echo '</div>
                                        </div>
                                        <hr>
                                        <button class="filtermenudropdown" onclick="swaptext2()"><div id="showfilter">Show Filters</div></button>
                                        <div class="filtertab">
                                            <h2 style="font-size: 20px; padding-top: 20px">Filter reviews by:</h2>
                                            <div class="filters">
                                                <div class="sortbyrating"> 
                                                    <h3>Rating</h3>
                                                    <label><input type="radio" name="sort" value="highest"> Highest to Lowest</label><br>
                                                    <label><input type="radio" name="sort" value="lowest"> Lowest to Highest</label>
                                                </div>
                                                <div class="filterrecommendations"> 
                                                    <h3>Recommendations</h3>
                                                    <label><input type="checkbox" class="recommendfilter" value="yes"> Recommended by Patient ('; echo $countrecommended . ')</label><br>
                                                    <label><input type="checkbox" class="recommendfilter" value="no"> Not Recommended by Patient ('; echo $countnotrecommended . ')</label>
                                                </div>
                                            </div>
                                            <div class="resetfilters"  style="margin-top: 5px">
                                                <button class="resetfilterbutton">Reset All Filters</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="reviewsarea">
                                        <h3 style="padding-bottom: 5px">Showing <span id="visiblecount">'. htmlspecialchars($row['total_reviews']). '</span> of '. htmlspecialchars($row['total_reviews']). ' reviews:</h3>
                                        <div class="listofreviews">';
                                            foreach ($listofreviews as $review) {
                                            $recommendation = strtolower($review['recommend']);
                                            $recommendtext = $review['recommend'] === 'Yes' ? "✅ Would recommend" : "❌ Wouldn't recommend";
                                            echo '
                                            <div class="customerreview" style="border-left: '. $color .' solid 10px"
                                            data-rating="'.htmlspecialchars($review['score']) .'"
                                            data-recommend="'. htmlspecialchars($recommendation) .'">
                                                <div>
                                                    <img style="width: 100px; height: auto; padding-left: 15px; padding-right: 15px" src="usericon.png">
                                                </div>
                                                <div class="reviewcontents" style="text-align: left;">
                                                    <h3>Patient #' . htmlspecialchars($review['id']) .'</h3>
                                                    <p style=" padding: 0 0 10px">' . htmlspecialchars($review['feedback']) . '</p>
                                                    <div class="ratingtext">
                                                        <div class="starrating3" style="padding: 0">';
                                                            $rating3 = (int) $review['score'];
                                                            for ($i = 1; $i <= 5; $i++) {
                                                                if ($i <= $rating3) {
                                                                    echo '<span style="color: gold; font-size: 20px">★</span>';
                                                                } else {
                                                                    echo '<span style="color: gold; font-size: 20px">☆</span>';
                                                                } 
                                                        } echo '</div>
                                                        <div class="patientscore"><p style="padding: 0">Rating: ' . htmlspecialchars($review['score']) .'/5</p></div>
                                                        <div class="recommendationtext" style="padding: 0"><p>'. htmlspecialchars($recommendtext) .'</p></div>
                                                        ';
                                                    echo '</div>
                                                </div>
                                                
                                            </div>';}
                                        echo '</div>
                                    </div>
                                </div>';
                            }
                            echo '
                        </div>
                        <div id="clinicinfo" class="details" data-latitude="' . htmlspecialchars($row['latitude']) . '" data-longitude="' . htmlspecialchars($row['longitude']) . '">';
                            $carparkingavailable = $row['car_parking'] === 'Yes' ? "<span style='color:rgb(6, 180, 0)'> available</span>" : "<span style='color:rgb(255, 58, 58)'> not available</span>";
                            $carparkingavailableimage = $row['car_parking'] === 'Yes' ? "yescarparking.png" : "noparking.png";
                            $disabledaccessavailable = $row['disabled_access'] === 'Yes' ? "<span style='color:rgb(6, 180, 0)'> available</span>" : "<span style='color:rgb(255, 58, 58)'> not available</span>";
                            $disabledaccessavailableimage = $row['disabled_access'] === 'Yes' ? "disabledavailable.png" : "disabledunavailable.png";
                            
                            echo '<div class="clinicinfowrapper">
                                <div class="generalclinicinfo">
                                    <h2 id="detailstitle">' . htmlspecialchars($row['name']). '</h2>
                                    <h3 style="font-size: 20px; margin-top: 20px">Location</h3>
                                    <h3><span id="h3span"> | </span> <span id="clinicaddress"> Loading address...</span></h3>
                                    <h3><span id="h3span"> | </span> Coordinates: ' . htmlspecialchars($row['latitude']) .','. htmlspecialchars($row['longitude']). '</h3>
                                    <h3 style="font-size: 20px; margin-top: 30px">Facilities and Accessibility</h3>
                                    <div class="clinicinfotext">
                                        <h3><span id="h3span"> |</span> <img src="';echo $carparkingavailableimage; echo'">Car parking is<strong>'; echo $carparkingavailable; echo '</strong></h3>
                                        </div>
                                    <div class="clinicinfotext">
                                        <h3><span id="h3span"> |</span> <img src="'; echo $disabledaccessavailableimage; echo '">Disabled access is<strong>'; echo $disabledaccessavailable; echo '</strong></h3>
                                    </div>
                                    <hr>
                                    <div style="display: flex; gap: 10px">
                                        <div class="learnmorebutton lmbuttons" style="margin-top: 5px">
                                            <a class="detailsbutton" href="clinicinfo.php?id='. $row['id'] .'&name=' . $row['name'] .'">Learn More</a>
                                        </div>
                                        <div class="learnmorebutton lmbuttons" style="margin-top: 5px">
                                            <a class="detailsbutton" href="https://www.google.com/maps/dir/?api=1&destination=' . $row['latitude'] . ',' . $row['longitude'] . '"target="_blank">Get Directions</a>
                                        </div>
                                    </div>
                                </div>
                                <iframe
                                    frameborder="0"
                                    src="https://www.google.com/maps?q='. $row['latitude'] .','. $row['longitude'] .'&hl=es;z=8&output=embed"
                                    allowfullscreen>
                                </iframe>
                            </div>
                        </div>
                        <div id="otherconsultants" class="details">
                            <h2 id="detailstitle">Other Consultants</h2>
                            <h3 style="padding-top: 15px">Other related consultants within ' . htmlspecialchars($row['speciality']) . ' include</h3>';
                            if ($otherconsultants->num_rows > 0) {
                                echo '<div class="otherconsultantswrapper">
                                        <button class="scrollbuttons leftbutton">&#10094;</button>
                                        <div class="otherconsultantinfo">';
                                while ($others = $otherconsultants->fetch_assoc()) {
                                    echo '
                                        <div class="otherconsultantcard" style="background: linear-gradient(30deg, '. $gradientcolor .', rgb(190, 190, 190)); border: 5px solid '. $gradientcolor .'">
                                            <div style="display: flex; gap: 5px; justify-content: end">
                                                <h3 id="ratingscore">★ '. round($others['average_score'], 2) .'/5</h3>
                                                <h3 id="fees">£' . htmlspecialchars($others["consultation_fee"]) . '</h3>
                                            </div>
                                            <div class="consultantinfo">
                                                <h3 style="color: white;">' . htmlspecialchars($others["consultant_name"]) . '</h3>
                                                <p><img src="locationicon.png" style="width: 20px; height: auto; filter: invert(1)"> ' . htmlspecialchars($others["clinic_name"]) . '<p>
                                                <p><img src="'. $others["speciality"] .'.png" style="width: 20px; height: auto; filter: invert(1)"> ' . htmlspecialchars($others["speciality"]) . '<p>
                                                <div class="learnmorebutton" style="background-color: '. $color .'">
                                                    <a class="detailsbutton" href="consultantinfo.php?id=' . $others['id'] . '&name=' . urlencode($others['consultant_name']);
                                                    if (!empty($selecteddate)) {
                                                        echo '&dates=' . htmlspecialchars($selecteddate);
                                                    } echo '">Learn More</a>
                                                </div>
                                            </div>
                                        </div>';}
                                echo '  </div>
                                    <button class="scrollbuttons rightbutton">&#10095;</button>
                                </div>
                                <hr>';
                            }
                        echo '<h3 style="padding-top: 10px; padding-bottom: 1px;">Alternatively, you can search for other consultants near you</h3>';
                        include "searchsystem.php";
                        echo '
                            <div style="display: flex; gap: 10px; max-width: 600px; margin: 0 auto">
                                <div class="learnmorebutton lmbuttons" style="margin: 20px 0">
                                    <a class="detailsbutton" href="consultants.php">See other consultants</a>
                                </div>
                                <div class="learnmorebutton lmbuttons" style="margin: 20px 0">
                                    <a class="detailsbutton" href="clinics.php">See our network of clinics</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';
            }
        } else {
            echo "<div class='text' style='text-align: center; padding-top: 20px; margin: 0 auto;padding-bottom: 60px'>
            <h3 style=\"padding-top: 50px; font-size: 20px\">It seems like the consultant ID you inputted isn't found on our databases</h3>
            <img src=\"unknownconsultant.png\">
            <h3 style=\"padding-bottom: 10px; font-size: 20px\">What would you like to do?</h3>
            <div class=\"unknownconsultanthyperlinks\">
                <a href=\"searchresults.php\">Go back to search</a><br>
                <a href=\"enthub.php\">Return to home</a><br>
                <a href=\"consultants.php\">See the consultants page</a>
            </div>
            </div>";
        }
    
        $fetchconsultantinfo->close();
    } else {
        echo "<div class='text' style='text-align: center; padding-top: 20px; margin: 0 auto;padding-bottom: 60px'>
            <h3 style=\"padding-top: 50px; font-size: 20px\">It looks like you did not input a consultant ID.</h3>
            <img src=\"unknownconsultant.png\">
            <h3 style=\"padding-bottom: 10px; font-size: 20px\">What would you like to do?</h3>
            <div class=\"unknownconsultanthyperlinks\">
                <a href=\"searchresults.php\">Go back to search</a><br>
                <a href=\"enthub.php\">Return to home</a><br>
                <a href=\"consultants.php\">See the consultants page</a>
            </div>
        </div>";
    };
    $conn->close();
    include 'footer.php';
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        const alreadybooked = <?php echo json_encode($alreadybooked); ?>;
    </script>
    <script src="consultantinfo.js"></script>
    <style> 
        .availabilitycalendar {
            border-left: <?php echo $color ?> solid 10px;
        }
        .infonavigation button:hover {
            color: <?php echo $color ?>;
        }
        .bookingavailable {
            color: white;
            font-weight: bold;
            background-color: <?php echo $color?>;
        }
        .alreadybooked {
            background-color: rgb(130, 130, 130);
            border: 2px solid rgb(60, 60, 60);
            font-weight: normal;
            color: rgb(230, 230, 230);
        }
        #detailstitle, #fees, #ratingscore2 {
            background-color: <?php echo $color?>;
            border: <?php echo $color?> 10px solid;
        }
        #keyinfo {
            background-color: <?php echo $color?>;
            border: <?php echo $color?> 10px solid;
        }
        .infonavigation button.active {
            color: <?php echo $color ?>;
            border-bottom: <?php echo $color ?> solid;
        }
        .clinicinfowrapper iframe {
            border-left: <?php echo $color ?> solid 10px;
        }
        .filtertab {
            border-radius: 5px;
            border-left: <?php echo $color ?> solid 10px;
        }
        .details h2 span {
            color: <?php echo $color?>;
        }
        .search h2 {
            color: <?php echo $color?>;
        }
        #h3span {
            color: <?php echo $color?>;
        }
        .search button {
            background-color: <?php echo $color?>;
        }
        .search button:hover {
            background-color: <?php echo $hovercolor?>;
        }
        .dropdownmenu {
            background-color: <?php echo $color?>;
        }
        .filtermenudropdown {
            background-color: <?php echo $color?>;
        }
        .dropdownmenu:hover {
            background-color: <?php echo $hovercolor?>;
        }
        .filters input[type="radio"], .filters input[type="checkbox"] {
            accent-color: <?php echo $color?>;
        }
        .resetfilterbutton {
            background-color: <?php echo $color?>;
            transition: background-color 0.2s ease;
        }
        .leftside img {
            border: 10px solid <?php echo $color?>;
        }
        .resetfilterbutton:hover {
            background-color: <?php echo $hovercolor?>;
        }
        .detailsbutton {
            background-color: <?php echo $color?>;
            transition: background-color 0.2s ease;
        }
        .detailsbutton:hover {
            background-color: <?php echo $hovercolor?>;
        }
    </style>
</body>
</html>
