
chmod 777 deploy.sh
git add .
git commit -m "server update"
git pull



# Commands to run after deployment
composer install
php artisan migrate:fresh --seed
