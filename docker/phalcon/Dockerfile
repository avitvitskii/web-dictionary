FROM php:7.2-fpm

RUN apt-get update && apt-get install -y git
RUN cd /home && git clone https://github.com/phalcon/cphalcon.git && cd /home/cphalcon && git checkout tags/v3.4.1 && cd /home/cphalcon/build && ./install
WORKDIR /var/www/app/

RUN docker-php-ext-install pdo_mysql
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

RUN mkdir -p /var/www/app/data; chown www-data:www-data /var/www/app/data
WORKDIR /var/www/app/


# Uncomment this lines to add Phalcon DevTools
# WORKDIR /home
# RUN git clone https://github.com/phalcon/phalcon-devtools.git && cd phalcon-devtools && ln -s $(pwd)/phalcon.php /usr/bin/phalcon && chmod ugo+x /usr/bin/phalcon
# WORKDIR /var/www/app/

EXPOSE 9000 9001