/**
 * =====================================================
 * PORTFOLIO HERY LEBEHOT
 * JavaScript Front-End
 * =====================================================
 */

(function () {
  "use strict";

  // =====================================================
  // CONFIGURATION & VARIABLES GLOBALES
  // =====================================================

  const CONFIG = {
    API_BASE: "/Portfolio/api",
    LANG_KEY: "portfolio_lang",
    DEFAULT_LANG: "fr",
  };

  const SITE_BASE = CONFIG.API_BASE.replace(/\/api\/?$/, "");

  function resolveAssetUrl(path) {
    if (!path) return "";
    if (/^(https?:)?\/\//.test(path)) return path;
    const base = path.startsWith("/") ? SITE_BASE + path : path;
    return base + "?v=3";
  }

  let currentLang =
    sessionStorage.getItem(CONFIG.LANG_KEY) || CONFIG.DEFAULT_LANG;
  let translations = {};
  let profileData = {};
  let languagesData = [];
  let projectsData = [];
  let educationData = [];

  // =====================================================
  // UTILITAIRES
  // =====================================================

  /**
   * Requête API générique
   */
  async function fetchAPI(endpoint) {
    try {
      const url = `${CONFIG.API_BASE}${endpoint}`;
      console.log("[fetchAPI] Fetching:", url);
      const response = await fetch(url);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Erreur API:", error);
      showToast("Erreur de chargement des données", "error");
      return null;
    }
  }

  /**
   * Affiche une notification toast
   */
  function showToast(message, type = "success") {
    const container = document.getElementById("toast-container");
    const toast = document.createElement("div");
    toast.className = `toast toast--${type}`;

    const icon =
      type === "success"
        ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>'
        : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';

    toast.innerHTML = `${icon}<span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.animation = "slideUp 0.3s ease reverse";
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  /**
   * Traduction des textes
   */
  function translate(key) {
    const keys = key.split(".");
    let value = translations[currentLang];

    for (const k of keys) {
      if (value && value[k]) {
        value = value[k];
      } else {
        return key;
      }
    }

    return value || key;
  }

  /**
   * Met à jour tous les textes traduisibles
   */
  function updateTranslations() {
    document.querySelectorAll("[data-i18n]").forEach((element) => {
      const key = element.getAttribute("data-i18n");
      const translation = translate(key);

      if (element.tagName === "INPUT" || element.tagName === "TEXTAREA") {
        element.placeholder = translation;
      } else {
        element.textContent = translation;
      }
    });

    // Met à jour l'attribut lang du document
    document.documentElement.lang = currentLang;
  }

  /**
   * Animation des compteurs (statistiques)
   */
  function animateCounter(element, target, duration = 1000) {
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;

    const timer = setInterval(() => {
      current += increment;
      if (current >= target) {
        element.textContent = target;
        clearInterval(timer);
      } else {
        element.textContent = Math.floor(current);
      }
    }, 16);
  }

  /**
   * Détecte si un élément est visible dans le viewport
   */
  function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
      rect.top >= 0 &&
      rect.left >= 0 &&
      rect.bottom <=
        (window.innerHeight || document.documentElement.clientHeight) &&
      rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
  }

  // =====================================================
  // NAVIGATION
  // =====================================================

  function initNavigation() {
    const header = document.getElementById("header");
    const navToggle = document.getElementById("nav-toggle");
    const navClose = document.getElementById("nav-close");
    const navMenu = document.getElementById("nav-menu");
    const navLinks = document.querySelectorAll(".nav__link");

    // Scroll header effect
    window.addEventListener("scroll", () => {
      if (window.scrollY > 50) {
        header.classList.add("scrolled");
      } else {
        header.classList.remove("scrolled");
      }
    });

    // Toggle menu mobile
    if (navToggle) {
      navToggle.addEventListener("click", () => {
        navMenu.classList.add("active");
      });
    }

    if (navClose) {
      navClose.addEventListener("click", () => {
        navMenu.classList.remove("active");
      });
    }

    // Fermer le menu lors du clic sur un lien
    navLinks.forEach((link) => {
      link.addEventListener("click", () => {
        navMenu.classList.remove("active");

        // Active link
        navLinks.forEach((l) => l.classList.remove("active"));
        link.classList.add("active");
      });
    });

    // Active link on scroll
    const sections = document.querySelectorAll("section[id]");

    window.addEventListener("scroll", () => {
      const scrollY = window.pageYOffset;

      sections.forEach((section) => {
        const sectionHeight = section.offsetHeight;
        const sectionTop = section.offsetTop - 100;
        const sectionId = section.getAttribute("id");

        if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
          document
            .querySelector(`.nav__link[href*="${sectionId}"]`)
            ?.classList.add("active");
        } else {
          document
            .querySelector(`.nav__link[href*="${sectionId}"]`)
            ?.classList.remove("active");
        }
      });
    });
  }

  // =====================================================
  // GESTION DES LANGUES
  // =====================================================

  function initLanguageToggle() {
    const langToggle = document.getElementById("lang-toggle");
    const langOptions = document.querySelectorAll(".lang__option");

    // Définir la langue active au chargement
    updateActiveLang();

    langOptions.forEach((option) => {
      option.addEventListener("click", async () => {
        const lang = option.getAttribute("data-lang");
        if (lang !== currentLang) {
          currentLang = lang;
          sessionStorage.setItem(CONFIG.LANG_KEY, lang);
          updateActiveLang();
          await loadTranslations(); // Reload translations from API
          updateDynamicContent();
        }
      });
    });

    function updateActiveLang() {
      langOptions.forEach((option) => {
        if (option.getAttribute("data-lang") === currentLang) {
          option.classList.add("active");
        } else {
          option.classList.remove("active");
        }
      });
    }
  }

  // =====================================================
  // GESTION DU MODE CLAIR/SOMBRE
  // =====================================================

  function initThemeToggle() {
    const themeToggle = document.getElementById("theme-toggle");
    const htmlElement = document.documentElement;
    const THEME_KEY = "portfolio_theme";

    // Récupérer la préférence sauvegardée ou la préférence système
    const savedTheme = localStorage.getItem(THEME_KEY);
    const prefersDark = window.matchMedia(
      "(prefers-color-scheme: dark)",
    ).matches;
    const initialTheme = savedTheme || (prefersDark ? "dark" : "dark"); // Défaut: sombre

    // Appliquer le thème initial
    setTheme(initialTheme);

    // Ajouter un écouteur sur le bouton
    themeToggle.addEventListener("click", () => {
      const currentTheme = htmlElement.classList.contains("light-mode")
        ? "light"
        : "dark";
      const newTheme = currentTheme === "light" ? "dark" : "light";
      setTheme(newTheme);
    });

    function setTheme(theme) {
      if (theme === "light") {
        htmlElement.classList.add("light-mode");
      } else {
        htmlElement.classList.remove("light-mode");
      }
      localStorage.setItem(THEME_KEY, theme);
    }
  }

  /**
   * Charge les traductions depuis la base de données
   */
  async function loadTranslations() {
    const data = await fetchAPI("/contents.php");
    if (data && data.success) {
      translations = data.translations || { fr: {}, en: {} };
      updateTranslations();
    } else {
      // Log error and show toast for debugging
      console.error(
        "[loadTranslations] Problème de chargement des traductions:",
        data,
      );
      showToast("Erreur de chargement des traductions", "error");
      // Prevent further errors by setting empty translations
      translations = { fr: {}, en: {} };
      updateTranslations();
    }
  }

  // =====================================================
  // CHARGEMENT DU PROFIL
  // =====================================================

  async function loadProfile() {
    const data = await fetchAPI("/profile.php");
    if (data && data.success) {
      profileData = data.profile;
      updateProfileUI();
    }
  }

  function updateProfileUI() {
    // Liens sociaux
    const githubLinks = document.querySelectorAll(
      "#link-github, #contact-github",
    );
    githubLinks.forEach((link) => {
      if (profileData.github) {
        link.href = profileData.github;
      }
    });

    const linkedinLinks = document.querySelectorAll(
      "#link-linkedin, #contact-linkedin",
    );
    linkedinLinks.forEach((link) => {
      if (profileData.linkedin) {
        link.href = profileData.linkedin;
      }
    });

    const emailLinks = document.querySelectorAll("#link-email, #contact-email");
    emailLinks.forEach((link) => {
      if (profileData.email) {
        link.href = `mailto:${profileData.email}`;
        if (link.id === "contact-email") {
          link.textContent = profileData.email;
        }
      }
    });

    // Gestion de l'avatar dans la cible
    const avatarImage = document.getElementById("avatar-image");
    if (avatarImage && profileData.avatar_path && profileData.avatar_centered) {
      avatarImage.onerror = () => {
        avatarImage.style.display = "none";
      };
      avatarImage.src = resolveAssetUrl(profileData.avatar_path);
      avatarImage.alt = profileData.name || "Avatar";
      avatarImage.style.display = "block";
    } else if (avatarImage) {
      avatarImage.style.display = "none";
    }

    // Gestion du CV
    const cvBtn = document.getElementById("download-cv-btn");
    if (cvBtn && profileData.cv_path) {
      cvBtn.style.opacity = "1";
      cvBtn.style.pointerEvents = "auto";
      cvBtn.addEventListener("click", (e) => {
        e.preventDefault();

        // Télécharger via l'API
        const link = document.createElement("a");
        link.href = `${CONFIG.API_BASE}/download-cv.php`;
        link.download = "CV.pdf";
        link.style.display = "none";

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showToast(
          translate("cv.downloaded") || "Téléchargement en cours...",
          "success",
        );
      });
    } else if (cvBtn && !profileData.cv_path) {
      // Si pas de CV, désactiver le bouton
      cvBtn.style.opacity = "0.5";
      cvBtn.style.pointerEvents = "none";
      cvBtn.title = "CV non disponible";
    }

    // Mise à jour des textes dynamiques si nécessaire
    if (profileData.tagline_fr && profileData.tagline_en) {
      const tagline = document.querySelector(".hero__tagline");
      if (tagline) {
        tagline.textContent =
          currentLang === "fr"
            ? profileData.tagline_fr
            : profileData.tagline_en;
      }
    }
  }

  // =====================================================
  // CLAVIER DES COMPÉTENCES
  // =====================================================

  async function loadLanguages() {
    const data = await fetchAPI("/languages.php");
    if (data && data.success) {
      languagesData = data.languages || [];
      buildKeyboard();

      // Animer le compteur de langages
      const statLang = document.getElementById("stat-languages");
      if (statLang) {
        animateCounter(statLang, languagesData.length);
      }
    }
  }

  /**
   * Construit le clavier des compétences
   */
  function buildKeyboard() {
    const keyboard = document.getElementById("keyboard");
    if (!keyboard) return;

    // Configuration du clavier QWERTY (4 rangées)
    const keyboardLayout = [
      ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0"],
      ["Q", "W", "E", "R", "T", "Y", "U", "I", "O", "P"],
      ["A", "S", "D", "F", "G", "H", "J", "K", "L"],
      ["Z", "X", "C", "V", "B", "N", "M"],
    ];

    // Mapping des slugs vers les touches (exemple)
    const keyMapping = {
      html: "H",
      css: "S",
      c: "C",
      php: "P",
      python: "Y",
      linux: "L",
      javascript: "J",
      mysql: "M",
      git: "G",
    };

    // Créer une map inverse pour accès rapide
    const reverseMapping = {};
    languagesData.forEach((lang) => {
      const key = keyMapping[lang.slug] || lang.slug.charAt(0).toUpperCase();
      reverseMapping[key] = lang;
    });

    keyboard.innerHTML = "";

    keyboardLayout.forEach((row) => {
      const rowDiv = document.createElement("div");
      rowDiv.className = "keyboard__row";

      row.forEach((key) => {
        const keyDiv = document.createElement("div");
        const language = reverseMapping[key];

        if (language && language.active) {
          // Touche active
          keyDiv.className = "keyboard__key keyboard__key--active";
          keyDiv.setAttribute("data-lang-id", language.id);
          keyDiv.setAttribute("data-lang-slug", language.slug);

          // Icône
          if (language.icon_path) {
            const icon = document.createElement("img");
            icon.src = resolveAssetUrl(language.icon_path);
            icon.alt =
              currentLang === "fr" ? language.name_fr : language.name_en;
            icon.className = "keyboard__key-icon";
            keyDiv.appendChild(icon);
          }

          // Nom
          const name = document.createElement("span");
          name.className = "keyboard__key-name";
          name.textContent =
            currentLang === "fr" ? language.name_fr : language.name_en;
          keyDiv.appendChild(name);

          // Barre de progression (hover)
          const progress = document.createElement("div");
          progress.className = "keyboard__key-progress";
          progress.style.width = "0%";
          keyDiv.appendChild(progress);

          // Pourcentage (hover)
          const percentage = document.createElement("span");
          percentage.className = "keyboard__key-percentage";
          percentage.textContent = `${language.percentage}%`;
          keyDiv.appendChild(percentage);

          // Événements
          keyDiv.addEventListener("mouseenter", () => {
            progress.style.width = `${language.percentage}%`;
          });

          keyDiv.addEventListener("mouseleave", () => {
            progress.style.width = "0%";
          });

          keyDiv.addEventListener("click", () => {
            showSkillModal(language);
          });
        } else {
          // Touche inactive
          keyDiv.className = "keyboard__key keyboard__key--inactive";
        }

        rowDiv.appendChild(keyDiv);
      });

      keyboard.appendChild(rowDiv);
    });
  }

  /**
   * Affiche la modal de détails d'une compétence
   */
  function showSkillModal(language) {
    let modal = document.getElementById("skill-modal");

    // Créer la modal si elle n'existe pas
    if (!modal) {
      modal = document.createElement("div");
      modal.id = "skill-modal";
      modal.className = "skill-modal";
      modal.innerHTML = `
                <div class="skill-modal__content">
                    <button class="skill-modal__close" onclick="closeSkillModal()">&times;</button>
                    <div class="skill-modal__header">
                        <div class="skill-modal__icon">
                            <img src="" alt="" id="modal-icon">
                        </div>
                        <h3 class="skill-modal__title" id="modal-title"></h3>
                    </div>
                    <div class="skill-modal__progress">
                        <div class="skill-modal__progress-bar">
                            <div class="skill-modal__progress-fill" id="modal-progress"></div>
                        </div>
                        <span class="skill-modal__percentage" id="modal-percentage"></span>
                    </div>
                    <p class="skill-modal__description" id="modal-description"></p>
                </div>
            `;
      document.body.appendChild(modal);

      // Fermer au clic sur le fond
      modal.addEventListener("click", (e) => {
        if (e.target === modal) {
          closeSkillModal();
        }
      });
    }

    // Remplir le contenu
    const icon = document.getElementById("modal-icon");
    const title = document.getElementById("modal-title");
    const progress = document.getElementById("modal-progress");
    const percentage = document.getElementById("modal-percentage");
    const description = document.getElementById("modal-description");

    if (language.icon_path) {
      icon.src = resolveAssetUrl(language.icon_path);
      icon.alt = currentLang === "fr" ? language.name_fr : language.name_en;
    }

    title.textContent =
      currentLang === "fr" ? language.name_fr : language.name_en;
    progress.style.width = "0%";
    percentage.textContent = `${language.percentage}%`;
    description.textContent =
      currentLang === "fr" ? language.description_fr : language.description_en;

    // Afficher la modal
    modal.classList.add("show");

    // Animer la barre de progression
    setTimeout(() => {
      progress.style.width = `${language.percentage}%`;
    }, 100);
  }

  // Fonction globale pour fermer la modal
  window.closeSkillModal = function () {
    const modal = document.getElementById("skill-modal");
    if (modal) {
      modal.classList.remove("show");
    }
  };

  /**
   * Affiche le tooltip de détails d'une compétence (ANCIEN)
   */
  function showSkillTooltip(language) {
    const tooltip = document.getElementById("skill-tooltip");
    if (!tooltip) return;

    const iconContainer = tooltip.querySelector(".tooltip__icon");
    const title = tooltip.querySelector(".tooltip__title");
    const progressFill = tooltip.querySelector(".progress__fill");
    const percentage = tooltip.querySelector(".progress__percentage");
    const description = tooltip.querySelector(".tooltip__description");
    const closeBtn = tooltip.querySelector(".tooltip__close");

    // Remplir le contenu
    if (language.icon_path) {
      iconContainer.innerHTML = `<img src="${resolveAssetUrl(language.icon_path)}" alt="${language.name_fr}">`;
    } else {
      iconContainer.innerHTML = "";
    }

    title.textContent =
      currentLang === "fr" ? language.name_fr : language.name_en;
    progressFill.style.width = `${language.percentage}%`;
    percentage.textContent = `${language.percentage}%`;
    description.textContent =
      currentLang === "fr" ? language.description_fr : language.description_en;

    // Afficher
    tooltip.classList.add("active");

    // Fermer
    closeBtn.onclick = () => tooltip.classList.remove("active");

    // Fermer en cliquant en dehors
    setTimeout(() => {
      document.addEventListener("click", closeTooltipOutside);
    }, 100);

    function closeTooltipOutside(e) {
      if (
        !tooltip.contains(e.target) &&
        !e.target.closest(".keyboard__key--active")
      ) {
        tooltip.classList.remove("active");
        document.removeEventListener("click", closeTooltipOutside);
      }
    }
  }

  // =====================================================
  // ÉDUCATION / PARCOURS SCOLAIRE
  // =====================================================

  async function loadEducation() {
    try {
      const data = await fetchAPI(`/education.php?lang=${currentLang}`);
      if (data && data.success) {
        educationData = data.education || [];
        buildEducationTimeline();
      }
    } catch (error) {
      console.error("Erreur chargement éducation:", error);
    }
  }

  function buildEducationTimeline() {
    const container = document.getElementById("education-timeline");
    if (!container || educationData.length === 0) return;

    container.innerHTML = "";

    educationData.forEach((edu, index) => {
      const school =
        currentLang === "fr"
          ? edu.school || ""
          : edu.school_en || edu.school || "";
      const degree =
        currentLang === "fr"
          ? edu.degree || ""
          : edu.degree_en || edu.degree || "";
      const field =
        currentLang === "fr"
          ? edu.field || ""
          : edu.field_en || edu.field || "";
      const endDate = edu.is_current
        ? currentLang === "fr"
          ? "Aujourd'hui"
          : "Present"
        : edu.end_date || "";

      const item = document.createElement("div");
      item.className = "carousel__item";
      item.innerHTML = `
                <div class="timeline__item">
                    <div class="timeline__marker"></div>
                    <div class="timeline__content">
                        <span class="timeline__date">${edu.start_date || ""} - ${endDate}</span>
                        <h3 class="timeline__title">${degree}</h3>
                        ${field ? `<p class="timeline__location">${field}</p>` : ""}
                        <p class="timeline__school">${school}</p>
                        ${edu.description ? `<p class="timeline__description">${edu.description}</p>` : ""}
                    </div>
                </div>
            `;
      container.appendChild(item);
    });

    // Initialiser le carousel si plus d'un élément
    if (educationData.length > 1) {
      initEducationCarousel();
    }

    // Mettre à jour l'indicateur
    const indicator = document.getElementById("education-indicator");
    if (indicator) {
      indicator.textContent = `1 / ${educationData.length}`;
    }
  }

  function initEducationCarousel() {
    let currentIndex = 0;
    const container = document.getElementById("education-timeline");
    const prevBtn = document.getElementById("education-prev");
    const nextBtn = document.getElementById("education-next");
    const indicator = document.getElementById("education-indicator");

    if (!container || !prevBtn || !nextBtn) return;

    function updateCarousel() {
      container.style.transform = `translateX(-${currentIndex * 100}%)`;
      if (indicator) {
        indicator.textContent = `${currentIndex + 1} / ${educationData.length}`;
      }
    }

    prevBtn.addEventListener("click", () => {
      currentIndex =
        (currentIndex - 1 + educationData.length) % educationData.length;
      updateCarousel();
    });

    nextBtn.addEventListener("click", () => {
      currentIndex = (currentIndex + 1) % educationData.length;
      updateCarousel();
    });
  }

  // =====================================================
  // PROJETS
  // =====================================================

  async function loadProjects() {
    const data = await fetchAPI("/projects.php");
    if (data && data.success) {
      projectsData = data.projects || [];
      buildProjectsGrid();

      // Animer le compteur de projets
      const statProjects = document.getElementById("stat-projects");
      if (statProjects) {
        animateCounter(statProjects, projectsData.length);
      }
    }
  }

  /**
   * Construit la grille des projets
   */
  function buildProjectsGrid() {
    const grid = document.getElementById("projects-grid");
    if (!grid) return;

    if (projectsData.length === 0) {
      grid.innerHTML = `
                <div class="projects__empty">
                    <p>${translate("projects.empty")}</p>
                </div>
            `;
      return;
    }

    grid.innerHTML = "";

    projectsData.forEach((project) => {
      const card = document.createElement("div");
      card.className = "project__card";

      // Image
      let imageUrl =
        resolveAssetUrl(project.image_path) ||
        "assets/img/project-placeholder.svg";

      // Technologies (array JSON)
      let techTags = "";
      if (project.technologies) {
        try {
          const techs =
            typeof project.technologies === "string"
              ? JSON.parse(project.technologies)
              : project.technologies;

          techTags = techs
            .map((tech) => `<span class="tech__tag">${tech}</span>`)
            .join("");
        } catch (e) {
          console.error("Erreur parsing technologies:", e);
        }
      }

      // Liens
      let linksHTML = "";
      if (project.github_url) {
        linksHTML += `
                    <a href="${project.github_url}" class="project__link" target="_blank" rel="noopener">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        <span>GitHub</span>
                    </a>
                `;
      }
      if (project.demo_url) {
        linksHTML += `
                    <a href="${project.demo_url}" class="project__link" target="_blank" rel="noopener">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                            <polyline points="15 3 21 3 21 9"></polyline>
                            <line x1="10" y1="14" x2="21" y2="3"></line>
                        </svg>
                        <span>Demo</span>
                    </a>
                `;
      }

      card.innerHTML = `
                <img src="${imageUrl}" alt="${project.title_fr}" class="project__image">
                <div class="project__content">
                    <h3 class="project__title">${currentLang === "fr" ? project.title_fr : project.title_en}</h3>
                    <p class="project__description">${currentLang === "fr" ? project.description_fr : project.description_en}</p>
                    ${techTags ? `<div class="project__tech">${techTags}</div>` : ""}
                    ${linksHTML ? `<div class="project__links">${linksHTML}</div>` : ""}
                </div>
            `;

      grid.appendChild(card);
    });
  }

  // =====================================================
  // FORMULAIRE DE CONTACT
  // =====================================================

  function initContactForm() {
    const form = document.getElementById("contact-form");
    if (!form) return;

    // Flag pour empêcher les soumissions multiples
    let isSubmitting = false;

    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      // Si déjà en cours d'envoi, ignorer
      if (isSubmitting) {
        showToast("⏳ Envoi en cours...", "success");
        return;
      }

      const formData = new FormData(form);
      const data = {
        name: formData.get("name"),
        email: formData.get("email"),
        subject: formData.get("subject"),
        message: formData.get("message"),
      };

      // Validation basique
      if (!data.name || !data.email || !data.subject || !data.message) {
        showToast(translate("contact.error.required"), "error");
        return;
      }

      // Validation email
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(data.email)) {
        showToast(translate("contact.error.invalidEmail"), "error");
        return;
      }

      try {
        // Marquer comme en cours d'envoi
        isSubmitting = true;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = "⏳ Envoi en cours...";
        submitBtn.disabled = true;

        // Envoyer à l'API
        const response = await fetch("/Portfolio/api/contact.php", {
          method: "POST",
          body: formData,
        });

        const result = await response.json();

        if (result.success) {
          showToast(
            translate("contact.success") || "Message envoyé avec succès! 📧",
            "success",
          );
          form.reset();
        } else {
          showToast(result.error || translate("contact.error.send"), "error");
        }
      } catch (error) {
        console.error("Erreur:", error);
        showToast(
          translate("contact.error.send") || "Erreur lors de l'envoi",
          "error",
        );
      } finally {
        // Réactiver le bouton
        isSubmitting = false;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.textContent = originalText || "Envoyer";
        submitBtn.disabled = false;
      }
    });
  }

  // =====================================================
  // SCROLL TO TOP
  // =====================================================

  function initScrollToTop() {
    const scrollTopBtn = document.getElementById("scroll-top");
    if (!scrollTopBtn) return;

    window.addEventListener("scroll", () => {
      if (window.scrollY > 400) {
        scrollTopBtn.classList.add("visible");
      } else {
        scrollTopBtn.classList.remove("visible");
      }
    });

    scrollTopBtn.addEventListener("click", () => {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    });
  }

  // =====================================================
  // ANIMATIONS AU SCROLL
  // =====================================================

  function initScrollAnimations() {
    const observerOptions = {
      threshold: 0.1,
      rootMargin: "0px 0px -50px 0px",
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = "1";
          entry.target.style.transform = "translateY(0)";
        }
      });
    }, observerOptions);

    // Animer les cartes et sections
    const animatedElements = document.querySelectorAll(`
            .project__card,
            .stat__card,
            .info__item,
            .timeline__item,
            .contact__card
        `);

    animatedElements.forEach((el, index) => {
      el.style.opacity = "0";
      el.style.transform = "translateY(30px)";
      el.style.transition = `all 0.6s ease ${index * 0.1}s`;
      observer.observe(el);
    });
  }

  // =====================================================
  // ANNÉE COURANTE DANS LE FOOTER
  // =====================================================

  function updateCurrentYear() {
    const yearSpan = document.getElementById("current-year");
    if (yearSpan) {
      yearSpan.textContent = new Date().getFullYear();
    }
  }

  // =====================================================
  // MISE À JOUR DU CONTENU DYNAMIQUE
  // =====================================================

  function updateDynamicContent() {
    buildKeyboard();
    buildProjectsGrid();
    buildEducationTimeline();
    updateProfileUI();
  }

  // =====================================================
  // INITIALISATION PRINCIPALE
  // =====================================================

  async function init() {
    console.log("🚀 Initialisation du portfolio...");

    // Initialisation synchrone
    initNavigation();
    initLanguageToggle();
    initThemeToggle();
    initScrollToTop();
    initContactForm();
    updateCurrentYear();

    // Chargement asynchrone des données
    await Promise.all([
      loadTranslations(),
      loadProfile(),
      loadLanguages(),
      loadProjects(),
      loadEducation(),
    ]);

    // Animations après chargement
    setTimeout(() => {
      initScrollAnimations();
    }, 300);

    console.log("✅ Portfolio initialisé avec succès");
  }

  // =====================================================
  // DÉMARRAGE
  // =====================================================

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }

  // =====================================================
  // GESTION DU REDIMENSIONNEMENT
  // =====================================================

  let resizeTimer;
  window.addEventListener("resize", () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      // Reconstruire le clavier si nécessaire
      if (window.innerWidth < 768) {
        // Ajustements responsive si nécessaire
      }
    }, 250);
  });

  // =====================================================
  // GESTION DE LA VISIBILITÉ DE LA PAGE
  // =====================================================

  document.addEventListener("visibilitychange", () => {
    if (!document.hidden) {
      // Rafraîchir les données si la page redevient visible
      // (optionnel, à décommenter si besoin)
      // updateDynamicContent();
    }
  });

  // =====================================================
  // GESTION DES ERREURS GLOBALES
  // =====================================================

  window.addEventListener("error", (event) => {
    console.error("Erreur globale:", event.error);
    // Ne pas afficher de toast pour ne pas perturber l'utilisateur
  });

  window.addEventListener("unhandledrejection", (event) => {
    console.error("Promise rejetée:", event.reason);
    // Ne pas afficher de toast pour ne pas perturber l'utilisateur
  });
})();
