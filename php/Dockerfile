# PHP 8.3 + 达梦扩展 + Swoole + Phalcon + YAC Dockerfile
FROM php:8.3-apache

# 安装系统依赖
# 添加了Swoole和Phalcon编译所需的依赖
RUN apt-get update && apt-get install -y \
    libaio-dev \
    build-essential \
    unzip \
    wget \
    git \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libnghttp2-dev \
    libpcre2-dev \
    libpq-dev \
    autoconf \
    automake \
    libtool \
    pkg-config \
    cmake \
    re2c \
    && rm -rf /var/lib/apt/lists/*

# 安装PHP常用扩展
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install -j$(nproc) \
    mysqli \
    pdo_mysql \
    gd \
    gettext \
    zip \
    mbstring \
    bcmath \
    opcache \
    sockets \
    pcntl \
    && docker-php-ext-enable opcache gd gettext

# 安装Redis扩展
RUN pecl channel-update pecl.php.net && \
    pecl install redis && docker-php-ext-enable redis

# 安装 APCu（Phalcon 需要）
RUN pecl install apcu && docker-php-ext-enable apcu

# ============ 安装 Swoole 扩展 ============
# Swoole 5.1.4+ 支持PHP 8.3，提供协程、异步IO等能力
RUN pecl install swoole && \
    docker-php-ext-enable swoole

# 安装Composer（构建 Phalcon 需要）
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ============ 安装 Phalcon 5.9 扩展 ============
# Phalcon是C语言编写的高性能PHP框架
# 5.9.x 通过 Zephir 重新生成以适配 PHP 8.3
ARG PHALCON_VERSION=5.9.3
RUN set -eux; \
    export CFLAGS="-Wno-deprecated-declarations -Wno-incompatible-pointer-types" COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_HOME=/tmp/composer PATH="/tmp/composer/vendor/bin:${PATH}"; \
    composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/; \
    composer global require phalcon/zephir; \
    wget -O /tmp/cphalcon.tar.gz "https://codeload.github.com/phalcon/cphalcon/tar.gz/refs/tags/v${PHALCON_VERSION}"; \
    mkdir -p /usr/src/cphalcon; \
    tar -xzf /tmp/cphalcon.tar.gz -C /usr/src/cphalcon --strip-components=1; \
    rm -f /tmp/cphalcon.tar.gz; \
    pecl channel-update pecl.php.net; \
    pecl install zephir_parser; \
    docker-php-ext-enable zephir_parser; \
    cd /usr/src/cphalcon; \
    php -d memory_limit=-1 "$(command -v zephir)" fullclean; \
    php -d memory_limit=-1 "$(command -v zephir)" build || { \
    echo "=== compile-errors.log ==="; \
    find /usr/src/cphalcon -name "compile-errors.log" -print -exec cat {} \; || true; \
    exit 1; \
    }; \
    cp -f ext/modules/phalcon.so "$(php-config --extension-dir)/phalcon.so"; \
    docker-php-ext-enable phalcon; \
    cd /; \
    rm -rf /usr/src/cphalcon /tmp/composer

# ============ 安装 YAC 扩展 ============
# YAC (Yet Another Cache) 是PHP的无锁共享内存缓存
RUN pecl channel-update pecl.php.net && \
    pecl install yac-2.3.1 && \
    docker-php-ext-enable yac

# 配置YAC
RUN echo "yac.enable=1" >> /usr/local/etc/php/conf.d/yac.ini && \
    echo "yac.enable_cli=1" >> /usr/local/etc/php/conf.d/yac.ini && \
    echo "yac.keys_memory=4M" >> /usr/local/etc/php/conf.d/yac.ini && \
    echo "yac.values_memory=64M" >> /usr/local/etc/php/conf.d/yac.ini

# 安装达梦PHP扩展
# 方式1：如果你已经有编译好的扩展文件，请取消注释以下行
# 扩展目录可通过 `php-config --extension-dir` 获取
# 示例：/usr/local/lib/php/extensions/no-debug-non-zts-20230831/
# COPY dm_pdo.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/
# COPY dm.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/


# 方式2：从达梦官网下载并编译（需要源码）
# 请将下载的PHP驱动源码放在php目录下
# COPY php_dm_driver/ /tmp/php_dm/
# RUN cd /tmp/php_dm && \
#     phpize && \
#     ./configure --with-dm=/opt/dmdbms && \
#     make && make install && \
#     rm -rf /tmp/php_dm

# ============ 安装达梦PHP扩展 ============
# 方式1：使用预编译的扩展文件（推荐）
# 必须先安装基础依赖库 (从 dm8 容器中提取)
COPY dm_php_pdo/libdmdpi.so /usr/lib/libdmdpi.so

# PHP 8.3 对应的扩展文件
COPY dm_php_pdo/libphp83_dm.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/dm.so
COPY dm_php_pdo/php83_pdo_dm.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/pdo_dm.so

# 启用达梦扩展（在php.ini中配置）
RUN echo "extension=dm.so" >> /usr/local/etc/php/conf.d/z-dm.ini && \
    echo "extension=pdo_dm.so" >> /usr/local/etc/php/conf.d/z-dm.ini

# 设置时区
RUN echo "date.timezone=Asia/Shanghai" >> /usr/local/etc/php/conf.d/timezone.ini

# 配置Apache
RUN a2enmod rewrite headers

# 工作目录
WORKDIR /var/www/html

# 权限设置
RUN chown -R www-data:www-data /var/www/html

# 健康检查
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

EXPOSE 80
