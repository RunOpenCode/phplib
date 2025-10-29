FROM php:8.4-fpm

#####################################################################################
#                                                                                   #
#                         Configure env vars and timezone                           #
#                                                                                   #
#####################################################################################
ARG SYSTEM_TIMEZONE=UTC
ARG PHP_MEMORY_LIMIT=1024M
ARG PHP_ERROR_LOG=/var/www/html/var/php/php.error.log

ENV SYSTEM_TIMEZONE=${SYSTEM_TIMEZONE}
ENV TZ="${SYSTEM_TIMEZONE}"
ENV PHP_MEMORY_LIMIT=${PHP_MEMORY_LIMIT}
ENV PHP_ERROR_LOG=${PHP_ERROR_LOG}

RUN ln -snf /usr/share/zoneinfo/${SYSTEM_TIMEZONE} /etc/localtime && echo ${SYSTEM_TIMEZONE} > /etc/timezone

#####################################################################################
#                                                                                   #
#                                 Setup PHP & Extensions                            #
#                                                                                   #
#####################################################################################
ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update --allow-releaseinfo-change

RUN apt-get install -y --no-install-recommends apt-utils autoconf bash-completion \
    ca-certificates curl filter ftp gettext git gnupg libbz2-dev libzip-dev  \
    libbz2-dev libedit2 libfreetype6-dev libxml2 libjpeg62-turbo-dev  \
    libpng-dev libxslt-dev libonig-dev libwebp-dev libxpm-dev  \
    libmagickwand-dev libicu-dev make openssl unzip zip zlib1g-dev xz-utils \
    xsltproc wget poppler-utils procps supervisor openssh-client supervisor \
    ghostscript

RUN docker-php-ext-configure gd --enable-gd --with-webp --with-jpeg --with-xpm --with-freetype \
    && docker-php-ext-install bcmath bz2 gd intl mbstring pdo pdo_mysql  \
    mysqli pcntl posix sockets xsl zip opcache \
    && pecl install apcu -o -f redis \
    && docker-php-ext-enable apcu redis

ADD php.ini.template /usr/local/etc/php/conf.d/custom.ini

#####################################################################################
#                                                                                   #
#                                     Setup XDebug                                  #
#                                                                                   #
#####################################################################################

RUN pecl install xdebug

ADD xdebug.ini.template /usr/local/etc/php/conf.d/xdebug.ini

#####################################################################################
#                                                                                   #
#                                   Setup Composer                                  #
#                                                                                   #
#####################################################################################

ENV COMPOSER_HOME=/composer
ENV PATH=/composer/vendor/bin:$PATH
ENV COMPOSER_ALLOW_SUPERUSER=1

# Setup the Composer installer
RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
    && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
    && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
    && php /tmp/composer-setup.php  \
    && mv /var/www/html/composer.phar /usr/local/bin/composer.phar  \
    && ln -s /usr/local/bin/composer.phar /usr/local/bin/composer  \
    && chmod +x /usr/local/bin/composer

#####################################################################################
#                                                                                   #
#                                   Dynamic config                                  #
#                                                                                   #
#####################################################################################

# Value below could change based on the PHP version, so it is required to be verified
# after each bump of the PHP version.
ENV PHP_EXTENSION_DIR=/usr/local/lib/php/extensions/no-debug-non-zts-20240924

#####################################################################################
#                                                                                   #
#                             Install tooling globally                              #
#                                                                                   #
#####################################################################################

RUN composer global config --no-plugins allow-plugins.infection/extension-installer true && \
    composer global require infection/infection && \
    composer global require maglnet/composer-require-checker && \
    composer global require icanhazstring/composer-unused && \
    composer global require phpmetrics/phpmetrics && \
    export PATH=~/.config/composer/vendor/bin:$PATH

#####################################################################################
#                                                                                   #
#                                   Finalize setup                                  #
#                                                                                   #
#####################################################################################

WORKDIR /var/www/html