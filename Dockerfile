FROM dunglas/frankenphp
ENV SERVER_NAME=":3000"
RUN install-php-extensions pdo_mysql mysqli
COPY . /app/public