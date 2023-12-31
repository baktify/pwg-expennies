FROM php:8.1-fpm

ARG USER
ARG USER_ID
ARG GROUP_ID

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    curl \
    vim \
    libicu-dev

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-configure intl
RUN docker-php-ext-install pdo pdo_mysql intl

RUN pecl install xdebug \
    && pecl install redis \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-enable redis

COPY xdebug.ini "${PHP_INI_DIR}/conf.d"

# Install node and npm
# Installing a specific version of node directly is dificult. Use `nvm` to
# install it (which installs both `node` and `npm`)

# Set this to any desired version
ENV NODE_VERSION 16.18.1
# Can be anything, but this is a good default
ENV NVM_DIR /usr/local/nvm
# Must match one of the tag versions on https://github.com/nvm-sh/nvm/tags
ENV NVM_VERSION 0.39.5

RUN mkdir -p $NVM_DIR \
  && curl https://raw.githubusercontent.com/creationix/nvm/v$NVM_VERSION/install.sh | bash \
  && . $NVM_DIR/nvm.sh \
  && nvm install $NODE_VERSION \
  && nvm alias default $NODE_VERSION \
  && nvm use default \
  && node -v \
  && npm -v

# Update the $PATH to make your installed `node` and `npm` available!
ENV PATH $NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH

RUN groupadd --force -g $GROUP_ID $USER
RUN useradd -ms /bin/bash --no-user-group -g $GROUP_ID -u 1337 $USER
RUN usermod -u $USER_ID $USER

USER $USER
