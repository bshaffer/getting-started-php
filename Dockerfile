# This file will go away once gcloud implements fingerprinting.
FROM gcr.io/php-mvm-a/php-nginx:latest

# The docker image will configure the document root according to this
# environment variable.
ENV DOCUMENT_ROOT /app/web

# custom entrypoint script
COPY pubsub_entrypoint.sh /pubsub_entrypoint.sh
RUN chmod +x /pubsub_entrypoint.sh

ENTRYPOINT ["/pubsub_entrypoint.sh"]
