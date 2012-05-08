#!/bin/sh

# temps (en minute) de mise en cache des fichiers RSS
cache_time=20

# configurer le chemin pour pointer vers le répertoire feed/refresh/id
# de l'application
pathRoot="/var/www/...../www/feed/refresh/id"

if [ -e $pathRoot ]; then
    find $pathRoot -name "*.rss" -type f -cmin +$cache_time -exec rm -f {} \;
fi

