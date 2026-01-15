<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Détection de la langue par défaut
$defaultLang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) 
    ? (strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'fr') !== false ? 'fr' : 'en')
    : 'en';
?>
<!DOCTYPE html>
<html lang="<?php echo $defaultLang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portfolio de LEBEHOT Hery - Étudiant BTS SIO & futur Pentester">
    <meta name="author" content="LEBEHOT Hery">
    <meta name="theme-color" content="#000000">
    
    <title>LEBEHOT Hery | Portfolio</title>
    
    <!-- Preconnect pour optimisation -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Design System Base -->
    <link rel="stylesheet" href="assets/css/base.css">
    <!-- CSS principal -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
</head>
<body>
    
    <!-- ======================== -->
    <!-- HEADER / NAVIGATION -->
    <!-- ======================== -->
    <header class="header" id="header">
        <nav class="nav container">
            <a href="#home" class="nav__logo">
                <span class="logo__bracket">&lt;</span>
                <span class="logo__name">HLEBEHOT</span>
                <span class="logo__bracket">/&gt;</span>
            </a>
            
            <div class="nav__menu" id="nav-menu">
                <ul class="nav__list">
                    <li class="nav__item">
                        <a href="#home" class="nav__link" data-i18n="nav.home">
                            Accueil
                        </a>
                    </li>
                    <li class="nav__item">
                        <a href="#about" class="nav__link" data-i18n="nav.about">
                            À propos
                        </a>
                    </li>
                    <li class="nav__item">
                        <a href="#skills" class="nav__link" data-i18n="nav.skills">
                            Compétences
                        </a>
                    </li>
                    <li class="nav__item">
                        <a href="#projects" class="nav__link" data-i18n="nav.projects">
                            Projets
                        </a>
                    </li>
                    <li class="nav__item">
                        <a href="#education" class="nav__link" data-i18n="nav.education">
                            Parcours
                        </a>
                    </li>
                    <li class="nav__item">
                        <a href="#contact" class="nav__link" data-i18n="nav.contact">
                            Contact
                        </a>
                    </li>
                </ul>
                
                <div class="nav__close" id="nav-close">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </div>
            </div>
            
            <div class="nav__actions">
                <!-- Toggle langue -->
                <button class="lang__toggle" id="lang-toggle" aria-label="Changer de langue">
                    <span class="lang__option" data-lang="fr">FR</span>
                    <span class="lang__separator">|</span>
                    <span class="lang__option" data-lang="en">EN</span>
                </button>
                
                <!-- Toggle Mode Clair/Sombre -->
                <button class="theme-toggle" id="theme-toggle" title="Basculer le mode sombre/clair" aria-label="Toggle dark/light mode">
                    <svg class="theme-icon--dark" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                    <svg class="theme-icon--light" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                </button>
                
                <!-- Menu burger mobile -->
                <div class="nav__toggle" id="nav-toggle">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </div>
            </div>
        </nav>
    </header>

    <!-- ======================== -->
    <!-- SECTION HERO -->
    <!-- ======================== -->
    <section class="hero section" id="home">
        <div class="hero__container container">
            <div class="hero__content">
                <span class="hero__greeting" data-i18n="hero.greeting">
                    Bonjour, je suis
                </span>
                <h1 class="hero__name">
                    <span class="hero__firstname">Hery</span>
                    <span class="hero__lastname">LEBEHOT</span>
                </h1>
                <p class="hero__tagline" data-i18n="hero.tagline">
                    Étudiant BTS SIO · Futur Pentester · Passionné de cybersécurité
                </p>
                
                <div class="hero__cta">
                    <a href="#projects" class="btn btn--primary" data-i18n="hero.cta.projects">
                        Voir mes projets
                    </a>
                    <a href="#contact" class="btn btn--outline" data-i18n="hero.cta.contact">
                        Me contacter
                    </a>
                </div>
                
                <div class="hero__social">
                    <a href="#" class="social__link" target="_blank" rel="noopener" aria-label="GitHub" id="link-github">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                    </a>
                    <a href="#" class="social__link" target="_blank" rel="noopener" aria-label="LinkedIn" id="link-linkedin">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                        </svg>
                    </a>
                    <a href="mailto:contact@example.com" class="social__link" aria-label="Email" id="link-email">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </a>
                </div>
            </div>
            
            <div class="hero__visual">
                <div class="hero__avatar">
                    <div class="avatar__glow"></div>
                    <div class="avatar__frame">
                        <svg viewBox="0 0 200 200" class="avatar__icon">
                            <circle cx="100" cy="100" r="90" fill="none" stroke="currentColor" stroke-width="2" opacity="0.2"/>
                            <circle cx="100" cy="100" r="70" fill="none" stroke="currentColor" stroke-width="2" opacity="0.3"/>
                            <circle cx="100" cy="100" r="50" fill="none" stroke="currentColor" stroke-width="2" opacity="0.5"/>
                            <path d="M100 50 L100 150 M50 100 L150 100" stroke="currentColor" stroke-width="2" opacity="0.6"/>
                        </svg>
                        <!-- Avatar image (chargé dynamiquement via JS) -->
                        <img src="" alt="" class="avatar__image" id="avatar-image" style="display: none;">
                    </div>
                </div>
                <div class="hero__code-snippet">
                    <pre><code><span class="token keyword">const</span> <span class="token variable">developer</span> <span class="token operator">=</span> <span class="token punctuation">{</span>
  <span class="token property">name</span><span class="token operator">:</span> <span class="token string">"Hery LEBEHOT"</span><span class="token punctuation">,</span>
  <span class="token property">focus</span><span class="token operator">:</span> <span class="token string">"Pentesting"</span><span class="token punctuation">,</span>
  <span class="token property">passion</span><span class="token operator">:</span> <span class="token string">"Cybersecurity"</span>
<span class="token punctuation">}</span><span class="token punctuation">;</span></code></pre>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================== -->
    <!-- SECTION À PROPOS -->
    <!-- ======================== -->
    <section class="about section" id="about">
        <div class="container">
            <h2 class="section__title" data-i18n="about.title">
                À propos de moi
            </h2>
            
            <div class="about__container">
                <div class="about__content">
                    <p class="about__description" data-i18n="about.description">
                        Actuellement en <strong>première année de BTS SIO</strong> à Basse-Terre, Guadeloupe, 
                        je me passionne pour la <strong>cybersécurité</strong> et les tests d'intrusion. 
                        Mon objectif est d'intégrer une école d'ingénieur pour devenir <strong>Pentester</strong> 
                        et contribuer à la sécurisation des systèmes d'information.
                    </p>
                    
                    <div class="about__info">
                        <div class="info__item">
                            <svg class="info__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <div>
                                <span class="info__label" data-i18n="about.age">Âge</span>
                                <span class="info__value">18 ans</span>
                            </div>
                        </div>
                        
                        <div class="info__item">
                            <svg class="info__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <div>
                                <span class="info__label" data-i18n="about.location">Localisation</span>
                                <span class="info__value">Basse-Terre, Guadeloupe</span>
                            </div>
                        </div>
                        
                        <div class="info__item">
                            <svg class="info__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                            </svg>
                            <div>
                                <span class="info__label" data-i18n="about.education">Formation</span>
                                <span class="info__value">BTS SIO – 1re année</span>
                            </div>
                        </div>
                        
                        <div class="info__item">
                            <svg class="info__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            <div>
                                <span class="info__label" data-i18n="about.goal">Objectif</span>
                                <span class="info__value" data-i18n="about.goalValue">École d'ingénieur & Pentester</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="about__stats">
                    <div class="stat__card">
                        <span class="stat__number" id="stat-languages">0</span>
                        <span class="stat__label" data-i18n="about.stats.languages">Langages maîtrisés</span>
                    </div>
                    <div class="stat__card">
                        <span class="stat__number" id="stat-projects">0</span>
                        <span class="stat__label" data-i18n="about.stats.projects">Projets réalisés</span>
                    </div>
                    <div class="stat__card">
                        <span class="stat__number">1+</span>
                        <span class="stat__label" data-i18n="about.stats.experience">Années d'apprentissage</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================== -->
    <!-- SECTION TÉLÉCHARGER CV -->
    <!-- ======================== -->
    <section class="cv-section section" id="cv" style="background: linear-gradient(135deg, rgba(220, 38, 38, 0.1) 0%, rgba(59, 130, 246, 0.1) 100%); padding: 60px 20px;">
        <div class="container">
            <div style="max-width: 600px; margin: 0 auto; text-align: center;">
                <div style="margin-bottom: 40px;">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto; color: var(--color-primary);">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                </div>
                
                <h2 class="section__title" data-i18n="cv.title" style="margin-bottom: 16px;">Télécharger mon CV</h2>
                <p class="section__description" data-i18n="cv.description" style="margin-bottom: 32px; color: var(--color-text-secondary);">
                    Téléchargez mon CV complet pour plus de détails sur mon parcours et mes compétences.
                </p>
                
                <a href="javascript:void(0)" id="download-cv-btn" class="btn btn--primary" style="display: inline-flex; align-items: center; gap: 10px; text-decoration: none;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span data-i18n="cv.button">Télécharger le CV</span>
                </a>
            </div>
        </div>
    </section>

    <!-- ======================== -->
    <!-- SECTION COMPÉTENCES (CLAVIER) -->
    <!-- ======================== -->
    <section class="skills section" id="skills">
        <div class="container">
            <h2 class="section__title" data-i18n="skills.title">
                Mes compétences
            </h2>
            <p class="section__subtitle" data-i18n="skills.subtitle">
                Survolez ou cliquez sur les touches actives pour en savoir plus
            </p>
            
            <div class="keyboard" id="keyboard">
                <!-- Généré dynamiquement par JavaScript -->
                <div class="keyboard__loading">
                    <div class="loading__spinner"></div>
                    <p data-i18n="skills.loading">Chargement du clavier...</p>
                </div>
            </div>
            
            <!-- Tooltip pour affichage des détails -->
            <div class="skill__tooltip" id="skill-tooltip">
                <button class="tooltip__close" aria-label="Fermer">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
                <div class="tooltip__icon">
                    <!-- Image/icône -->
                </div>
                <h3 class="tooltip__title"></h3>
                <div class="tooltip__progress">
                    <div class="progress__bar">
                        <div class="progress__fill"></div>
                    </div>
                    <span class="progress__percentage"></span>
                </div>
                <p class="tooltip__description"></p>
            </div>
        </div>
    </section>

    <!-- ======================== -->
    <!-- SECTION PROJETS -->
    <!-- ======================== -->
    <section class="projects section" id="projects">
        <div class="container">
            <h2 class="section__title" data-i18n="projects.title">
                Mes projets
            </h2>
            
            <div class="projects__grid" id="projects-grid">
                <!-- Généré dynamiquement par JavaScript -->
                <div class="projects__loading">
                    <div class="loading__spinner"></div>
                    <p data-i18n="projects.loading">Chargement des projets...</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================== -->
    <!-- SECTION PARCOURS SCOLAIRE -->
    <!-- ======================== -->
    <section class="education section" id="education">
        <div class="container">
            <h2 class="section__title" data-i18n="education.title">
                Parcours scolaire
            </h2>
            
            <div class="carousel-container">
                <div class="carousel" id="education-carousel">
                    <div class="carousel__items" id="education-timeline">
                        <!-- Généré dynamiquement depuis la BDD via JavaScript -->
                    </div>
                </div>
                
                <!-- Contrôles du carousel -->
                <div class="carousel__controls">
                    <button class="carousel__btn carousel__btn--prev" id="education-prev" aria-label="Précédent">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>
                    <span class="carousel__indicator" id="education-indicator">1 / 1</span>
                    <button class="carousel__btn carousel__btn--next" id="education-next" aria-label="Suivant">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================== -->
    <!-- SECTION EXPÉRIENCES -->
    <!-- ======================== -->
    <section class="experience section" id="experience">
        <div class="container">
            <h2 class="section__title" data-i18n="experience.title">
                Expériences & Stages
            </h2>
            
            <div class="carousel-container">
                <div class="carousel" id="experience-carousel">
                    <div class="carousel__items" id="experience-timeline">
                        <!-- Généré dynamiquement depuis la BDD -->
                        <div class="carousel__item">
                            <div class="timeline__item">
                                <div class="timeline__marker"></div>
                                <div class="timeline__content">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div>
                                            <h3 class="timeline__title" data-i18n="experience.empty">Expériences à venir</h3>
                                            <p class="timeline__location">...</p>
                                        </div>
                                        <span class="badge badge--warning">Stage</span>
                                    </div>
                                    <p class="timeline__description">Expériences à venir dans le cadre de mes études...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contrôles du carousel -->
                <div class="carousel__controls">
                    <button class="carousel__btn carousel__btn--prev" id="experience-prev" aria-label="Précédent">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>
                    <span class="carousel__indicator" id="experience-indicator">1 / 1</span>
                    <button class="carousel__btn carousel__btn--next" id="experience-next" aria-label="Suivant">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================== -->
    <!-- SECTION CONTACT -->
    <!-- ======================== -->
    <section class="contact section" id="contact">
        <div class="container">
            <h2 class="section__title" data-i18n="contact.title">
                Me contacter
            </h2>
            <p class="section__subtitle" data-i18n="contact.subtitle">
                Une question, un projet ? N'hésitez pas à me contacter !
            </p>
            
            <div class="contact__container">
                <form class="contact__form" id="contact-form">
                    <div class="form__group">
                        <label for="name" class="form__label" data-i18n="contact.form.name">Nom</label>
                        <input type="text" id="name" name="name" class="form__input" required>
                    </div>
                    
                    <div class="form__group">
                        <label for="email" class="form__label" data-i18n="contact.form.email">Email</label>
                        <input type="email" id="email" name="email" class="form__input" required>
                    </div>
                    
                    <div class="form__group">
                        <label for="subject" class="form__label" data-i18n="contact.form.subject">Sujet</label>
                        <input type="text" id="subject" name="subject" class="form__input" required>
                    </div>
                    
                    <div class="form__group">
                        <label for="message" class="form__label" data-i18n="contact.form.message">Message</label>
                        <textarea id="message" name="message" class="form__textarea" rows="6" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn--primary btn--block">
                        <span data-i18n="contact.form.submit">Envoyer le message</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </form>
                
                <div class="contact__info">
                    <div class="contact__card">
                        <svg class="contact__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <h3 class="contact__card-title" data-i18n="contact.email.title">Email</h3>
                        <a href="mailto:contact@example.com" class="contact__link" id="contact-email">
                            contact@example.com
                        </a>
                    </div>
                    
                    <div class="contact__card">
                        <svg class="contact__icon" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        <h3 class="contact__card-title">GitHub</h3>
                        <a href="#" class="contact__link" target="_blank" rel="noopener" id="contact-github">
                            github.com/hlebehot
                        </a>
                    </div>
                    
                    <div class="contact__card">
                        <svg class="contact__icon" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                        </svg>
                        <h3 class="contact__card-title">LinkedIn</h3>
                        <a href="#" class="contact__link" target="_blank" rel="noopener" id="contact-linkedin">
                            linkedin.com/in/hery-lebehot
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================== -->
    <!-- FOOTER -->
    <!-- ======================== -->
    <footer class="footer">
        <div class="container">
            <div class="footer__content">
                <div class="footer__brand">
                    <a href="#home" class="footer__logo">
                        <span class="logo__bracket">&lt;</span>
                        <span class="logo__name">HLEBEHOT</span>
                        <span class="logo__bracket">/&gt;</span>
                    </a>
                    <p class="footer__tagline" data-i18n="footer.tagline">
                        Étudiant BTS SIO · Futur Pentester
                    </p>
                </div>
                
                <div class="footer__links">
                    <h4 class="footer__title" data-i18n="footer.navigation">Navigation</h4>
                    <ul class="footer__list">
                        <li><a href="#about" data-i18n="nav.about">À propos</a></li>
                        <li><a href="#skills" data-i18n="nav.skills">Compétences</a></li>
                        <li><a href="#projects" data-i18n="nav.projects">Projets</a></li>
                        <li><a href="#contact" data-i18n="nav.contact">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer__social">
                    <h4 class="footer__title" data-i18n="footer.followMe">Me suivre</h4>
                    <div class="social__links">
                        <a href="#" class="social__link" target="_blank" rel="noopener" aria-label="GitHub">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                            </svg>
                        </a>
                        <a href="#" class="social__link" target="_blank" rel="noopener" aria-label="LinkedIn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="footer__bottom">
                <p class="footer__copyright">
                    &copy; <span id="current-year"></span> Hery LEBEHOT. 
                    <span data-i18n="footer.rights">Tous droits réservés.</span>
                </p>
                <p class="footer__made">
                    <span data-i18n="footer.madeWith">Fait avec</span>
                    <span class="footer__heart">❤️</span>
                    <span data-i18n="footer.location">en Guadeloupe</span>
                </p>
            </div>
        </div>
    </footer>

    <!-- Bouton scroll to top -->
    <button class="scroll-top" id="scroll-top" aria-label="Retour en haut">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="18 15 12 9 6 15"></polyline>
        </svg>
    </button>

    <!-- Toast notifications -->
    <div class="toast__container" id="toast-container"></div>

    <!-- Scripts -->
    <script src="assets/js/script.js" defer></script>
</body>
</html>