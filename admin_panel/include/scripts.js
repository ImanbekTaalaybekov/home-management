document.addEventListener('DOMContentLoaded', () => {
    const menuBtn = document.querySelector('.js-menu-toggle');
    const sidebar = document.querySelector('.sidebar');

    if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('sidebar--open');
        });

        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                sidebar.classList.remove('sidebar--open');
            }
        });
    }

    document.querySelectorAll('.sidebar__group-header').forEach(header => {
        header.addEventListener('click', function (e) {
            e.stopPropagation();
            const group = this.closest('.sidebar__group');
            group.classList.toggle('sidebar__group--open');
        });
    });

    const activeItem = document.querySelector('.sidebar__item--active');
    if (activeItem) {
        const parentGroup = activeItem.closest('.sidebar__group');
        if (parentGroup) {
            parentGroup.classList.add('sidebar__group--open');
        }
    }

    document.querySelectorAll('.sidebar__item').forEach(item => {
        item.addEventListener('click', () => {
            if (window.innerWidth <= 992 && sidebar) {
                sidebar.classList.remove('sidebar--open');
            }
        });
    });
});