# Default Dockerfile
#
# @link     https://salesforce.pintap.id
# @contact  abyan@bisabaget.com

FROM php:7.4-apache

LABEL maintainer="SalesForce <abyan@bisabanget.com>" version="1.0" license="MIT" app.name="salesforce-api"
##
# ---------- env settings ----------
##
# --build-arg timezone=Asia/Jakarta
ARG timezone
# --build-arg commit_branch=$CI_COMMIT_BRANCH
ARG commit_branch

ENV TIMEZONE=${timezone:-"Asia/Jakarta"} \
    APP_ENV=prod \
    SCAN_CACHEABLE=(true)

RUN apt-get update && \
    apt-get install -y && \
    apt-get install -y zlib1g-dev && \
    apt-get install -y git
RUN apt-get install -y curl
RUN apt-get install -y build-essential libssl-dev zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev
RUN apt-get install zip unzip
RUN apt-get install -y libicu-dev
RUN docker-php-ext-install intl
RUN docker-php-ext-configure intl
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql
RUN a2enmod rewrite
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY contents/configure/devapi-salesforce.pintap.id.conf /etc/apache2/sites-enabled/dev-salesforce.pintap.id.conf

COPY contents/configure/uploads.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

COPY . /var/www/html/

RUN mkdir -p /var/www/html/contents/img \
    && mkdir -p /var/www/html/contents/img/avatar \
    && chmod 0777 /var/www/html/contents/img/avatar \
    && mkdir -p /var/www/html/contents/img/identity \
    && chmod 0777 /var/www/html/contents/img/identity


RUN composer install --no-dev -o

RUN chmod 0777 /var/www/html/vendor/mpdf/mpdf/tmp

COPY .env.sample.${commit_branch} .env.development

RUN echo .env.development
RUN cat .env.development
RUN echo .env.sample.${commit_branch}
RUN cat .env.sample.${commit_branch}

COPY htaccess .htaccess

RUN service apache2 restart
