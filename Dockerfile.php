FROM php:7.3-fpm-stretch
RUN apt-get -y update && apt-get -y install --no-install-recommends \
  libcurl4-openssl-dev \
  libicu-dev \
  libjpeg-dev \
  libpng-dev \
  libpq-dev \
  libssl-dev \
  libxml2-dev \
  libzip-dev

#RUN true && \
#       docker-php-ext-enable event

RUN docker-php-ext-install \
          mbstring \
          tokenizer \
          iconv \
          opcache \
          intl \
          sockets \
          xmlwriter \
          phar \
          dom \
          gd \
          ctype \
          zip \
          posix \
          json \
          fileinfo \
          gettext \
          curl \
          pgsql

ENV PGHOST=127.0.0.1
ENV PGPORT=5432
ENV PGDATABASE=scenemodels
ENV PGUSER=flightgear
ENV PGPASSWORD=secret

COPY ./scenemodels /scenemodels
RUN chown -R root.root /scenemodels
RUN find /scenemodels -type d -not -perm 755 -exec chmod 755 {} \;
RUN find /scenemodels -type f -not -perm 644 -exec chmod 644 {} \;

VOLUME /scenemodels
