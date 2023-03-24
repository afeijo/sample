FROM ubuntu:22.04

LABEL MAINTAINER=alessandrofeijo

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update
RUN apt-get install software-properties-common -yq
RUN add-apt-repository ppa:ondrej/php

RUN apt-get update && apt-get install -yq --no-install-recommends \
  apt-utils curl git tig autoconf \
  # Install apache
  apache2 libapache2-mod-php8.2 \
  # Install php 8.2
  php8.2-cli php8.2-curl php8.2-apc \
  php8.2-fpm php8.2-gd php8.2-ldap \
  php8.2-memcache libmemcached-dev php-dev \
  php8.2-mbstring php8.2-mysql php8.2-soap \
  php8.2-sqlite3 php8.2-xml php8.2-zip \
  php8.2-intl php-imagick php-geoip php8.2-imap \
  php-uploadprogress libjpeg-dev php-pear \
  # Install tools
  openssl mysql-client \
  locales ca-certificates
RUN update-alternatives --set php /usr/bin/php8.2
RUN apt-get install -y vim wget tree unzip gnupg2 patch htop make redis
RUN pecl i apcu

# Add our drupal cron shell script
ADD .docker/drupal_cron.sh /root/drupal_cron.sh
RUN chmod 0644 /root/drupal_cron.sh

# Install cron and run drupal cron every 3 hours
RUN apt-get install -y cron
RUN crontab -l | { cat; echo "* */3 * * * bash /root/drupal_cron.sh\n0 0 * * * dr -r="; } | crontab -

# Install composer from oficial image
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Install drush
RUN wget -O drush.phar https://github.com/drush-ops/drush-launcher/releases/download/0.4.2/drush.phar && \
  chmod +x drush.phar && \
  mv drush.phar /usr/local/bin/drush
RUN php -r "readfile('https://drupalconsole.com/installer');" > /usr/bin/drupal

# Set locales
RUN locale-gen pt_BR.UTF-8 en_US.UTF-8 en_GB.UTF-8

RUN a2enmod rewrite headers ssl proxy_fcgi setenvif
RUN a2enconf php8.2-fpm
RUN systemctl enable php8.2-fpm

RUN apt-get -yq install libmcrypt-dev libzip-dev libjpeg-dev
RUN apt-get -y upgrade && apt-get -y autoclean && apt-get -y autoremove
# RUN curl -sL https://deb.nodesource.com/setup_10.x | bash -
# RUN apt-get -yq install #nodejs
# RUN npm install -g yarn gulp gulp-cli

# Install xdebug
# RUN yes | pecl install xdebug \
#   && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
#   && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
#   && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini

RUN rm -rf /var/www/html/*

# My shell improvements
RUN cd ~ \
  && wget https://gist.githubusercontent.com/afeijo/0c8c16c1726a27d4d1f4476c1f7cf53d/raw/118a33d210ec5d1d1ee1edad49679a83154e8343/.bash_aliases \
  && wget https://gist.github.com/afeijo/8601619/raw/a13d121b0abe2e4d0603612b744238c77b7d59ad/.ps1_color \
  && wget https://raw.githubusercontent.com/git/git/master/contrib/completion/git-prompt.sh \
  && echo source ~/git-prompt.sh >> .bashrc \
  && echo source ~/.bash_aliases >> .bashrc \
  && echo source ~/.ps1_color >> .bashrc

RUN echo -e "\nalias dr='drush'" >> ~/.bash_aliases

COPY .docker/apache-drupal.conf /etc/apache2/sites-enabled/000-default.conf
COPY .docker/php.ini /etc/php/8.2/apache2/conf.d/
COPY .docker/opcache.ini /etc/php/8.2/apache2/conf.d/
RUN echo "upload_max_filesize = 100M" >> /etc/php/8.2/apache2/php.ini
RUN echo "post_max_size = 100M" >> /etc/php/8.2/apache2/php.ini

WORKDIR /app/web

RUN mkdir -p ../private ../config/sync sites/default/files/translations
RUN chown www-data -R ../private ../config sites/default/files/translations

CMD cron;service redis-server start;service php8.2-fpm start;apachectl -D FOREGROUND
