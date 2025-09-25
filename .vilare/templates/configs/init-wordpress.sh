# !/bin/bash

[ -d .git ] && rm -rf .git

composer install

yarn

yarn vilare install --prepare

THEME_DIR=$(find wp-content/themes -mindepth 1 -maxdepth 1 -type d ! -name 'twentytwentyfive')

if [ -n "$THEME_DIR" ]; then
    cd $THEME_DIR
    yarn vilare install
fi
