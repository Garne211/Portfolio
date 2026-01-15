<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Administration'; ?> - Portfolio Admin</title>
    <!-- Design System et CSS Admin -->
    <?php 
        $baseUrl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];
        $adminPath = '/Portfolio/admin';
        $publicPath = '/Portfolio/public';
    ?>
    <!-- Base Design System -->
    <link rel="stylesheet" href="<?php echo $baseUrl . $publicPath; ?>/assets/css/base.css">
    <!-- Admin Components -->
    <link rel="stylesheet" href="<?php echo $baseUrl . $adminPath; ?>/assets/css/components.css">
    <!-- Legacy Admin CSS (si nécessaire) -->
    <link rel="stylesheet" href="<?php echo $baseUrl . $adminPath; ?>/assets/css/admin.css">
    <!-- Session Warning -->
    <link rel="stylesheet" href="<?php echo $baseUrl . $adminPath; ?>/assets/css/session-warning.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo">
                    <span class="logo__bracket">&lt;</span>
                    <span class="logo__name">ADMIN</span>
                    <span class="logo__bracket">/&gt;</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <span>Tableau de bord</span>
                </a>
                
                <a href="profile.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>Profil</span>
                </a>
                
                <a href="languages.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'languages.php' ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="16 18 22 12 16 6"></polyline>
                        <polyline points="8 6 2 12 8 18"></polyline>
                    </svg>
                    <span>Langages</span>
                </a>
                
                <a href="projects.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'projects.php' ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <span>Projets</span>
                </a>
                
                <a href="experiences.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'experiences.php' ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>Expériences</span>
                </a>
                
                <a href="education.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'education.php' ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                    <span>Parcours scolaire</span>
                </a>
                
                <a href="contents.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'contents.php' ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                    <span>Contenus</span>
                </a>
                
                <a href="messages.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'messages.php' ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <span>Messages</span>
                </a>
                
                <a href="security.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'security.php' ? 'active' : ''; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    <span>Sécurité</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="/" target="_blank" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                    <span>Voir le site</span>
                </a>
                
                <a href="logout.php" class="nav-item nav-item--danger">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Déconnexion</span>
                </a>
            </div>
        </aside>
        
        <!-- Main content -->
        <main class="main-content">
            <div class="content-header">
                <h1 class="page-title"><?php echo $pageTitle ?? 'Administration'; ?></h1>
                <div class="header-actions">
                    <span class="user-info">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <?php echo escape($_SESSION['admin_username']); ?>
                    </span>
                </div>
            </div>
            
            <div class="content-body">