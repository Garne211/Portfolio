<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$db = getDBConnection();

// Statistiques rapides
$stats = [
    'languages' => $db->query("SELECT COUNT(*) FROM languages WHERE active = 1")->fetchColumn(),
    'projects' => $db->query("SELECT COUNT(*) FROM projects WHERE visible = 1")->fetchColumn(),
    'total_languages' => $db->query("SELECT COUNT(*) FROM languages")->fetchColumn(),
    'total_projects' => $db->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
];

// Récupérer le profil
$profile = $db->query("SELECT * FROM profile LIMIT 1")->fetch(PDO::FETCH_ASSOC);

$pageTitle = 'Tableau de bord';
include 'includes/header.php';
?>

<div class="dashboard-grid">
    <!-- Carte : Bienvenue -->
    <div class="dashboard-card dashboard-card--welcome">
        <div class="card-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
        <div class="card-content">
            <h2>Bienvenue, <?php echo escape($_SESSION['admin_username']); ?></h2>
            <p>Dernière connexion : <?php echo date('d/m/Y à H:i'); ?></p>
        </div>
    </div>
    
    <!-- Carte : Infos personnelles -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-icon card-icon--primary">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <div>
                <h3>Profil</h3>
                <p class="card-subtitle">Informations personnelles</p>
            </div>
        </div>
        <div class="card-body">
            <?php if ($profile): ?>
                <div class="info-row">
                    <span class="info-label">Nom :</span>
                    <span class="info-value"><?php echo escape($profile['name'] ?? 'Non configuré'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email :</span>
                    <span class="info-value"><?php echo escape($profile['email'] ?? 'Non configuré'); ?></span>
                </div>
            <?php else: ?>
                <p class="text-muted">Aucun profil configuré</p>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <a href="profile.php" class="btn btn--outline btn--sm">Gérer le profil</a>
        </div>
    </div>
    
    <!-- Carte : Langages -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-icon card-icon--success">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="16 18 22 12 16 6"></polyline>
                    <polyline points="8 6 2 12 8 18"></polyline>
                </svg>
            </div>
            <div>
                <h3>Langages</h3>
                <p class="card-subtitle">Compétences techniques</p>
            </div>
        </div>
        <div class="card-body">
            <div class="stat-number"><?php echo $stats['languages']; ?></div>
            <p class="stat-label">Langages actifs</p>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo ($stats['languages'] / max($stats['total_languages'], 1)) * 100; ?>%"></div>
            </div>
            <p class="text-muted text-xs"><?php echo $stats['total_languages']; ?> au total</p>
        </div>
        <div class="card-footer">
            <a href="languages.php" class="btn btn--outline btn--sm">Gérer les langages</a>
        </div>
    </div>
    
    <!-- Carte : Projets -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-icon card-icon--warning">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                </svg>
            </div>
            <div>
                <h3>Projets</h3>
                <p class="card-subtitle">Portfolio de réalisations</p>
            </div>
        </div>
        <div class="card-body">
            <div class="stat-number"><?php echo $stats['projects']; ?></div>
            <p class="stat-label">Projets visibles</p>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo ($stats['projects'] / max($stats['total_projects'], 1)) * 100; ?>%"></div>
            </div>
            <p class="text-muted text-xs"><?php echo $stats['total_projects']; ?> au total</p>
        </div>
        <div class="card-footer">
            <a href="projects.php" class="btn btn--outline btn--sm">Gérer les projets</a>
        </div>
    </div>
    
    <!-- Carte : Contenus -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-icon card-icon--info">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
            </div>
            <div>
                <h3>Contenus</h3>
                <p class="card-subtitle">Textes et traductions</p>
            </div>
        </div>
        <div class="card-body">
            <p>Gérez tous les textes du site en français et en anglais.</p>
        </div>
        <div class="card-footer">
            <a href="contents.php" class="btn btn--outline btn--sm">Gérer les contenus</a>
        </div>
    </div>
    
    <!-- Carte : Sécurité -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-icon card-icon--danger">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>
            <div>
                <h3>Sécurité</h3>
                <p class="card-subtitle">Mot de passe & sessions</p>
            </div>
        </div>
        <div class="card-body">
            <p>Changez votre mot de passe et gérez la sécurité de votre compte.</p>
        </div>
        <div class="card-footer">
            <a href="security.php" class="btn btn--outline btn--sm">Paramètres de sécurité</a>
        </div>
    </div>
</div>

<!-- Actions rapides -->
<div class="quick-actions">
    <h2 class="section-title">Actions rapides</h2>
    <div class="actions-grid">
        <button class="action-btn" onclick="window.location.href='languages.php?action=add'">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Ajouter un langage</span>
        </button>
        
        <button class="action-btn" onclick="window.location.href='projects.php?action=add'">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Ajouter un projet</span>
        </button>
        
        <button class="action-btn" onclick="window.open('/', '_blank')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                <polyline points="15 3 21 3 21 9"></polyline>
                <line x1="10" y1="14" x2="21" y2="3"></line>
            </svg>
            <span>Voir le site public</span>
        </button>
        
        <button class="action-btn" onclick="if(confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) window.location.href='logout.php'">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Se déconnecter</span>
        </button>
    </div>
</div>

<?php include 'includes/footer.php'; ?>