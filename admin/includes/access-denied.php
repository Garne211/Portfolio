<?php
/**
 * Page d'erreur d'accès refusé
 * S'affiche 5 secondes puis redirige
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Refusé</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .error-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #ff6b6b, #ff8787);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        h1 {
            color: #2d3748;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        p {
            color: #718096;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .countdown {
            font-size: 14px;
            color: #667eea;
            font-weight: 600;
            margin-top: 30px;
            padding: 15px;
            background: #f7fafc;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .countdown-number {
            font-size: 32px;
            color: #667eea;
            font-weight: bold;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }
        
        .back-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">🔒</div>
        <h1>Accès Refusé</h1>
        <p>Vous n'avez pas les permissions nécessaires pour effectuer cette action.</p>
        <p>Les utilisateurs en <strong>lecture seule</strong> ne peuvent pas modifier le contenu.</p>
        
        <div class="countdown">
            Redirection dans <span class="countdown-number">5</span>s...
        </div>
        
        <a href="javascript:history.back()" class="back-link">Revenir à la page précédente</a>
    </div>
    
    <script>
        let countdown = 5;
        const countdownElement = document.querySelector('.countdown-number');
        
        const interval = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(interval);
                history.back();
            }
        }, 1000);
    </script>
</body>
</html>
