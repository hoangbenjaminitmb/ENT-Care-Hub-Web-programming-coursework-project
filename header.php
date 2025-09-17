<link rel="stylesheet" href="maincssscript1.css">
<link href="https://fonts.googleapis.com/css2?family=Lexend&display=swap" rel="stylesheet">

<div class="mainheader">
    <div class="pageheading">
        <h1><a href="enthub.php"><span style="color: rgb(139, 218, 218)">ENT</span> Care Hub</a></h1>
    </div>

    <button class="sidebar-toggle btn btn-dark">☰</button>
    <div class="sidebar d-flex">
        <nav class="nav fw-bold fs-6">
            <?php
                $links = [
                    "enthub.php" => "Home",
                    "clinics.php" => "Clinics",
                    "consultants.php" => "Consultants",
                    "about.php" => "About Us",
                ];
                foreach ($links as $href => $text) {
                    echo "<a class='nav-link' href='$href'>$text</a>";
                }
            ?>
        </nav>
    </div>
</div>

<script src="header.js"></script>

