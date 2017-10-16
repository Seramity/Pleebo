<?php

use App\View\Factory;
use Respect\Validation\Validator as v;
use Dotenv\Dotenv as dotenv;
use Illuminate\Pagination\Paginator;
use App\Mail\Mailer\Mailer;

use Symfony\Component\VarDumper\VarDumper;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\{HtmlDumper, CliDumper};


session_start();

require __DIR__ . '/../vendor/autoload.php';

//GET ENV VARIABLES
$dotenv = new Dotenv(__DIR__ . '/../');
$dotenv->load();

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN),

        'app' => [
            'name' => getenv('APP_NAME'),
            'version' => getenv('APP_VERSION'),
            'baseUrl' => getenv('APP_BASEURL'),
            'debug' => filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN),
            'cache' => filter_var(getenv('APP_CACHE'), FILTER_VALIDATE_BOOLEAN),
            'registration_enabled' => filter_var(getenv('REGISTRATION'), FILTER_VALIDATE_BOOLEAN),
            'max_file_size' => getenv('APP_MAX_FILE_SIZE')
        ],

        'auth' => [
            'remember' => 'user_r'
        ],

        'db' => [
            'driver' => 'mysql',
            'host' => getenv('DB_HOST'),
            'port' => getenv('DB_PORT'),
            'database' => getenv('DB_DATABASE'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ],

        'mail' => [
            'host' => getenv('MAIL_HOST'),
            'port' => getenv('MAIL_PORT'),
            'from' => [
                'name' => getenv('MAIL_FROMNAME'),
                'address' => getenv('MAIL_FROM')
            ],
            'username' => getenv('MAIL_USERNAME'),
            'password' => getenv('MAIL_PASSWORD')
        ],

        'paths' => [
            'avatar' => getenv('PATH_AVATAR_UPLOAD'),
            'answer_image' => getenv('PATH_ANSWER_IMAGE_UPLOAD')
        ]
    ]
]);

$container = $app->getContainer();

// CONNECT DB WITH ELOQUENT
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function ($container) use ($capsule) {
    return $capsule;
};


$container['auth'] = function ($container) {
    return new \App\Auth\Auth;
};

$container['flash'] = function ($container) {
    return new \Slim\Flash\Messages;
};


// MODELS
$container['user'] = function ($container) {
    return new \App\Models\User;
};


// SET VIEWS TO TWIG
$container['view'] = function ($container) {
    $view = Factory::getEngine($container);

    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));

    // APP GLOBALS
    $view->getEnvironment()->addGlobal('app', $container['settings']['app']);
    $view->addExtension(new \App\View\DebugExtension);

    // SEND AUTH INTO VIEWS
    $view->getEnvironment()->addGlobal('auth', [
        'check' => $container->auth->check(),
        'user' => $container->auth->user(),
    ]);


    // SEND FLASH MESSAGES INTO VIEWS
    $view->getEnvironment()->addGlobal('flash', $container->flash);


    return $view;
};

// CONTROLLERS
require __DIR__ . '/controllers.php';


// 404 ERROR HANDLING
$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $container->view->render($response, 'errors/404.twig')
            ->withStatus(404);
    };
};
// 500 ERROR HANDLING
/*$container['phpErrorHandler'] = function ($container) {
return function ($request, $response) use ($container) {
        return $container->view->render($response, 'errors/500.twig')
            ->withStatus(500);
    };
};
// APP ERROR HANDLING
$container['errorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {
        $data = ['error' => $exception->getMessage()];
        return $container->view->render($response, 'errors/app_error.twig', $data)
            ->withStatus(500);
    };
};*/// REMOVED TEMPORARILY FOR IMPROVED ERROR REPORTING
    // UNCOMMENT THIS FOR PRODUCTION


$container['validator'] = function ($container) {
    return new App\Validation\Validator;
};

$container['csrf'] = function ($container) {
  return new \Slim\Csrf\Guard;
};

// RANDOMLIB FOR AUTH HASHING
$container['hash'] = function ($container) {
  return new \App\Hash\Hash;
};
$container['randomlib'] = function ($container) {
  return new \RandomLib\Factory;
};
$container['securitylib'] = function ($container) {
    return new SecurityLib\Strength(SecurityLib\Strength::MEDIUM);
};

// MAILER
$container['mail'] = function ($container) {
    $config = $container->get('settings')['mail'];

    $transport = (new Swift_SmtpTransport($config['host'], $config['port']))
        ->setUsername($config['username'])
        ->setPassword($config['password']);

    $swift = new Swift_Mailer($transport);

    return (new Mailer($swift, $container->view))
        ->from($config['from']['address'], $config['from']['name']);
};


// MIDDLEWARE
$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\FormInputMiddleware($container));
$app->add(new \App\Middleware\CsrfMiddleware($container));
$app->add(new \App\Middleware\RememberMeMiddleware($container));

$app->add($container->csrf);



// SET CUSTOM RULES INTO RESPECT VALIDATOR
v::with('App\\Validation\\Rules\\');


require __DIR__ . '/../app/routes.php';


// PAGINATION
Paginator::viewFactoryResolver(function() {
    return new Factory;
});
// USE defaultSimpleView FOR Paginator
// USE defaultView FOR LengthAwarePaginator
Paginator::defaultSimpleView('templates/pagination/default_simple.twig');
Paginator::defaultView('templates/pagination/default.twig');

Paginator::currentPathResolver(function() {
    return isset($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : '/';
});
Paginator::currentPageResolver(function() {
    return isset($_GET['page']) ? $_GET['page'] : 1;
});


// SYMFONY VARDUMPER
VarDumper::setHandler(function ($var) {
    $cloner = new VarCloner;

    $htmlDumper = new HtmlDumper;
    $htmlDumper->setStyles([
        'default' => 'background-color:#fff; color:#FF8400; line-height:1.2em; font:12px Menlo, Monaco, Consolas, monospace; word-wrap: break-word; white-space: pre-wrap; position:relative; z-index:99999; word-break: break-all',
        'public' => 'color:#555',
        'protected' => 'color:#555',
        'private' => 'color:#555',
    ]);

    $dumper = PHP_SAPI === 'cli' ? new CliDumper : $htmlDumper;

    $dumper->dump($cloner->cloneVar($var));
});