FROM php:8.2-apache

# Habilitar mod_rewrite de Apache para que funcione el .htaccess
RUN a2enmod rewrite

# Instalar extensiones necesarias de PHP para la base de datos
RUN docker-php-ext-install pdo pdo_mysql

# Cambiar el DocumentRoot de Apache a la carpeta public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copiar el código del proyecto al contenedor
COPY . /var/www/html/

# Dar permisos a Apache sobre la carpeta
RUN chown -R www-data:www-data /var/www/html
