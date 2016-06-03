#!/bin/bash

# Copyright 2015 Google Inc.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.


# This file configure the runtime dynamically based on the contents
# and environment variables that user provides.

set -xe

## START supervisord.conf override ##
cp ${APP_DIR}/supervisord.conf /etc/supervisor/supervisord.conf
## END supervisord.conf override ##

# Configure memcached based session.
if [ -n ${MEMCACHE_PORT_11211_TCP_ADDR} ] && [ -n ${MEMCACHE_PORT_11211_TCP_PORT} ]; then
    cat <<EOF > ${PHP_DIR}/lib/conf.d/memcached-session.ini
session.save_handler=memcached
session.save_path="${MEMCACHE_PORT_11211_TCP_ADDR}:${MEMCACHE_PORT_11211_TCP_PORT}"
EOF
fi

# Configure document root in php.ini with DOCUMENT_ROOT
# environment variable or APP_DIR if DOCUMENT_ROOT is not set.

if [ -z "${DOCUMENT_ROOT}" ]; then
    DOCUMENT_ROOT=${APP_DIR}
fi

sed -i "s|%%DOC_ROOT%%|${DOCUMENT_ROOT}|g" $PHP_DIR/lib/php.ini

# start the process
php ${APP_DIR}/src/pubsub.php

# exec "$@"

