function setupscroll(containerselector, leftbuttonselector, rightbuttonselector, scrollincrement) {
    const container = document.querySelector(containerselector);
    const leftbutton = document.querySelector(leftbuttonselector);
    const rightbutton = document.querySelector(rightbuttonselector);

    function clicktoscroll(amount) {
        container.scrollBy({ left: amount, behavior: 'smooth' })
    }

    function showscrollbutton() {
        if (window.innerWidth < 768) {
            leftbutton.style.display = 'none';
            rightbutton.style.display = 'none';
            return;
        }

        if (container.scrollWidth > container.clientWidth) {
            leftbutton.style.display = 'block';
            rightbutton.style.display = 'block';
        } else {
            leftbutton.style.display = 'none';
            rightbutton.style.display = 'none';
        }
    }

    if (leftbutton && rightbutton && container) {
        leftbutton.addEventListener('click' , () => clicktoscroll(-scrollincrement));
        rightbutton.addEventListener('click' , () => clicktoscroll(scrollincrement));

        showscrollbutton();
        window.addEventListener('resize', showscrollbutton);
    }
}

setupscroll('#exploretabs', '.leftbutton', '.rightbutton', 443 );
setupscroll('.featuredconsultantsinfo', '.leftbutton2', '.rightbutton2', 600 );

async function convertlonglattoaddress(lat, lng, callback) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
        const data = await response.json();
        if (data && data.display_name) {
            const fulladdress = [data.address.road || "", data.address.city || data.address.county || "", data.address.postcode || ""].filter(Boolean).join(", ");
            callback(fulladdress);
        } else {
            callback("N/A");
        }
    } catch(error) {
        callback("N/A");
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const tab = document.querySelector(".featuredclinictab");
    const latitude = parseFloat(tab.dataset.latitude);
    const longitude = parseFloat(tab.dataset.longitude);

    convertlonglattoaddress(latitude, longitude, function (address) {
        document.getElementById("clinicaddress").textContent = "Address: " + address;
    });
});

document.addEventListener("DOMContentLoaded", function () {
    var clinicmap = L.map(('clinicmap'), { scrollWheelZoom: false }).setView([clinicinfo.latitude, clinicinfo.longitude], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(clinicmap);

    L.marker([clinicinfo.latitude, clinicinfo.longitude]).addTo(clinicmap)
});
