document.addEventListener('DOMContentLoaded', () => {
    const searchForms = document.querySelectorAll('.cc-search-bar');

    searchForms.forEach((searchForm) => {
        const searchTriggers = searchForm.querySelectorAll('.cc-search-bar__trigger');

        searchTriggers.forEach((trigger) => {
            trigger.addEventListener('click', () => {
                searchForm.classList.toggle('active');
            });
        });
    });

    const mobileMenuToggle = document.querySelector('.cc-mobile-menu-toggle');
    const mobileMenuClose = document.querySelector('.cc-mobile-menu-close');
    const mobileMenu = document.querySelector('.cc-mobile-menu');
    const mobileMenuOverlay = document.querySelector('.cc-mobile-menu__overlay');
    const body = document.body;

    const openMobileMenu = () => {
        if (!mobileMenu) {
            return;
        }

        mobileMenu.classList.add('active');
        body.classList.add('mobile-menu-open');
    };

    const closeMobileMenu = () => {
        if (mobileMenu) {
            mobileMenu.classList.remove('active');
        }

        body.classList.remove('mobile-menu-open');
        searchForms.forEach((searchForm) => {
            searchForm.classList.remove('active');
        });
    };

    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', (event) => {
            event.preventDefault();
            openMobileMenu();
        });
    }

    if (mobileMenuClose) {
        mobileMenuClose.addEventListener('click', (event) => {
            event.preventDefault();
            closeMobileMenu();
        });
    }

    if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener('click', (event) => {
            event.preventDefault();
            closeMobileMenu();
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mobileMenu?.classList.contains('active')) {
            closeMobileMenu();
        }
    });

    const mobileMenuLinks = document.querySelectorAll('.cc-mobile-menu__link');
    mobileMenuLinks.forEach((link) => {
        link.addEventListener('click', closeMobileMenu);
    });
});
