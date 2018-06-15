FROM php:7-fpm-alpine

RUN apk add --update \
		autoconf \
		g++ \
		libtool \
		make \
	&& docker-php-ext-install mbstring \
	&& docker-php-ext-install tokenizer \
#	&& docker-php-ext-install dom \
	&& docker-php-ext-install iconv \
#	&& docker-php-ext-install pear \
#	&& docker-php-ext-install mysqli \
	&& docker-php-ext-install opcache \
	&& apk add --update icu-dev \
	&& docker-php-ext-install intl \
	&& apk add --update postgresql-dev \
	&& docker-php-ext-install pgsql \
	&& apk del \
		postgresql-libs \
		libsasl \
		db \
	&& docker-php-ext-install sockets \
	&& touch /usr/local/etc/php/bogus.ini \
	&& pear config-set php_ini /usr/local/etc/php/bogus.ini \
	&& pecl config-set php_ini /usr/local/etc/php/bogus.ini \
	&& apk add --update \
		libevent-dev \
	&& pecl install event \
	&& docker-php-ext-enable event \
	&& mv /usr/local/etc/php/conf.d/docker-php-ext-event.ini \
		/usr/local/etc/php/conf.d/docker-php-ext-zz-event.ini \
	&& rm /usr/local/etc/php/bogus.ini

	RUN apk add --update \
		libevent-dev \
	&& docker-php-ext-install phar 

	RUN apk del \
		autoconf \
		bash \
		binutils \
		binutils-libs \
		db \
		expat \
		file \
		g++ \
		gcc \
		gdbm \
		gmp \
		isl \
		libatomic \
		libbz2 \
		libc-dev \
		libffi \
		libgcc \
		libgomp \
		libldap \
		libltdl \
		libmagic \
		libsasl \
		libstdc++ \
		libtool \
		m4 \
		make \
		mpc1 \
		mpfr3 \
		musl-dev \
		perl \
		pkgconf \
		pkgconfig \
		python \
		re2c \
		readline \
		sqlite-libs \
		zlib-dev \
         && rm -rf /tmp/* /var/cache/apk/*

##php7-tokenizer-7.0.7-25.1.x86_64
#php7-dom-7.0.7-25.1.x86_64
#php7-mysql-7.0.7-25.1.x86_64
#php7-gd-7.0.7-25.1.x86_64
#php7-ctype-7.0.7-25.1.x86_64
#php7-7.0.7-25.1.x86_64
#php7-iconv-7.0.7-25.1.x86_64
#php7-pear-7.0.7-25.1.noarch
#php7-mbstring-7.0.7-25.1.x86_64
#php7-xmlreader-7.0.7-25.1.x86_64
#php7-phar-7.0.7-25.1.x86_64
#php7-pdo-7.0.7-25.1.x86_64
#php7-zip-7.0.7-25.1.x86_64
#php7-sqlite-7.0.7-25.1.x86_64
#php7-posix-7.0.7-25.1.x86_64
#php7-json-7.0.7-25.1.x86_64
#php7-pear-Archive_Tar-7.0.7-25.1.noarch
#apache2-mod_php7-7.0.7-25.1.x86_64
#php7-xmlwriter-7.0.7-25.1.x86_64
#php7-sockets-7.0.7-25.1.x86_64
#php7-fileinfo-7.0.7-25.1.x86_64
#php7-gettext-7.0.7-25.1.x86_64
#php7-pgsql-7.0.7-25.1.x86_64
#php7-curl-7.0.7-25.1.x86_64
#php7-zlib-7.0.7-25.1.x86_64

#COPY ./config.php /usr/local/scenemodels/config.php

COPY ./scenemodels /scenemodels
RUN chown -R www-data.www-data /scenemodels

ENV PGHOST=127.0.0.1
ENV PGPORT=5432
ENV PGDATABASE=scenemodels
ENV PGUSER=flightgear
ENV PGPASSWORD=secret

