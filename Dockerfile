# CARMENT — PHP 8.2 + Apache (agar .htaccess & uploads/.htaccess tetap berlaku)
FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
      libpq-dev libpng-dev libjpeg62-turbo-dev libwebp-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" pdo_pgsql gd \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*

# batas upload 3 MB (simpan_foto) + zona waktu
RUN printf "upload_max_filesize=8M\npost_max_size=10M\nmemory_limit=192M\ndate.timezone=Asia/Jakarta\nexpose_php=Off\n" \
      > /usr/local/etc/php/conf.d/app.ini

# Render free = 0.1 CPU / 512 MB. Apache prefork default (150 worker) akan OOM.
RUN sed -i 's/^StartServers.*/StartServers 2/; s/^MinSpareServers.*/MinSpareServers 2/; s/^MaxSpareServers.*/MaxSpareServers 5/; s/^MaxRequestWorkers.*/MaxRequestWorkers 8/; s/^MaxConnectionsPerChild.*/MaxConnectionsPerChild 500/' \
      /etc/apache2/mods-available/mpm_prefork.conf

COPY carment-php/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html/gambar /var/www/html/uploads

# AllowOverride All -> uploads/.htaccess ("Require all denied") benar-benar aktif
RUN printf '<Directory /var/www/html>\n  AllowOverride All\n  Options -Indexes\n  Require all granted\n</Directory>\nServerName localhost\n' \
      > /etc/apache2/conf-available/app.conf && a2enconf app

EXPOSE 80

# Render menyuntik $PORT (default 10000); Apache default listen 80 -> health check gagal
CMD ["sh","-c","sed -i \"s/^Listen 80/Listen ${PORT:-80}/\" /etc/apache2/ports.conf && exec apache2-foreground"]
