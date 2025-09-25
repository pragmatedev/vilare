# !/bin/bash

cd wp-content/themes/vilare

composer install

yarn

yarn vilare install --setup
