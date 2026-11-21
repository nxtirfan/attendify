#!/bin/bash
# Bootstrap EC2 Attendify (Amazon Linux 2023, PHP 8.3 + Nginx)
# Dipakai oleh launch template attendify-lt. TIDAK menyimpan kredensial/rahasia.
set -e

dnf update -y
dnf install -y nginx git unzip curl
dnf install -y php8.3 php8.3-fpm php8.3-mysqlnd php8.3-gd php8.3-mbstring \
  php8.3-xml php8.3-curl php8.3-zip php8.3-intl php8.3-bcmath

curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

cd /var/www
if [ ! -d attendify ]; then
  git clone https://github.com/nxtirfan/attendify.git attendify
fi
cd attendify
composer install --no-dev --optimize-autoloader

cp -n .env.production.example .env
php artisan key:generate
php artisan storage:link

chown -R apache:apache storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

cat > /etc/nginx/conf.d/attendify.conf <<'NGINX'
server {
    listen 80;
    server_name _;
    root /var/www/attendify/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ ^/storage/.*\.(php|phtml|phar)$ {
        deny all;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX

systemctl enable --now php-fpm
systemctl enable --now nginx

echo '* * * * * cd /var/www/attendify && php artisan schedule:run >> /dev/null 2>&1' \
  > /etc/cron.d/attendify
