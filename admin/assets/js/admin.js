/**
 * JavaScript Admin - Portfolio Hery LEBEHOT
 */

(function () {
  "use strict";

  // Toast notification
  function showToast(message, type = "success") {
    const container = document.getElementById("toast-container");
    if (!container) return;

    const toast = document.createElement("div");
    toast.className = `toast toast--${type}`;

    const icon =
      type === "success"
        ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>'
        : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';

    toast.innerHTML = `${icon}<span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.animation = "slideIn 0.3s ease reverse";
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  // Auto-generate slug from title
  const titleFrInput = document.getElementById("title_fr");
  const slugInput = document.getElementById("slug");

  if (titleFrInput && slugInput && !slugInput.value) {
    titleFrInput.addEventListener("input", function () {
      const slug = this.value
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "");
      slugInput.value = slug;
    });
  }

  // Confirm delete
  document.querySelectorAll('a[href*="action=delete"]').forEach((link) => {
    link.addEventListener("click", function (e) {
      if (!confirm("Êtes-vous sûr de vouloir supprimer cet élément ?")) {
        e.preventDefault();
      }
    });
  });

  // Preview d'image avant upload
  document
    .querySelectorAll('input[type="file"][accept*="image"]')
    .forEach((input) => {
      input.addEventListener("change", function (e) {
        const file = e.target.files[0];
        if (file && file.type.startsWith("image/")) {
          const reader = new FileReader();
          reader.onload = function (e) {
            const preview = document.createElement("img");
            preview.src = e.target.result;
            preview.style.maxWidth = "200px";
            preview.style.marginTop = "1rem";
            preview.style.borderRadius = "0.375rem";

            const existing =
              input.parentElement.querySelector("img:last-child");
            if (existing && !existing.closest(".current-image")) {
              existing.remove();
            }

            input.parentElement.appendChild(preview);
          };
          reader.readAsDataURL(file);
        }
      });
    });

  console.log("✅ Admin JS chargé");
})();
