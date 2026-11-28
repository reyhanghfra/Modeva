// Modeva Admin Dashboard Scripts (Vanilla JS)
document.addEventListener('DOMContentLoaded', () => {
    const mobileToggleBtn = document.getElementById('adminMobileToggle');
    const sidebarCloseBtn = document.getElementById('adminSidebarCloseBtn');
    const sidebarBackdrop = document.getElementById('adminSidebarBackdrop');
    const sidebar = document.getElementById('adminSidebar');

    if (!sidebar) {
        return;
    }

    const openSidebar = () => {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
        if (sidebarBackdrop) {
            sidebarBackdrop.classList.remove('opacity-0', 'pointer-events-none');
            sidebarBackdrop.classList.add('opacity-100', 'pointer-events-auto');
        }
        document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
    };

    const closeSidebar = () => {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        if (sidebarBackdrop) {
            sidebarBackdrop.classList.remove('opacity-100', 'pointer-events-auto');
            sidebarBackdrop.classList.add('opacity-0', 'pointer-events-none');
        }
        document.body.classList.remove('overflow-hidden', 'lg:overflow-auto');
    };

    if (mobileToggleBtn) {
        mobileToggleBtn.addEventListener('click', openSidebar);
    }

    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', closeSidebar);
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', closeSidebar);
    }

    // Tutup sidebar saat menekan tombol Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });

    // Lepas kunci scroll saat resize ke desktop breakpoint (1024px)
    const adminMediaQuery = window.matchMedia('(min-width: 1024px)');
    const handleAdminBreakpoint = (e) => {
        if (e.matches) {
            closeSidebar();
        }
    };
    if (adminMediaQuery.addEventListener) {
        adminMediaQuery.addEventListener('change', handleAdminBreakpoint);
    } else {
        adminMediaQuery.addListener(handleAdminBreakpoint);
    }

    // Lepas kunci scroll saat event pageshow (kembali via tombol back browser)
    window.addEventListener('pageshow', () => {
        closeSidebar();
    });
});
