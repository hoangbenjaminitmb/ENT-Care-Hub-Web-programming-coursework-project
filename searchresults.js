function swaptext2() {
    var showfilter = document.getElementById('showfilter');
    if (showfilter.innerHTML === 'Show Filters') {
        showfilter.innerHTML = 'Hide Filters';
    } else {
        showfilter.innerHTML = 'Show Filters';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const filtertab = document.querySelector('.filtertab'); 

    const showhiddentab = (showfilterbutton) => showfilterbutton.classList.toggle('active');
    const autohidetab = () => {
        filtertab.classList.remove('active');
        const showfilter = document.getElementById('showfilter');
        if (showfilter) showfilter.innerHTML = 'Show Filters';
    };

    const filtermenudropdown = document.querySelector('.filtermenudropdown');
    if (filtermenudropdown) filtermenudropdown.addEventListener('click', () => showhiddentab(filtertab));

    autohidetab();
    window.addEventListener('resize', autohidetab);
});

document.addEventListener('DOMContentLoaded', () => {
    const displayingtabs = document.querySelector('.displayingtabs');
    const filterby = document.querySelectorAll('input[name="sort"]');
    const resetfilters = document.querySelector('.resetfilterbutton');
    const filterbyavailability = document.querySelector('.filterbyavailability');

    const listedconsultants = [...displayingtabs.children];
    listedconsultants.forEach((child, index) => { child.dataset.ogindex = index; });

    const apply = () => {
        const sortby = [...filterby].find(button => button.checked)?.value;
        const availability = filterbyavailability.value;

        let filtered = listedconsultants.filter(consultant => {
            if (availability === 'available') {
                return consultant.dataset.available === '1';
            }
            return true;
        });

        const countdisplayedconsultants = document.getElementById('visiblecount');
        if (countdisplayedconsultants) countdisplayedconsultants.textContent = filtered.length;

        const options = {
            highestrating: (lowest , highest) => parseFloat(highest.dataset.rating) - parseFloat(lowest.dataset.rating),
            closestdistance: (lowest , highest) => parseFloat(lowest.dataset.distance) - parseFloat(highest.dataset.distance),
            lowestcost: (lowest , highest) => parseFloat(lowest.dataset.consultationfee) - parseFloat(highest.dataset.consultationfee)
        };

        if (sortby && options[sortby]) {
            filtered.sort(options[sortby]);
        } else {
            filtered.sort((lowest , highest) => lowest.dataset.ogindex - highest.dataset.ogindex);
        }

        displayingtabs.innerHTML = ''; 

        if (filtered.length === 0) {
            const noconsultantsfound = document.createElement('div');
            noconsultantsfound.className = 'noconsultantsfound';
            noconsultantsfound.innerHTML = `
                <div>There are no matching consultants for the filters you've selected</div>
                <img src="noresultfound.png">
                <div>Try de-selecting any options</div>
            `;
            displayingtabs.appendChild(noconsultantsfound);
        } else {
            filtered.forEach(c => displayingtabs.appendChild(c));
        }
    };

    resetfilters.addEventListener('click', () => {
        filterby.forEach(btn => btn.checked = false);
        filterbyavailability.value = 'all';
        apply();
    });

    filterby.forEach(btn => btn.addEventListener('change', apply));
    filterbyavailability.addEventListener('change', apply);
});