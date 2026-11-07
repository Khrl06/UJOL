/**
 * SmartExam AI - Frontend Interactions & Utilities
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Sidebar Toggle Management
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  const contentWrapper = document.getElementById('content-wrapper');
  let backdrop = document.querySelector('.sidebar-backdrop');

  // Create mobile backdrop if not present
  if (!backdrop && sidebar) {
    backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    document.body.appendChild(backdrop);
  }

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', (e) => {
      e.preventDefault();
      if (window.innerWidth < 992) {
        // Mobile behavior: toggle offcanvas drawer
        sidebar.classList.toggle('show-mobile');
        if (backdrop) {
          backdrop.classList.toggle('show');
        }
      } else {
        // Desktop behavior: collapse sidebar
        sidebar.classList.toggle('toggled');
      }
    });
  }

  // Close mobile sidebar when clicking backdrop
  if (backdrop) {
    backdrop.addEventListener('click', () => {
      sidebar.classList.remove('show-mobile');
      backdrop.classList.remove('show');
    });
  }

  // 2. Real-time Clock in Navbar
  const liveClockEl = document.getElementById('liveClock');
  if (liveClockEl) {
    const updateClock = () => {
      const now = new Date();
      const options = {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
      };
      liveClockEl.textContent = now.toLocaleDateString('id-ID', options) + ' WIB';
    };
    updateClock();
    setInterval(updateClock, 1000);
  }

  // 3. Auto-dismiss Flash Alerts
  const alerts = document.querySelectorAll('.alert-dismissible');
  alerts.forEach((alert) => {
    setTimeout(() => {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      if (bsAlert) {
        bsAlert.close();
      }
    }, 6000);
  });

  // 4. Initialize Bootstrap Tooltips & Popovers
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl));

  // 5. Password Visibility Toggle
  const togglePasswordBtn = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');
  if (togglePasswordBtn && passwordInput) {
    togglePasswordBtn.addEventListener('click', () => {
      const isPassword = passwordInput.type === 'password';
      passwordInput.type = isPassword ? 'text' : 'password';
      const icon = togglePasswordBtn.querySelector('i');
      if (icon) {
        icon.classList.toggle('bi-eye', !isPassword);
        icon.classList.toggle('bi-eye-slash', isPassword);
      }
    });
  }
});
