/**
 * SopirPilihan.id - Form Validation
 */

// Validasi Nomor WhatsApp (Hanya angka, minimal 10 digit)
function validateWhatsApp(no) {
    const regex = /^[0-9]{10,15}$/;
    return regex.test(no);
}

document.addEventListener('DOMContentLoaded', function() {
    const authForm = document.querySelector('form');

    if (authForm) {
        authForm.addEventListener('submit', function(e) {
            let isValid = true;
            let errorMessage = "";

            // 1. Validasi WhatsApp jika ada field no_wa
            const waInput = document.querySelector('input[name="no_wa"]');
            if (waInput && !validateWhatsApp(waInput.value)) {
                isValid = false;
                errorMessage += "Nomor WhatsApp harus berupa angka (10-15 digit).\n";
            }

            // 2. Validasi Password (Minimal 6 karakter)
            const passwordInput = document.querySelector('input[name="password"]');
            if (passwordInput && passwordInput.value.length < 6) {
                isValid = false;
                errorMessage += "Password minimal harus 6 karakter.\n";
            }

            // 3. Validasi Ekstensi Gambar (Khusus upload mobil/profil)
            const fileInput = document.querySelector('input[type="file"]');
            if (fileInput && fileInput.files.length > 0) {
                const filePath = fileInput.value;
                const allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;
                if (!allowedExtensions.exec(filePath)) {
                    isValid = false;
                    errorMessage += "Format file harus .jpg, .jpeg, atau .png.\n";
                }
            }

            // Jika ada error, hentikan submit dan munculkan alert
            if (!isValid) {
                e.preventDefault();
                alert("Terjadi Kesalahan:\n" + errorMessage);
            }
        });
    }
});