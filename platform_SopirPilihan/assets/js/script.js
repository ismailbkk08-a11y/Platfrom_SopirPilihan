// Navigasi Efek Scroll
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.style.backgroundColor = '#ffffff';
        navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
    } else {
        navbar.style.backgroundColor = 'transparent';
        navbar.style.boxShadow = 'none';
    }
});

// Konfirmasi Global untuk Aksi Berbahaya (Hapus)
const confirmActions = document.querySelectorAll('.btn-hapus');
confirmActions.forEach(button => {
    button.addEventListener('click', function(e) {
        if (!confirm('Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.')) {
            e.preventDefault();
        }
    });
});

// Preview Foto Profil sebelum Upload
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Auto-close Alert/Notifikasi
const alerts = document.querySelectorAll('.alert-msg');
alerts.forEach(alert => {
    setTimeout(() => {
        alert.style.display = 'none';
    }, 3000);
});