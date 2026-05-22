const menuToggle = document.querySelector('.menu-toggle');
const sidebar = document.querySelector('.sidebar');
const closeSidebar = document.querySelector('.close-sidebar');
const overlay = document.getElementById('overlay');
const sidebarLinks = document.querySelectorAll('.sidebar-nav a');

function openSidebar() {    
    sidebar.classList.add('active');
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden'; 
}
function closeSidebarMenu() {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
    document.body.style.overflow = ''; 
}

if (menuToggle) {
    menuToggle.addEventListener('click', openSidebar);
}

if (closeSidebar) {
    closeSidebar.addEventListener('click', closeSidebarMenu);
}

if (overlay) {
    overlay.addEventListener('click', closeSidebarMenu);
}

sidebarLinks.forEach(link => {
    link.addEventListener('click', () => {
        closeSidebarMenu();
    });
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && sidebar.classList.contains('active')) {
        closeSidebarMenu();
    }
});