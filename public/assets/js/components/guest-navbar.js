/**
 * Guest Navbar Component JavaScript
 * Handles mobile menu toggle and user profile dropdown
 */

document.addEventListener('DOMContentLoaded', function() {
    const navbarToggleBtn = document.getElementById('navbarToggleBtn');
    const navbarMenu = document.querySelector('.navbar-menu');
    const userProfileBtn = document.getElementById('userProfileBtn');
    const userProfileDropdown = document.querySelector('.user-profile-dropdown');
    const userProfileMenu = document.getElementById('userProfileMenu');

    // Mobile Menu Toggle
    if (navbarToggleBtn && navbarMenu) {
        navbarToggleBtn.addEventListener('click', function() {
            // Toggle class 'active' pada container utama navbar
            document.querySelector('.guest-navbar').classList.toggle('active');
        });
        
        // Close menu when clicking a link
        const navLinks = navbarMenu.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                document.querySelector('.guest-navbar').classList.remove('active');
            });
        });
    }

    // User Profile Dropdown Toggle
    if (userProfileBtn && userProfileDropdown) {
        userProfileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userProfileDropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userProfileDropdown.contains(e.target)) {
                userProfileDropdown.classList.remove('active');
            }
        });

        // Close dropdown when clicking on menu item
        if (userProfileMenu) {
            const menuItems = userProfileMenu.querySelectorAll('.profile-menu-item');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    userProfileDropdown.classList.remove('active');
                });
            });
        }
    }

    // Navbar scroll effect
    let lastScroll = 0;
    const navbar = document.querySelector('.guest-navbar');
    
    if (navbar) {
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 50) {
                navbar.classList.add('scrolled');
                navbar.querySelector('.navbar-content').style.background = 'rgba(15, 23, 42, 0.9)';
            } else {
                navbar.classList.remove('scrolled');
                navbar.querySelector('.navbar-content').style.background = 'rgba(15, 23, 42, 0.7)';
            }
            
            lastScroll = currentScroll;
        });
    }
});

