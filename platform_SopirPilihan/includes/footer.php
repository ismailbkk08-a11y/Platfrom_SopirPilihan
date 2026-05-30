<footer>
    <div style="max-width: 1200px; margin: 0 auto;">
        <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">SopirPilihan.id</h3>
        <p style="margin-bottom: 0.5rem;">Solusi Transportasi Pribadi Anti-Ribet</p>
        <p style="margin-bottom: 1.5rem; opacity: 0.8;">Platform gratis tanpa biaya atau komisi untuk menghubungkan driver dan pelanggan</p>
        
        <div style="display: flex; gap: 2rem; justify-content: center; margin-bottom: 1.5rem; flex-wrap: wrap;">
            <a href="<?php echo $base_url; ?>pages/tentang.php">Tentang Kami</a>
            <a href="<?php echo $base_url; ?>pages/syarat-ketentuan.php">Syarat & Ketentuan</a>
            <a href="<?php echo $base_url; ?>pages/kebijakan-privasi.php">Kebijakan Privasi</a>
            <a href="<?php echo $base_url; ?>pages/kontak.php">Kontak</a>
        </div>
        
        <p style="opacity: 0.8;">&copy; <?php echo date('Y'); ?> SopirPilihan.id. All rights reserved.</p>
    </div>
</footer>

<!-- Scripts -->
<script>
    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if(target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.driver-card, .card, .section, .stat-card').forEach(el => {
        observer.observe(el);
    });
</script>
</body>
</html>