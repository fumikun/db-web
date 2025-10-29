# docker-compose.yml で指定されていたベースイメージを使用します
FROM php:8.2-apache

# データベース接続に必要なPHP拡張機能をインストールします
# (環境変数にMySQLの情報があるため、これらが必要だと推測されます)
RUN docker-php-ext-install pdo pdo_mysql mysqli && docker-php-ext-enable pdo_mysql mysqli

# Apache の設定ファイルをホストからコンテナにコピーします
COPY ./apache-config.conf /etc/apache2/sites-enabled/000-default.conf

# ホストの src ディレクトリの中身を、コンテナの /var/www/html にコピーします
COPY ./src /var/www/html/

# PHPが書き込むデータディレクトリを作成し、Apache (www-data) ユーザーに書き込み権限を与えます
# (docker-compose.yml の PHP_WRITE_DIR=/var/www/html/data に対応)
RUN mkdir -p /var/www/html/data && \
    chown -R www-data:www-data /var/www/html/data && \
    chmod -R 775 /var/www/html/data

# (オプション) 多くのPHPフレームワークで必要な Apache のリライトモジュールを有効にします
RUN a2enmod rewrite
