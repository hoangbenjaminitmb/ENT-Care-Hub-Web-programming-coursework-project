<style>
    .nav {
        display: flex;
        flex-direction: row;
        justify-content: center;
    }
</style>

<div class="bottompart">
    <div style="margin: 0 auto">
        <nav class="nav fw-bold">
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
    <div class="text" style="text-align: right; margin: 0 auto; font-size: 14px">
        <button onclick="window.scrollTo(0,0)" style="background: none; border: none; color: white;">⯅ Return to the top</button>
        <img src="entlogo.png" alt="entlogo" style="height: 90px">
        <img src="cqclogo.png" alt="cqclogo" style="height: 85px">
        <p style="font-size: 14px;">ENT Care Hub © - Shaping Healthcare in the East Midlands since 1921</p>
    </div>
</div>