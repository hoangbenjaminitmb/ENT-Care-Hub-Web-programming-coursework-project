function initMap() {
    const locationofclinics = L.map(document.querySelector('.showmap'), { scrollWheelZoom: false }).setView([52.787, -1.2181633], 9);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(locationofclinics);

    locations.forEach(location => {
        const marker = L.marker([location.cliniclatitude, location.cliniclongitude], {
            icon: L.icon({
                iconUrl: 'cliniclocationmarker.png',
                iconSize: [50, 50]
            })
        }).addTo(locationofclinics);

        marker.bindPopup(`
            <div class="markerlink">
                <a href="clinicinfo.php?id=${location.clinicid}&name=${location.clinicname}"><h3>${location.clinicname}</h3></a>
            </div>
        `);
    });
}

document.querySelector('.resetfilterbutton').addEventListener('click', () => {
    document.querySelectorAll('.clinicfilters input[type="checkbox"]').forEach(checkbox => { checkbox.checked = false; });
    const select = document.getElementById('areasofspeciality');
    if (select) { select.selectedIndex = 0; }
    if (typeof fetchclinics === "function" && typeof ogclinics !== "undefined") { fetchclinics(ogclinics); }
});

async function convertlonglattoaddress(lat, lng) {
    try {
        const reversegeocode = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`);
        const data = await reversegeocode.json();
        if (data && data.display_name) {
            const fulladdress = [data.address.road || "", data.address.city || data.address.county || "", data.address.postcode || ""].filter(Boolean).join(", ");
            return fulladdress;
        } else {
            return "N/A";
        }
    } catch (error) {
        return "N/A";
    }
}

document.addEventListener("DOMContentLoaded", async function () {
    const clinics = document.querySelectorAll(".clinicinformation");

    clinics.forEach(async function (clinic) {
        const latitude = parseFloat(clinic.dataset.latitude);
        const longitude = parseFloat(clinic.dataset.longitude);
        const addressspan = clinic.querySelector(".clinicaddress");

        convertlonglattoaddress(latitude, longitude).then(function (address) {
            if (addressspan) {
                addressspan.textContent = address;
            }
        });
    });
});


const checkdisability = document.getElementById('disability');
const checkcarparking = document.getElementById('carparking');
const selectspecialitytype = document.getElementById('areasofspeciality');
const resetallfilters = document.querySelector('.resetfilterbutton');
const clinics = document.querySelectorAll('.showingclinics');

function filterclinicsby() {
    clinics.forEach(clinic => {
        let show = true;

        if (checkdisability.checked && clinic.getAttribute('data-disabledaccess') !== 'Yes') show = false;
        if (checkcarparking.checked && clinic.getAttribute('data-carparking') !== 'Yes') show = false;
        if (selectspecialitytype.value !== '' && !clinic.getAttribute('data-specialities').split(',').includes(selectspecialitytype.value)) show = false;

        clinic.style.display = show ? '' : 'none';
    });
    document.getElementById('countclinics').textContent = document.querySelectorAll('.showingclinics:not([style*="display: none"])').length
}

let clinicmaps = [];

checkdisability.addEventListener('change', () => {filterclinicsby(); fixmarkerpos()});
checkcarparking.addEventListener('change', () => {filterclinicsby(); fixmarkerpos()});
selectspecialitytype.addEventListener('change', () => {filterclinicsby(); fixmarkerpos()});
resetallfilters.addEventListener('click', () => {
    checkdisability.checked = false;
    checkcarparking.checked = false;
    selectspecialitytype.value = ''; filterclinicsby(); fixmarkerpos();
});

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".clinicinformation").forEach(function(clinic) {
        let latitude = clinic.getAttribute("data-latitude");
        let longitude = clinic.getAttribute("data-longitude");
        let clinicid = clinic.closest(".showingclinics").querySelector(".clinicmap").id;

        let clinicmap = L.map(clinicid, {
            zoomControl: false,
            dragging: false,
            scrollWheelZoom: false,
            doubleClickZoom: false,
            touchZoom: false
        }).setView([latitude, longitude], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(clinicmap);
        L.marker([latitude, longitude]).addTo(clinicmap);
        clinicmaps.push({ map: clinicmap, lat: latitude, lng: longitude});

        window.addEventListener("resize", fixmarkerpos())
    });
});

function fixmarkerpos() {
    clinicmaps.forEach(entry => {
        entry.map.invalidateSize();
        entry.map.setView([entry.lat, entry.lng], 14);
    })
}