#!/bin/bash

# Backup du fichier CSS
sudo cp /var/www/portfolio/public/assets/css/style.css /var/www/portfolio/public/assets/css/style.css.backup

# Suppression des anciennes règles (lignes 1-2)
sudo sed -i '1,2d' /var/www/portfolio/public/assets/css/style.css

# Création du nouveau CSS en haut du fichier
sudo sed -i '1i/* Force header visibility - includes scrolled state */\
.header, .header.scrolled {\
  position: fixed !important;\
  top: 0 !important;\
  transform: translateY(0) !important;\
  animation: none !important;\
  transition: none !important;\
  opacity: 1 !important;\
  visibility: visible !important;\
  display: flex !important;\
}\
' /var/www/portfolio/public/assets/css/style.css

# Mise à jour de la version CSS
NEW_VERSION=$(date +%s)
sudo sed -i "s/style\.css?v=[0-9]*/style.css?v=$NEW_VERSION/g" /var/www/portfolio/public/index.php

echo "Header CSS fixed! New version: $NEW_VERSION"
