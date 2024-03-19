FROM composer:latest

COPY / /hive
WORKDIR /hive
RUN docker-php-ext-install mysqli
RUN composer dump-autoload -o

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000"]
