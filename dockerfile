FROM php:latest

WORKDIR /hive

COPY *.php /hive

RUN apt-get update -y
RUN docker-php-ext-install mysqli

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000"]
