FROM php:8.2-apache

# Habilitar mod_rewrite de Apache para que funcione el .htaccess
RUN a2enmod rewrite

# Instalar extensiones necesarias de PHP para la base de datos
RUN docker-php-ext-install pdo pdo_mysql

# Instalar Composer (para descargar las dependencias del proyecto)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Cambiar el DocumentRoot de Apache a la carpeta public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copiar el código del proyecto al contenedor
COPY . /var/www/html/

# Instalar dependencias de PHP (vlucas/phpdotenv, dompdf, etc.)
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader

# Dar permisos a Apache sobre la carpeta
RUN chown -R www-data:www-data /var/www/html

# Render usa la variable PORT; Apache debe escuchar en ese puerto
RUN sed -ri -e 's/80/${PORT}/g' /etc/apache2/sites-available/*.conf /etc/apache2/ports.conf

# Exponer el puerto que Render asigna (por defecto 10000)
ENV PORT 10000
EXPOSE 10000
