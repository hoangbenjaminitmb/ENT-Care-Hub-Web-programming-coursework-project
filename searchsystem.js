document.getElementById("searchbutton").addEventListener("click", async (event) => {
    event.preventDefault();
        
    const locale = document.getElementById("enterlocale").value.trim();
    const dates = document.getElementById("selectdate").value;
    const speciality = document.getElementById("specialities").value;

    if (!locale || !dates || !speciality ) {
        alert("Please fill in all fields to get your results");
        return;
    } 

    try {
        const geocodeUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(locale)}&countrycodes=GB&addressdetails=1`;
    
        const response = await fetch(geocodeUrl);
        const data = await response.json();
    
        if (data.length > 0) {
            const latitude = parseFloat(data[0].lat);
            const longitude = parseFloat(data[0].lon);
    
            const url = `searchresults.php?lat=${latitude}&lng=${longitude}&speciality=${speciality}&locale=${encodeURIComponent(locale)}&dates=${encodeURIComponent(dates)}`;
            window.location.href = url;
        } else {
            alert("Location inputted not identified. Please try again.");
        }
    } catch (error) {
        console.error("Error fetching geolocation:", error);
        alert("Error. Location not found.");
    }
});