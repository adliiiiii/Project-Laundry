    <!-- ===== FOOTER ===== -->
    <div class="footer-wrapper scroll-animate">
        <div class="footer">
            <div class="footer-brand">
                <span>🧺</span>
                <span class="brand-name">White Clean</span>
                <span>|</span>
                <span>&copy; <?= date('Y') ?> All Rights Reserved</span>
            </div>
            <div class="footer-links">
                <a href="#hero-section">Dashboard</a>
                <a href="#pesanan-section">Pesanan</a>
                <a href="#customer-section">Customer</a>
                <a href="#kurir-section">Kurir</a>
                <a href="#layanan-section">Layanan</a>
            </div>
        </div>
    </div>
</div><!-- end .container -->

<script>
// ===== MODAL FUNCTIONS =====
function bukaModal(id) {
    document.getElementById(id).classList.add('active');
}

function tutupModal(id) {
    document.getElementById(id).classList.remove('active');
}

document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });
});

// ============================================
// NAVBAR SCROLL EFFECT & ACTIVE SECTION
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const headerWrapper = document.getElementById('headerWrapper');
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('.page-section, #hero-section, #stats-section');

    // ===== SCROLL EFFECT NAVBAR =====
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
        
        // Navbar background effect
        if (currentScroll > 50) {
            headerWrapper.classList.add('scrolled');
        } else {
            headerWrapper.classList.remove('scrolled');
        }

        // ===== ACTIVE SECTION DETECTION =====
        let currentSection = '';

        sections.forEach(section => {
            const sectionTop = section.offsetTop - 120;
            const sectionBottom = sectionTop + section.offsetHeight;

            if (currentScroll >= sectionTop && currentScroll < sectionBottom) {
                currentSection = section.id;
            }
        });

        // Jika di paling atas, aktifkan dashboard
        if (currentScroll < 100) {
            currentSection = 'hero-section';
        }

        // Update active class pada nav
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + currentSection) {
                link.classList.add('active');
            }
        });
    });

    // ===== SMOOTH SCROLL NAV =====
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const offsetTop = targetSection.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });

                // Update active class
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

    // ===== SCROLL ANIMATION =====
    const animateElements = document.querySelectorAll('.scroll-animate');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { 
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    animateElements.forEach(el => observer.observe(el));

    // Trigger scroll detection once on load
    setTimeout(() => {
        window.dispatchEvent(new Event('scroll'));
    }, 100);
});
</script>
</body>
</html>