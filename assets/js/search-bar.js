document.addEventListener('DOMContentLoaded', () => {
    const searchForms = document.querySelectorAll('.cc-search-bar');

    const setSearchState = (searchForm, isOpen) => {
        searchForm.classList.toggle('active', isOpen);
        searchForm
            .querySelectorAll('.cc-search-bar__trigger')
            .forEach((trigger) => trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false'));
    };

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
        searchForms.forEach((searchForm) => setSearchState(searchForm, false));
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('.cc-search-bar__trigger');

        if (trigger) {
            const searchForm = trigger.closest('.cc-search-bar');

            if (!searchForm) {
                return;
            }

            event.preventDefault();
            setSearchState(searchForm, !searchForm.classList.contains('active'));

            return;
        }

        if (event.target.closest('.cc-mobile-menu__link')) {
            closeMobileMenu();
        }
    });

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

});
