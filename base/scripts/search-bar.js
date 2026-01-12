document.addEventListener("DOMContentLoaded", function () {
    // Search bar functionality
    const searchForms = document.querySelectorAll(".skl-search-bar");
    searchForms.forEach(function (searchForm) {
        const searchTrigger = searchForm.querySelectorAll(
            ".skl-search-bar__trigger"
        );

        searchTrigger.forEach(function (trigger) {
            trigger.addEventListener("click", function () {
                searchForm.classList.toggle("active");
            });
        });
    });

    // Mobile menu functionality
    const mobileMenuToggle = document.querySelector(".skl-mobile-menu-toggle");
    const mobileMenuClose = document.querySelector(".skl-mobile-menu-close");
    const mobileMenu = document.querySelector(".skl-mobile-menu");
    const mobileMenuOverlay = document.querySelector(
        ".skl-mobile-menu__overlay"
    );
    const body = document.body;

    function openMobileMenu() {
        mobileMenu.classList.add("active");
        body.classList.add("mobile-menu-open");
    }

    function closeMobileMenu() {
        mobileMenu.classList.remove("active");
        body.classList.remove("mobile-menu-open");
        searchForms.forEach(function (searchForm) {
            searchForm.classList.remove("active");
        });
    }

    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener("click", function (e) {
            e.preventDefault();
            openMobileMenu();
        });
    }

    if (mobileMenuClose) {
        mobileMenuClose.addEventListener("click", function (e) {
            e.preventDefault();
            closeMobileMenu();
        });
    }

    if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener("click", function (e) {
            e.preventDefault();
            closeMobileMenu();
        });
    }

    // Close mobile menu on escape key
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && mobileMenu?.classList.contains("active")) {
            closeMobileMenu();
        }
    });

    // Close mobile menu when clicking on menu links
    const mobileMenuLinks = document.querySelectorAll(".skl-mobile-menu__link");
    mobileMenuLinks.forEach(function (link) {
        link.addEventListener("click", function () {
            closeMobileMenu();
        });
    });
});
