document.addEventListener("DOMContentLoaded", function () {
    if (typeof clinicLocations !== "undefined" && clinicLocations.length > 0) {
        var clinicmap = L.map('staticclinicmap', {
            zoomControl: false,
            dragging: false,
            scrollWheelZoom: false,
            doubleClickZoom: false,
            touchZoom: false
        }).setView([clinicLocations[2].cliniclatitude, clinicLocations[2].cliniclongitude], 9);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(clinicmap);

        clinicLocations.forEach(function(clinic) {
            L.marker([clinic.cliniclatitude, clinic.cliniclongitude])
                .addTo(clinicmap)
                .bindPopup(clinic.clinicname);
        });
    } else {
        console.error("Location data is missing or empty.");
    }
});
