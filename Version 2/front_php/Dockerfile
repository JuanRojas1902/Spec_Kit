# Imagen oficial de PHP sin framework.
FROM php:8.3-cli

# Instala cURL para permitir que PHP se comunique con la API.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && rm -rf /var/lib/apt/lists/*

# Directorio de trabajo del contenedor.
WORKDIR /var/www/html

# Copia los archivos del frontend.
COPY . .

# Puerto interno definido en Docker Compose.
EXPOSE 8024

# Inicia el servidor PHP. La carpeta publico será la única raíz pública.
CMD ["php", "-S", "0.0.0.0:8024", "-t", "publico", "publico/index.php"]
