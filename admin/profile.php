<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAuth();

$db = getDBConnection();
$success = '';
$error = '';

// Récupérer le profil actuel
$profile = $db->query("SELECT * FROM profile LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireEditAccess(); // Vérifier les permissions
    
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $data = [
            'name' => sanitizeInput($_POST['name'] ?? ''),
            'email' => sanitizeInput($_POST['email'] ?? ''),
            'github' => sanitizeInput($_POST['github'] ?? ''),
            'linkedin' => sanitizeInput($_POST['linkedin'] ?? ''),
            'tagline_fr' => sanitizeInput($_POST['tagline_fr'] ?? ''),
            'tagline_en' => sanitizeInput($_POST['tagline_en'] ?? ''),
            'bio_fr' => sanitizeInput($_POST['bio_fr'] ?? ''),
            'bio_en' => sanitizeInput($_POST['bio_en'] ?? ''),
        ];
        
        // Gestion de l'upload de l'avatar
        $avatarPath = null;
        if (!empty($_FILES['avatar']['name'])) {
            $upload = uploadFile(
                $_FILES['avatar'],
                ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'],
                2097152 // 2MB
            );
            
            if ($upload['success']) {
                $avatarPath = $upload['url'];
                
                // Supprimer l'ancien avatar si modification
                if ($profile && !empty($profile['avatar_path'])) {
                    deleteUploadedFile(basename($profile['avatar_path']));
                }
            } else {
                $error = $upload['error'];
            }
        }
        
        // Gestion de l'upload du CV
        $cvPath = null;
        if (!empty($_FILES['cv']['name'])) {
            $upload = uploadFile(
                $_FILES['cv'],
                [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ],
                5242880 // 5MB
            );
            
            if ($upload['success']) {
                $cvPath = $upload['url'];
                
                // Supprimer l'ancien CV si modification
                if ($profile && !empty($profile['cv_path'])) {
                    deleteUploadedFile(basename($profile['cv_path']));
                }
            } else {
                $error = $upload['error'];
            }
        }
        
        // Validation
        if (empty($data['name'])) {
            $error = 'Le nom est requis';
        } elseif (!empty($data['email']) && !validateEmail($data['email'])) {
            $error = 'Email invalide';
        } elseif (!empty($data['github']) && !validateUrl($data['github'])) {
            $error = 'URL GitHub invalide';
        } elseif (!empty($data['linkedin']) && !validateUrl($data['linkedin'])) {
            $error = 'URL LinkedIn invalide';
        } else {
            try {
                // Vérifier si la colonne avatar_centered existe
                $columns = $db->query("DESCRIBE profile")->fetchAll(PDO::FETCH_COLUMN, 0);
                $hasAvatarCentered = in_array('avatar_centered', $columns);
                
                if ($profile) {
                    // Update
                    $updateSql = "
                        UPDATE profile SET
                            name = ?,
                            email = ?,
                            github = ?,
                            linkedin = ?,
                            tagline_fr = ?,
                            tagline_en = ?,
                            bio_fr = ?,
                            bio_en = ?
                    ";
                    
                    $updateParams = [
                        $data['name'],
                        $data['email'],
                        $data['github'],
                        $data['linkedin'],
                        $data['tagline_fr'],
                        $data['tagline_en'],
                        $data['bio_fr'],
                        $data['bio_en']
                    ];
                    
                    if ($hasAvatarCentered) {
                        $updateSql .= ", avatar_centered = ?";
                        $updateParams[] = isset($_POST['avatar_centered']) ? 1 : 0;
                    }
                    
                    if ($avatarPath) {
                        $updateSql .= ", avatar_path = ?";
                        $updateParams[] = $avatarPath;
                    }
                    
                    if ($cvPath) {
                        $updateSql .= ", cv_path = ?";
                        $updateParams[] = $cvPath;
                    }
                    
                    $updateSql .= " WHERE id = ?";
                    $updateParams[] = $profile['id'];
                    
                    $stmt = $db->prepare($updateSql);
                    $stmt->execute($updateParams);
                } else {
                    // Insert
                    $insertSql = "
                        INSERT INTO profile (name, email, github, linkedin, tagline_fr, tagline_en, bio_fr, bio_en";
                    $insertParams = [
                        $data['name'],
                        $data['email'],
                        $data['github'],
                        $data['linkedin'],
                        $data['tagline_fr'],
                        $data['tagline_en'],
                        $data['bio_fr'],
                        $data['bio_en']
                    ];
                    
                    if ($cvPath) {
                        $insertSql .= ", cv_path";
                        $insertParams[] = $cvPath;
                    }
                    
                    $insertSql .= ") VALUES (" . implode(", ", array_fill(0, count($insertParams), "?")) . ")";
                    
                    $stmt = $db->prepare($insertSql);
                    $stmt->execute($insertParams);
                }
                
                $success = 'Profil mis à jour avec succès';
                $profile = $db->query("SELECT * FROM profile LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $error = 'Erreur lors de la mise à jour : ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Gérer le profil';
include 'includes/header.php';
?>

<?php if ($success): ?>
    <div class="alert alert--success">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span><?php echo escape($success); ?></span>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert--error">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span><?php echo escape($error); ?></span>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>Informations personnelles</h2>
        <p class="card-subtitle">Ces informations seront affichées publiquement sur le portfolio</p>
    </div>
    
    <form method="POST" class="card-body" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <div class="form-row">
            <div class="form-group">
                <label for="name" class="required">Nom</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    class="form-control"
                    value="<?php echo escape($profile['name'] ?? ''); ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control"
                    value="<?php echo escape($profile['email'] ?? ''); ?>"
                >
            </div>
        </div>
        
        <div class="form-group">
            <label for="tagline_fr">Tagline (Français)</label>
            <textarea 
                id="tagline_fr" 
                name="tagline_fr" 
                class="form-control"
                rows="2"
                placeholder="Phrase d'accroche en français"
            ><?php echo escape($profile['tagline_fr'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="tagline_en">Tagline (Anglais)</label>
            <textarea 
                id="tagline_en" 
                name="tagline_en" 
                class="form-control"
                rows="2"
                placeholder="Phrase d'accroche en anglais"
            ><?php echo escape($profile['tagline_en'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="bio_fr">Biographie (Français)</label>
            <textarea 
                id="bio_fr" 
                name="bio_fr" 
                class="form-control"
                rows="4"
                placeholder="Présentez-vous..."
            ><?php echo escape($profile['bio_fr'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="bio_en">Biographie (Anglais)</label>
            <textarea 
                id="bio_en" 
                name="bio_en" 
                class="form-control"
                rows="4"
                placeholder="About yourself..."
            ><?php echo escape($profile['bio_en'] ?? ''); ?></textarea>
        </div>
        
        <hr class="form-divider">
        
        <h3 class="form-section-title">Liens & Contact</h3>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                class="form-control"
                value="<?php echo escape($profile['email'] ?? ''); ?>"
                placeholder="votre@email.com"
            >
        </div>
        
        <div class="form-group">
            <label for="github">GitHub (URL complète)</label>
            <input 
                type="url" 
                id="github" 
                name="github" 
                class="form-control"
                value="<?php echo escape($profile['github'] ?? ''); ?>"
                placeholder="https://github.com/username"
            >
        </div>
        
        <div class="form-group">
            <label for="linkedin">LinkedIn (URL complète)</label>
            <input 
                type="url" 
                id="linkedin" 
                name="linkedin" 
                class="form-control"
                value="<?php echo escape($profile['linkedin'] ?? ''); ?>"
                placeholder="https://linkedin.com/in/username"
            >
        </div>
        
        <hr class="form-divider">
        
        <h3 class="form-section-title">Image de profil (Avatar)</h3>
        
        <div class="form-group">
            <label for="avatar">Photo de profil (JPG, PNG, WEBP - Max 2 Mo)</label>
            <input 
                type="file" 
                id="avatar" 
                name="avatar" 
                class="form-control"
                accept="image/jpeg,image/png,image/webp,image/jpg"
            >
            <?php if (!empty($profile['avatar_path'])): ?>
                <small style="display: block; margin-top: 8px;">
                    <img src="<?php echo escape($profile['avatar_path']); ?>" alt="Avatar actuel" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-border);">
                </small>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label class="checkbox-label">
                <input 
                    type="checkbox" 
                    name="avatar_centered" 
                    value="1" 
                    <?php echo (!empty($profile['avatar_centered']) ? 'checked' : ''); ?>
                >
                <span>Afficher l'avatar au centre de la cible sur la page d'accueil</span>
            </label>
            <small style="display: block; margin-top: 4px; color: var(--color-text-secondary);">
                Si décoché, seul le symbole de cible sera affiché
            </small>
        </div>
        
        <hr class="form-divider">
        
        <h3 class="form-section-title">Télécharger votre CV</h3>
        
        <div class="form-group">
            <label for="cv">Fichier CV (PDF, DOC, DOCX)</label>
            <input 
                type="file" 
                id="cv" 
                name="cv" 
                class="form-control"
                accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
            >
            <?php if (!empty($profile['cv_path'])): ?>
                <small style="display: block; margin-top: 8px;">
                    ✓ CV actuel : <a href="<?php echo escape($profile['cv_path']); ?>" target="_blank">Télécharger</a>
                </small>
            <?php endif; ?>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn--primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                <span>Enregistrer les modifications</span>
            </button>
            
            <a href="dashboard.php" class="btn btn--outline">Annuler</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>