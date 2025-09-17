<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results | ENT Care Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="entlogo.png">
    <link rel="stylesheet" href="searchresults.css">
</head>
<body>
    <?php
        include 'header.php';
    ?>
    <br>
    <div class="main-content">
        <?php 
            include 'searchsystem.php';
            if (isset($_GET['lat']) && isset($_GET['lng']) && isset($_GET['speciality'])) {
                $lat = $_GET['lat'];
                $lng = $_GET['lng'];
                $speciality = $_GET['speciality'];
                include 'database.php';
                if (isset($_GET['lat'], $_GET['lng'], $_GET['speciality'], $_GET['locale'], $_GET['dates'])) {
                    $EnteredLocale = htmlspecialchars($_GET['locale']);
                    $CalculatedLatitude = (float)$_GET['lat'];
                    $CalculatedLongitude = (float)$_GET['lng'];
                    $SelectedSpeciality = htmlspecialchars($_GET['speciality']);
                    $SelectedDate = date('Y-m-d', strtotime($_GET['dates']));
                    $weekday = date('l', strtotime($SelectedDate));

                    $identifyinfo = $conn->prepare("
                        SELECT DISTINCT consultants.id, consultants.name, consultants.consultation_fee, clinics.name AS clinicname,
                        clinics.latitude, clinics.longitude, specialities.speciality, AVG(reviews.score) AS average_score,
                        GROUP_CONCAT(DISTINCT consultant_schedule.weekday ORDER BY consultant_schedule.weekday ASC) AS available_days,
                        GROUP_CONCAT(DISTINCT bookings.booking_date ORDER BY bookings.booking_date ASC) AS booking_dates,
                        (3959 * acos(
                            cos(radians(?)) * cos(radians(clinics.latitude)) * cos(radians(clinics.longitude) - radians(?)) +
                            sin(radians(?)) * sin(radians(clinics.latitude))
                        )) AS distance
                        FROM clinics
                        JOIN consultants ON clinics.id = consultants.clinic_id
                        JOIN specialities ON consultants.speciality_id = specialities.id
                        LEFT JOIN consultant_schedule ON consultants.id = consultant_schedule.consultant_id
                        LEFT JOIN reviews ON consultants.id = reviews.consultant_id
                        LEFT JOIN bookings ON consultants.id = bookings.consultant_id
                        WHERE specialities.speciality = ?
                        GROUP BY consultants.id;
                    ");

                    $identifyinfo->bind_param("ddds", $CalculatedLatitude, $CalculatedLongitude, 
                        $CalculatedLatitude, $SelectedSpeciality);

                    $identifyinfo->execute();
                    $result = $identifyinfo->get_result();
                    $totalResults = $result->num_rows;
                    if ($totalResults > 0) {
                        echo '
                            <div class="searchresultscontainer">
                                <div class="resultsinfoandfilter">
                                    <div style="font-size: 14px;text-align: left; ">
                                        <p style="margin: 0"><strong>Selected date:</strong> ' . $SelectedDate .' </p>
                                        <p style="margin: 0"><strong>Location entered:</strong> '. $EnteredLocale .' </p>
                                    </div>
                                    <button class="filtermenudropdown" onclick="swaptext2()">
                                    <div id="showfilter">Show Filters</div>
                                        </button>
                                    <div class="filtertab">
                                        <h2 style="font-size: 20px; padding-top: 20px;">Sort results by:</h2>
                                        <div class="filters">
                                            <div class="sortby"> 
                                                <label><input type="radio" name="sort" value="highestrating"><span style="padding-left: 5px">Rating</span></label><br>
                                                <label><input type="radio" name="sort" value="closestdistance"><span style="padding-left: 5px">Distance</span></label><br>
                                                <label><input type="radio" name="sort" value="lowestcost"><span style="padding-left: 5px">Cost</span></label>
                                            </div>
                                        </div>
                                        <h2 style="font-size: 20px; padding-top: 20px;">Filter by availability:</h2>
                                        <select class="filterbyavailability">
                                            <option value="all">Show all</option>
                                            <option value="available">Available on '. $SelectedDate .'</option>
                                        </select>
                                        <div class="resetfilters" style="margin: 5px">
                                            <button class="resetfilterbutton">Reset All Filters</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="resultsarea">
                                    <h3 style="padding-bottom: 5px; font-size: 14px; text-align: left">Showing <span id="visiblecount">'. $totalResults .'</span> of '. $totalResults . ' '.$SelectedSpeciality . ' consultants:</h3>
                                    <div class="displayingtabs">';
                                        $weekdays = [
                                        0 => 'Mondays',
                                        1 => 'Tuesdays',
                                        2 => 'Wednesdays',
                                        3 => 'Thursdays',
                                        4 => 'Fridays',
                                        5 => 'Saturdays',
                                        6 => 'Sundays'
                                    ];

                                    while ($row = $result->fetch_assoc()) {
                                        $availabledays = [];
                                        $booking_dates = $row['booking_dates'] ? explode(',',$row['booking_dates']) : [];
                                        $availableweekdays = $row['available_days'] ? explode(',',$row['available_days']) : [];

                                        $nextavailableday = null;
                                        $checkdate = $SelectedDate;

                                        for ($i = 0; $i < 365; $i++) {
                                            $checkdate = date('Y-m-d', strtotime($SelectedDate . "+$i days"));
                                            $checkweekday = date('N', strtotime($checkdate)) - 1;

                                            $isbooked = in_array($checkdate, $booking_dates);
                                            $isavailableday = in_array($checkweekday, $availableweekdays);

                                            if ($isavailableday && !$isbooked) {
                                                $nextavailableday = $checkdate;
                                                break;
                                            }
                                        }

                                        $availableonselected = ($nextavailableday == $SelectedDate);
                                        $alreadybooked = in_array($SelectedDate, $booking_dates);
                                        
                                        if (!empty($row['available_days'])) {
                                            $daysarray = explode(',', $row['available_days']);
                                            $availabledays = array_map(fn($day) => $weekdays[$day], $daysarray);
                                        }
                                        echo '
                                        <div class="searchresultinfo" 
                                            data-rating="' .round($row['average_score'],2). '"
                                            data-distance="' .round($row['distance'],2). '"
                                            data-consultationfee="' .$row['consultation_fee']. '"
                                            data-available="'. ($availableonselected ? true : false) .'">
                                            <div class="consultantimage"> 
                                                <a class="clicktolearnmore" href="consultantinfo.php?id=' . $row['id'] . '&name=' . urlencode($row['name']) . '&dates=' . urlencode($SelectedDate). '"></a>
                                                <img src="healthconsultanticon.png" style="width: 100px; height: auto; padding-left: 15px; padding-right: 15px">
                                            </div>
                                            <div class="consultantinfo" style="text-align: left;">
                                                <div id="topcontent">
                                                    <h3>'. htmlspecialchars($row['name']) .'</h3>
                                                    <div style="display: flex; gap: 5px;">
                                                        <h3 id="ratingscore">★ '. round($row['average_score'], 2) .'/5</h3> 
                                                        <h3 id="fees">£' . htmlspecialchars($row["consultation_fee"]) . '</h3>
                                                    </div>
                                                </div>
                                                <p style="margin-bottom: 2px"><img src="locationicon.png" style="width: 25px; height: auto;">'. htmlspecialchars($row['clinicname']) .'
                                                (<strong>'. round($row['distance'], 2) .'mi</strong> from '. $EnteredLocale .')</p>
                                                <div class="availabledays">';
                                                    $days = $availabledays;
                                                    if (count($availabledays) > 1) {
                                                        $lastday = array_pop($days);
                                                        $daystring = implode(', ', $days) . ' & ' . $lastday;
                                                    } else {
                                                        $daystring = implode('', $days);
                                                    } echo '<p style="padding-right: 10px"><img src="calendaricon.png" style="width: 25px; height: auto;"> '.$daystring.'</p>';
                                                    if ($alreadybooked) {
                                                        echo '<p><strong>Already booked</strong> | Next available booking: <strong>'. $nextavailableday .'</strong></p>';
                                                    } else {
                                                        if ($nextavailableday) {
                                                            if ($nextavailableday == $SelectedDate) {
                                                                echo '<p><strong>Booking available</strong> on selected date: <strong>' . $SelectedDate . '</strong></p>';
                                                            } else {
                                                                echo '<p><strong>Unavailable</strong> | Next available booking: <strong>'. $nextavailableday .'</strong></p>';
                                                            }
                                                        } else {
                                                            echo '<p>No available bookings</p>';
                                                        };
                                                    };
                                                echo '</div>
                                            </div>
                                        </div>'; }
                                    echo '</div>
                                </div>
                            </div>
                        </div>';
                    } else {
                        echo '<div class="no-results">
                                <h3 style="padding-top: 50px; font-size: 20px">It seems that your search inputs have returned no results</h3>
                                <img src="noresultfound.png" alt="No results">
                                <h3 style="padding-bottom: 50px; font-size: 20px">Try another search</h3>
                            </div>';
                    }
                    $identifyinfo->close();
                    $conn->close();
                } else {
                    echo '<p class="error-message">Please provide all search parameters.</p>';
                }
            } else {
                echo "<div style=\"; padding-top: 100px; font-size: 20px\">
                <br> <p style=\"padding-bottom: 500px\">Please enter your postcode and select your area of concern/speciality for results</p>
                </div>";
            }            
        ?>
    </div>
    <?php
        include 'footer.php';
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="searchresults.js"></script>
</body>
</html>