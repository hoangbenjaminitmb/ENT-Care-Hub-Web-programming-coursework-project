document.getElementById("showfirst").click();

function switchtext(id, text1, text2) {
    var element = document.getElementById(id);
    element.innerHTML = (element.innerHTML === text1) ? text2 : text1;
}

function swaptext1() {switchtext('dropdown' , 'Jump to Section', 'Close')}
function swaptext2() {switchtext('showfilter' , 'Show Filters', 'Hide Filters')}

function showinfo(navbutton, tabname) {
    document.querySelectorAll(".details").forEach(detailstab => detailstab.style.display = "none");
    document.querySelectorAll(".navigablebutton").forEach(navigablebutton =>navigablebutton.classList.remove("active"));
    document.getElementById(tabname).style.display = "block";
    navbutton.currentTarget.className += " active";

    if (navbutton.isTrusted) {
        document.querySelector('.main-content').scrollIntoView({behavior: 'smooth'});
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const infonavigation = document.querySelector('.infonavigation');
    const filtertab = document.querySelector('.filtertab');
    const buttons = document.querySelectorAll('.infonavigation button');

    const showhiddentab = (element) => element.classList.toggle('active');
    const autohidetab = () => {
        [filtertab, infonavigation].forEach(element=> element.classList.remove('active'));
        const showmenus = {
            dropdown : 'Jump to Section', showfilter : 'Show Filters'
        };
        for (const [id, text] of Object.entries(showmenus)) {
            if (document.getElementById(id)) document.getElementById(id).innerHTML = text;
        }
    };

    document.querySelector('.dropdownmenu').addEventListener('click', () => showhiddentab(infonavigation));
    document.querySelector('.filtermenudropdown').addEventListener('click', () => showhiddentab(filtertab));

    document.querySelector('.clinicinfobutton').addEventListener('click', () => {
        const clinicinfobtn = document.querySelector("[onclick=\"showinfo(event, 'clinicinfo')\"]")
        if (clinicinfobtn) clinicinfobtn.click()
    });

    document.querySelector('.reviewsbutton').addEventListener('click', () => {
        const reviewsbtn = document.querySelector("[onclick=\"showinfo(event, 'reviews')\"]")
        if (reviewsbtn) reviewsbtn.click()
    });

    buttons.forEach(navbutton => {
        navbutton.addEventListener('click', () => {infonavigation.classList.remove('active');
            const dropdown = document.getElementById('dropdown');
            if (dropdown) dropdown.innerHTML = 'Jump to Section';
        });
    });

    autohidetab();
    window.addEventListener('resize', autohidetab);
});

const ogreviews = [...document.querySelectorAll('#reviews .customerreview')];
const reviewcontainer = document.querySelector('#reviews .listofreviews');

function fetchreviews(reviews) {
    reviewcontainer.querySelectorAll('.customerreview, .noreviewsfound').forEach(element => element.remove());
    const countdisplayedreviews = document.getElementById('visiblecount');
    if (countdisplayedreviews) countdisplayedreviews.textContent = reviews.length;

    if (!reviews.length) {
        const noreviewsfoundmessage = document.createElement('div');
        noreviewsfoundmessage.className = 'noreviewsfound';
        noreviewsfoundmessage.innerHTML = `
            <div>There are no matching reviews for the filters you've selected</div>
            <img src="missingreview.png">
            <div>Try de-selecting any options</div>
        `;
        reviewcontainer.appendChild(noreviewsfoundmessage);
    } else {
        reviews.forEach(review => reviewcontainer.appendChild(review));
    }
}

function updatereviews() {
    const sort = document.querySelector('input[name="sort"]:checked')?.value;
    const filters = [...document.querySelectorAll('.recommendfilter:checked')].map(checkbox => checkbox.value);

    let filtered = ogreviews.filter(review => !filters.length || filters.includes(review.dataset.recommend));

    if (sort) {
        filtered.sort((highest, lowest) =>
            parseFloat(sort === 'highest' ? lowest.dataset.rating : highest.dataset.rating) -
            parseFloat(sort === 'highest' ? highest.dataset.rating : lowest.dataset.rating)
        );
    } fetchreviews(filtered);
}

document.querySelectorAll('.recommendfilter, input[name="sort"]').forEach(sortfilter => sortfilter.addEventListener('change', updatereviews));

document.querySelector('.resetfilterbutton').addEventListener('click', () => {
    document.querySelectorAll('.select, input[name="sort"], .recommendfilter:checked').forEach(sortfilter => sortfilter.checked = false);
    fetchreviews(ogreviews);
});

const scrollcontainer = document.querySelector('.otherconsultantinfo');

function clicktoscroll(amount) {
    scrollcontainer.scrollBy({ left: amount, behavior: 'smooth' })
}
document.querySelector('.leftbutton').addEventListener('click', () => clicktoscroll(-600));
document.querySelector('.rightbutton').addEventListener('click', () => clicktoscroll(600));

async function convertlonglattoaddress(lat, lng, callback) {
    try {
        const reversegeocode = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
        const data = await reversegeocode.json();
        if (data && data.display_name) {
            const fulladdress = [data.address.road || "" , data.address.city || data.address.county || "", data.address.postcode || ""].filter(Boolean).join(", ");
            callback(fulladdress);
        } else {
            callback("N/A");
        }
    } catch(error) {
        callback("N/A");
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const latitude = parseFloat(document.getElementById("clinicinfo").dataset.latitude);
    const longitude = parseFloat(document.getElementById("clinicinfo").dataset.longitude);
    convertlonglattoaddress(latitude, longitude, function(address) {
        document.getElementById("clinicaddress").textContent = "Address: " + address;
    });
});

const currentmonth = document.getElementById('currentmonth');
const calendargrid = document.getElementById('calendar-grid');

let currentday;
if (selectedDate) {
    currentday = new Date(selectedDate);
} else {
    currentday = new Date();
}

function fetchcalendar() {
    const year = currentday.getFullYear();
    const month = currentday.getMonth();
    const firstmonthday = new Date(year, month, 1);
    const lastmonthday = new Date(year, month + 1, 0);
    const thedaysinmonth = lastmonthday.getDate();

    let firstdow = (firstmonthday.getDay() + 6) % 7;  

    currentmonth.textContent = firstmonthday.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
    calendargrid.innerHTML = '';

    ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].forEach(day => {
        const daydiv = document.createElement('div');
        daydiv.classList.add('weekday');
        daydiv.textContent = day;
        calendargrid.appendChild(daydiv);
    });
    
    for (let i = 0; i < firstdow; i++) {
        calendargrid.appendChild(document.createElement('div'));
    }

    for (let day = 1; day <= thedaysinmonth; day++) {
        const daydiv = document.createElement('div');
        daydiv.classList.add('notavailableonthatday')
        daydiv.textContent = day;

        const m = month + 1;
        const dateStr = year + '-' + (m < 10 ? '0' + m : m) + '-' + (day < 10 ? '0' + day : day);
        const adjustedday = (new Date(year, month, day).getDay() + 6) % 7;
        
        if (availableweekdays.includes(adjustedday)) daydiv.classList.add('bookingavailable');
        if (alreadybooked.includes(dateStr)) daydiv.classList.add('alreadybooked');
        if (selectedDate === dateStr) daydiv.classList.add('selecteddate')  
        calendargrid.appendChild(daydiv);
    }
}

function changemonth(increment) {
    currentday.setMonth(currentday.getMonth() + increment);
    fetchcalendar();
}

document.getElementById('prevmonth').addEventListener('click', () => {changemonth(-1)});
document.getElementById('nextmonth').addEventListener('click', () => {changemonth(1)});

fetchcalendar();