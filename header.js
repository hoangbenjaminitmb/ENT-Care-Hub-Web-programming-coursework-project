const togglesidebar = document.querySelector('.sidebar-toggle');
const sidebar = document.querySelector('.sidebar');
const body = document.body;

togglesidebar.addEventListener('click', () => {
    sidebar.classList.toggle('hidden');
    sidebar.classList.toggle('visible');
    document.documentElement.scrollTop = 0;

    if (sidebar.classList.contains('visible')) {
        body.classList.add('no-scroll');
    } else {
        body.classList.remove('no-scroll')
    }
});

function handleResize() {
    if (window.innerWidth > 768) {
        sidebar.classList.remove('hidden');
        sidebar.classList.add('visible');
        body.classList.remove('no-scroll')
    } else {
        sidebar.classList.remove('visible');
        sidebar.classList.add('hidden');
        body.classList.remove('no-scroll')
    }
}
window.addEventListener('DOMContentLoaded', handleResize);
window.addEventListener('resize', handleResize);