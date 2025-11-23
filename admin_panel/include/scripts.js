document.addEventListener('DOMContentLoaded', () => {
    const menuBtn = document.querySelector('.js-menu-toggle');
    const sidebar = document.querySelector('.sidebar');

    if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar--open');
        });

        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                sidebar.classList.remove('sidebar--open');
            }
        });
    }
});
