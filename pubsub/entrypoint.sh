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

if [ -z "${DOCUMENT_ROOT}" ]; then
    DOCUMENT_ROOT=${APP_DIR}
fi

sed -i "s|%%DOC_ROOT%%|${DOCUMENT_ROOT}|g" $PHP_DIR/lib/php.ini

# load pubsub-specific php.ini.
mv "${APP_DIR}/pubsub/php.ini" "${PHP_DIR}/lib/conf.d"

# start the process
${PHP_DIR}/bin/php ${APP_DIR}/pubsub/worker.php
