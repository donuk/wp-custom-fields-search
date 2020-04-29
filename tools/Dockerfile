FROM php:7.4-cli
WORKDIR /
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN chmod a+x wp-cli.phar 
RUN mv wp-cli.phar /bin/wp
RUN apt-get update && apt-get install -y less && apt-get clean
ENTRYPOINT [ "wp", "--allow-root" ]
CMD [ "i18n", "make-pot", "/plugin" ]
# docker build -t wpi18n . && docker run --rm -v $PWD/..:/plugin wpi18n
