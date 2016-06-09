# This file will go away once gcloud implements fingerprinting.
FROM php-nginx
ENV APP_PHP_FPM_CONF=pubsub/php-fpm.conf \
    APP_PHP_INI=pubsub/php.ini \
    PATH=${PHP_DIR}/bin:$PATH

ENTRYPOINT ["/bin/bash pubsub/entrypoint.sh"]
