document.addEventListener('DOMContentLoaded', () => {
    const wrappers = document.querySelectorAll('.consultantswrapper');

    wrappers.forEach(wrapper => {
        const scrollContainer = wrapper.querySelector('.consultantinfosection');
        const leftbutton = wrapper.querySelector('.leftbutton');
        const rightbutton = wrapper.querySelector('.rightbutton');

        function clicktoscroll(amount) {
            scrollContainer.scrollBy({ left: amount, behavior: 'smooth' })
        }

        function showscrollbutton() {
            if (window.innerWidth < 768) {
                leftbutton.style.display = 'none';
                rightbutton.style.display = 'none';
                return;
            }

            if (scrollContainer.scrollWidth > scrollContainer.clientWidth) {
                leftbutton.style.display = 'block';
                rightbutton.style.display = 'block';
            } else {
                leftbutton.style.display = 'none';
                rightbutton.style.display = 'none';
            }
        }

        if (scrollContainer && leftbutton && rightbutton) {
            leftbutton.addEventListener('click', () => {clicktoscroll(-600);});
            rightbutton.addEventListener('click', () => {clicktoscroll(600);});

            showscrollbutton();
            window.addEventListener('resize', showscrollbutton);
        }
    });
});

document.querySelectorAll('.specialitysectionsheader').forEach(header => {
    const showconsultantsection = header.querySelector('button');
    const consultantsection = header.closest('.specialitysections').querySelector('.consultantsection');

    if (!consultantsection) return;

    consultantsection.classList.remove('visible');

    header.addEventListener('click', () => {
        togglesection(showconsultantsection, consultantsection)
    });
});

function togglesection(showconsultantsection, consultantsection) {
    if (consultantsection.classList.contains ('visible')) {
        consultantsection.classList.remove ('visible');
        showconsultantsection.textContent = '⯆';
    } else {
        consultantsection.classList.add ('visible');
        showconsultantsection.textContent = '⯅';
    }
}