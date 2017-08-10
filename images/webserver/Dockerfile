# Modified from https://hub.docker.com/r/petronetto/php-nginx-alpine/~/dockerfile/
# by Juliano Petronetto <juliano@petronetto.com.br>

FROM alpine:3.6

MAINTAINER Rom Luengwattanapong <s1567783@ed.ac.uk>

# Install packages
RUN apk --update add --no-cache \
tzdata \
nginx \
curl \
supervisor \
php7 \
php7-dom \
php7-fpm \
php7-mbstring \
php7-mcrypt \
php7-tokenizer \
php7-opcache \
php7-pdo \
php7-pdo_mysql \
php7-pdo_pgsql \
php7-pdo_sqlite \
php7-xml \
php7-simplexml \
php7-phar \
php7-openssl \
php7-json \
php7-curl \
php7-ctype \
php7-session \
php7-gd \
php7-zlib \
php7-fileinfo \
php7-zip \
php7-mbstring \
docker \
git \
bash \
coreutils \
shadow \
python3 && \
rm -rf /var/cache/apk/*

# Configuring PHP Plugins
# RUN echo "extension=php_xmlrpc.dll" >> /etc/php7/php.ini

# Configuring timezones
RUN \
cp /usr/share/zoneinfo/Europe/London /etc/localtime && \
echo "Europe/London" >  /etc/timezone && \
apk del tzdata && rm -rf /var/cache/apk/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Configure Nginx
COPY config/nginx/nginx.conf /etc/nginx/nginx.conf
COPY config/nginx/default /etc/nginx/sites-enabled/default

# Configure PHP-FPM
COPY config/php/php.ini /etc/php7/php.ini
COPY config/php/www.conf /etc/php7/php-fpm.d/www.conf

# Configure supervisord
COPY config/supervisord.conf /etc/supervisord.conf

# Create application folder
RUN mkdir -p /app

# Setting the workdir
WORKDIR /app

# Set UID for www user to 1000
RUN addgroup -g 1000 -S www \
    && adduser -u 1000 -D -S -G www -h /app -g www www \
    && chown -R www:www /var/lib/nginx \
    && chown -R www:www /var/tmp/nginx

# Supervisord
ADD config/start.sh /start.sh
RUN chmod +x /start.sh

VOLUME ["/app", "/var/run/docker.sock"]
EXPOSE 80 443

# Start Supervisord
CMD ["/start.sh"]

# docker run -v /mnt/hgfs/Project/website:/app -v /var/run/docker.sock:/var/run/docker.sock -p 8080:80 -p 4433:443 -itd webserver
