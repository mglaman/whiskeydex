# syntax = docker/dockerfile:1.2

# Composer build step.
FROM composer:2 as vendor

WORKDIR /app

COPY config/ config/
COPY composer.json composer.json
COPY composer.lock composer.lock
COPY bootstrap.php bootstrap.php
COPY worker.php worker.php
COPY src/ src/
COPY web/ web/

RUN set -eux; \
    export COMPOSER_HOME="$(mktemp -d)"; \
    composer install --no-dev --ignore-platform-reqs; \
    rm -rf "$COMPOSER_HOME";

FROM php:8.1-apache-buster as build

ARG RENDER_GIT_COMMIT
ENV DEPLOYMENT_IDENTIFIER=$RENDER_GIT_COMMIT

# install the PHP extensions we need
RUN set -eux; \
	\
	if command -v a2enmod; then \
		a2enmod rewrite; \
	fi; \
	\
	savedAptMark="$(apt-mark showmanual)"; \
	\
	apt-get update; \
	apt-get install -y --no-install-recommends \
		libfreetype6-dev \
		libjpeg-dev \
		libpng-dev \
		libpq-dev \
		libzip-dev \
    libxml2-dev \
	; \
	\
	docker-php-ext-configure gd \
		--with-freetype \
		--with-jpeg=/usr \
	; \
	\
	docker-php-ext-install -j "$(nproc)" \
		gd \
		opcache \
		pdo_mysql \
		pdo_pgsql \
		zip \
	; \
	\
# reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
	apt-mark auto '.*' > /dev/null; \
	apt-mark manual $savedAptMark; \
	ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
		| awk '/=>/ { print $3 }' \
		| sort -u \
		| xargs -r dpkg-query -S \
		| cut -d: -f1 \
		| sort -u \
		| xargs -rt apt-mark manual; \
	\
	apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
	rm -rf /var/lib/apt/lists/*

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=60'; \
		echo 'opcache.fast_shutdown=1'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini
RUN { \
		echo 'memory_limit=256M'; \
		echo 'upload_max_filesize=10M'; \
	} > $PHP_INI_DIR/conf.d/zzz.ini

RUN set -eux; \
    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf; \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf;

RUN pecl install apcu; docker-php-ext-enable apcu

# Copy precompiled codebase into the container.
COPY --from=vendor /app/ /var/www/html/
WORKDIR /var/www/html/

RUN chmod 444 web/sites/default/settings.php
RUN mkdir -p web/sites/default/files
RUN mkdir -p private
RUN chown -R www-data:www-data private
RUN chown -R www-data:www-data web/sites/default/files

# Adjust the Apache docroot.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/web

EXPOSE 80

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
