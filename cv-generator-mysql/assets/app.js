/* ══════════════════════════════════════════════════════════════════
   CV GENERATOR — app.js
   Handles: photo preview, form UX
══════════════════════════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', function () {

    // ── Photo upload preview ───────────────────────────────────────
    const photoInput   = document.getElementById('photoInput');
    const photoPreview = document.getElementById('photoPreview');

    if (photoInput && photoPreview) {

        photoInput.addEventListener('change', function () {
            const file = this.files[0];

            if (!file) return;

            // Validate size (10 Mo)
            if (file.size > 10 * 1024 * 1024) {
                alert('La photo ne doit pas dépasser 10 Mo.');
                this.value = '';
                return;
            }

            // Show preview
            const reader = new FileReader();

            reader.onload = function (e) {
                // Clear existing content and insert image
                photoPreview.innerHTML = '';

                const img    = document.createElement('img');
                img.src      = e.target.result;
                img.alt      = 'Aperçu photo';

                photoPreview.appendChild(img);
            };

            reader.readAsDataURL(file);
        });
    }

    // ── Smooth tab highlight on auth page ─────────────────────────
    const tabs = document.querySelectorAll('.tab');

    tabs.forEach(function (tab) {
        tab.addEventListener('mouseenter', function () {
            if (!this.classList.contains('active')) {
                this.style.opacity = '0.85';
            }
        });

        tab.addEventListener('mouseleave', function () {
            this.style.opacity = '';
        });
    });

    // ── Auto-dismiss alerts after 5 s ─────────────────────────────
    const alerts = document.querySelectorAll('.alert');

    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity    = '0';

            setTimeout(function () {
                alert.remove();
            }, 500);
        }, 5000);
    });

});
