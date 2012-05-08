#!/bin/sh

# chemin vers un répertoire temporaire (pour fichier lock)
pathRoot="/var/www/...../var/tmp"

# adresse du site de l'application
host="http://exemple.com"

# clé spécifié lors de l'installation de l'application
# aussi disponible dans le fichier config.ini du répertoire "app"
key=""

# FIN CONFIGURATION

# détecte une exécution en cours
if [ -e "$pathRoot/file.lock" ]; then
    exit 0
fi

touch "$pathRoot/file.lock"
curl "$host/cron/alert-mail/send?key=$key"
rm "$pathRoot/file.lock"
