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
RUN docker-php-ext-install -j$(nproc) \
    mysqli \
    pdo_mysql \
    gd \
    zip \
    mbstring \
    bcmath \
    opcache \
    sockets \
    pcntl \
    && docker-php-ext-enable opcache

# 安装Redis扩展
RUN pecl install redis && docker-php-ext-enable redis

# ============ 安装 Swoole 扩展 ============
# Swoole 5.1.4+ 支持PHP 8.3，提供协程、异步IO等能力
RUN pecl install swoole && \
    docker-php-ext-enable swoole

# ============ 安装 Phalcon 5.8 扩展 ============
# Phalcon是C语言编写的高性能PHP框架
# 5.8.x 支持PHP 8.3
RUN set -eux; \
    # 使用官方发布的 pecl 包（包含已生成的C源码）
    wget -O /tmp/phalcon-pecl.tgz \
      https://github.com/phalcon/cphalcon/releases/download/v5.8.0/phalcon-pecl.tgz; \
    pecl install /tmp/phalcon-pecl.tgz; \
    docker-php-ext-enable phalcon; \
    rm -f /tmp/phalcon-pecl.tgz

# ============ 安装 YAC 扩展 ============
# YAC (Yet Another Cache) 是PHP的无锁共享内存缓存
RUN pecl install yac-2.3.1 && \
    docker-php-ext-enable yac

# 配置YAC
RUN echo "yac.enable=1" >> /usr/local/etc/php/conf.d/yac.ini && \
    echo "yac.enable_cli=1" >> /usr/local/etc/php/conf.d/yac.ini && \
    echo "yac.keys_memory=4M" >> /usr/local/etc/php/conf.d/yac.ini && \
    echo "yac.values_memory=64M" >> /usr/local/etc/php/conf.d/yac.ini

# 安装Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

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

# 启用达梦扩展（在php.ini中配置）
# RUN echo "extension=dm.so" >> /usr/local/etc/php/conf.d/dm.ini && \
#     echo "extension=pdo_dm.so" >> /usr/local/etc/php/conf.d/dm.ini

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
