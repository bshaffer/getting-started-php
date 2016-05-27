<?php

/*
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Create a new Silex Application with Twig.  Configure it for debugging.
 * Follows Silex Skeleton pattern.
 */

use Google\Cloud\Samples\Bookshelf\DataModel\CloudSql;
use Google\Cloud\Samples\Bookshelf\DataModel\Datastore;
use Google\Cloud\Samples\Bookshelf\DataModel\MongoDb;
use Google\Cloud\Logger\AppEngineFlexHandler;
use Google\Cloud\PubSub\PubSubClient;
use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\Yaml\Yaml;

$app = new Application();

// register twig
$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../templates',
    'twig.options' => array(
        'strict_variables' => false,
    ),
));

// register the url generator
$app->register(new UrlGeneratorServiceProvider);

// parse configuration
$config = getenv('BOOKSHELF_CONFIG') ?:
    __DIR__ . '/../config/' . 'settings.yml';

$app['config'] = Yaml::parse(file_get_contents($config));

// register the session handler
// [START session]
$app->register(new SessionServiceProvider);
// fall back on PHP's "session.save_handler" for session storage
$app['session.storage.handler'] = null;
$app['user'] = function ($app) {
    /** @var Symfony\Component\HttpFoundation\Session\Session $session */
    $session = $app['session'];

    return $session->has('user') ? $session->get('user') : null;
};
// [END session]

// add AppEngineFlexHandler on prod
// [START logging]
$app->register(new Silex\Provider\MonologServiceProvider());
if (isset($_SERVER['GAE_VM']) && $_SERVER['GAE_VM'] === 'true') {
    $app['monolog.handler'] = new AppEngineFlexHandler();
} else {
    $app['monolog.handler'] = new Monolog\Handler\ErrorLogHandler();
}
// [END logging]

// create the google authorization client
// [START google_client]
$app['google_client'] = function ($app) {
    $client = new Google_Client([
        'client_id'     => $app['config']['google_client_id'],
        'client_secret' => $app['config']['google_client_secret'],
    ]);

    $client->setHttpClient(new GuzzleHttp\Client(['proxy' => 'localhost:8888', 'verify' => false]));

    return $client;
};
// [END google_client]

// [START pubsub_client]
$app['pubsub_client'] = function ($app) {
    // create the pubsub client
    $pubsub = new PubSubClient([
        'projectId' => $app['config']['google_project_id'],
    ]);

    return $pubsub;
};
// [END pubsub_client]

// determine the datamodel backend using the app configuration
$app['bookshelf.model'] = function ($app) {
    /** @var array $config */
    $config = $app['config'];

    // Data Model
    switch ($config['bookshelf_backend']) {
        case 'mongodb':
            return new MongoDb(
                $config['mongo_url'],
                $config['mongo_database'],
                $config['mongo_collection']
            );
        case 'datastore':
            return new Datastore(
                $config['google_project_id']
            );
        case 'cloudsql':
            return new CloudSql(
                $config['mysql_dsn'],
                $config['mysql_user'],
                $config['mysql_password']
            );
        default:
            throw new \Exception("Invalid BOOKSHELF_DATA_BACKEND given: $config[bookshelf_backend]. "
                        . "Possible values are cloudsql, mongodb, or datastore.");
    }
};

// Turn on debug locally
if (in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', 'fe80::1', '::1'])
    || php_sapi_name() === 'cli-server'
) {
    $app['debug'] = true;
} else {
    $app['debug'] = filter_var(getenv('BOOKSHELF_DEBUG'),
                               FILTER_VALIDATE_BOOLEAN);
}

// add service parameters
$app['bookshelf.page_size'] = 10;

return $app;