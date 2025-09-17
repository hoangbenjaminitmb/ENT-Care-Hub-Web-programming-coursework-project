async function reversegeocode(lat, lng) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
        return await response.json();
    } catch {
        return null;
    }
}

function getcoordinates(selector) {
    const cliniclatlng = document.querySelector(selector);
    return [parseFloat(cliniclatlng.dataset.latitude), parseFloat(cliniclatlng.dataset.longitude)];
}

document.addEventListener("DOMContentLoaded", async function () {
    async function fetchaddress(queryselector, addressformat = "full") {
        let [lat, lng] = getcoordinates(queryselector);
        let data = await reversegeocode(lat, lng);
        let address = "N/A";

        if (data?.address) {
            if (addressformat === "full") {
                address = [data.address.road || "",data.address.city || data.address.county || "",data.address.postcode || ""].filter(Boolean).join(", ");
            } else if (addressformat === "cityonly") {
                address = [data.address.city || data.address.county || ""].filter(Boolean).join(", ");
            }
        } return address;
    }

    const selectedaddress = [
        { queryselector: ".leftside", clinicspanid: "clinicaddress", addressformat: "full" },
        { queryselector: ".leftside", clinicspanid: "clinicaddress2", addressformat: "full" },
        { queryselector: ".generalclinicinfo", clinicspanid: "cityname", addressformat: "cityonly" },
        { queryselector: ".leftside", clinicspanid: "clinicaddress3", addressformat: "full" },
        { queryselector: ".generalclinicinfo", clinicspanid: "cityname2", addressformat: "cityonly" },
    ];

    const addresses = await Promise.all(selectedaddress.map(cfg => fetchaddress(cfg.queryselector, cfg.addressformat)));

    addresses.forEach((address, index) => {
        document.getElementById(selectedaddress[index].clinicspanid).textContent = address;
    });
});


function switchtext(id, text1, text2) {
    var element = document.getElementById(id);
    element.innerHTML = (element.innerHTML === text1) ? text2 : text1;
}

function swaptext() {switchtext('dropdown' , 'Jump to section', 'Close')}

document.addEventListener("DOMContentLoaded", () => {
    const infosecbuttons = document.querySelector('.infosecbuttons');
    const buttons = document.querySelectorAll('.infosecbuttons button')

    const showhiddentab = (element) => element.classList.toggle('active');
    const autohidetab = () => {
        infosecbuttons.classList.remove('active');
        const dropdowntext = document.getElementById('dropdown');
        if (dropdowntext) dropdowntext.innerHTML = 'Jump to section';
    };

    document.querySelector('.dropdownmenu').addEventListener('click', () => showhiddentab(infosecbuttons));

    buttons.forEach(infosecbtn => {
        infosecbtn.addEventListener('click', () => {infosecbuttons.classList.remove('active');
            const dropdown = document.getElementById('dropdown');
            if (dropdown) dropdown.innerHTML = 'Jump to section';
        });
    });

    autohidetab();
    window.addEventListener('resize', autohidetab);
})

window.addEventListener('scroll', function() {
    let sections = document.querySelectorAll(".infosection");
    let buttons = {
        "overviewtab" : document.getElementById('overviewbutton'),
        "specialitiestab" : document.getElementById('specialitiesbutton'),
        "consultantstab" : document.getElementById('consultantsbutton'),
        "visitustab" : document.getElementById('visitusbutton'),
    }
    let currentactive = null;
    for (let i = 0; i < sections.length; i++) {
        let section = sections[i];
        let rect = section.getBoundingClientRect();
        if (rect.top <= 150 && rect.bottom >= 100) {currentactive = section.id; break;}
    }

    for (let key in buttons) {
        buttons[key].classList.remove('active');
    }
    if (currentactive) {
        buttons[currentactive].classList.add('active');
    }
})

function scrolltosection(event) {
    let sectionmap = {
        overviewbutton : "overviewtab",
        specialitiesbutton : "specialitiestab",
        consultantsbutton : "consultantstab",
        visitusbutton : "visitustab" 
    }
    let targetsection = document.getElementById(sectionmap[event.target.id]);
    if (targetsection) {
        let y = targetsection.getBoundingClientRect().top + window.pageYOffset + ((window.innerWidth < 850) ? - 275 : -20);
        window.scrollTo({top: y, behavior: "smooth"})
    }
}