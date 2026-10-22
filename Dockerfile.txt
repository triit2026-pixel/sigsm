# Usar la imagen base de PHP con Apache
FROM php:8.2-apache

# Instalar extensiones necesarias (por ejemplo, mysqli)
# RUN docker-php-ext-install mysqli
 RUN docker-php-ext-install pdo pdo_mysql

# Establecer el directorio de trabajo dentro del contenedor
WORKDIR /var/www/html

# Copiar todo el contenido desde el contexto del Dockerfile al directorio de trabajo en el contenedor
COPY . .

# Configurar Apache para que use el archivo .htaccess
RUN a2enmod rewrite