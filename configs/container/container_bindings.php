<?php

declare(strict_types=1);

use App\Auth;
use App\Config;
use App\Contracts\AuthInterface;
use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\Contracts\SessionInterface;
use App\Csrf;
use App\DataObjects\SessionConfig;
use App\EntityBindingRouteStrategy;
use App\Enums\AppEnvironment;
use App\Enums\SameSite;
use App\Enums\StorageDriver;
use App\Filters\UserFilter;
use App\RedisCache;
use App\RequestValidators\RequestValidatorFactory;
use App\Services\EntityManagerService;
use App\Session;
use Clockwork\Clockwork;
use Clockwork\DataSource\DoctrineDataSource;
use Clockwork\Storage\FileStorage;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\SimpleCache\CacheInterface;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\Twig;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension;
use Twig\Extra\Intl\IntlExtension;

return [
    App::class => function (ContainerInterface $container) {
        $addMiddlewares = require CONFIG_PATH . '/middleware.php';
        $router = require CONFIG_PATH . '/routes/web.php';

        AppFactory::setContainer($container);
        $app = AppFactory::create();

        $app->getRouteCollector()->setDefaultInvocationStrategy(
            new EntityBindingRouteStrategy($container->get(EntityManagerServiceInterface::class))
        );

        $addMiddlewares($app);
        $router($app);

        return $app;
    },

    Config::class => new Config(require CONFIG_PATH . '/app.php'),

    EntityManagerInterface::class => function (Config $config) {
        $connection = DriverManager::getConnection($config->get('doctrine.connection'));
        $ORMConfig = ORMSetup::createAttributeMetadataConfiguration(
            $config->get('doctrine.entity_dir'),
            $config->get('doctrine.dev_mode')
        );
        $ORMConfig->addFilter('user', UserFilter::class);

        return new EntityManager($connection, $ORMConfig);
    },

    EntityManagerServiceInterface::class => function (ContainerInterface $container) {
        return $container->get(EntityManagerService::class);
    },

    Twig::class => function (Config $config, ContainerInterface $container) {
        $twig = Twig::create(VIEW_PATH, [
            'cache' => STORAGE_PATH . '/cache/templates',
            'auto_reload' => AppEnvironment::isDevelopment($config->get('app_environment')),
            'autoescape' => false
        ]);
        $twig->addExtension(new IntlExtension());
        $twig->addExtension(new EntryFilesTwigExtension($container));
        $twig->addExtension(new AssetExtension($container->get('webpack_encore.packages')));
        return $twig;
    },

    'webpack_encore.packages' => fn() => new Packages(
        new Package(new JsonManifestVersionStrategy(BUILD_PATH . '/manifest.json'))
    ),

    'webpack_encore.tag_renderer' => fn(ContainerInterface $container) => new TagRenderer(
        new EntrypointLookup(BUILD_PATH . '/entrypoints.json'),
        $container->get('webpack_encore.packages')
    ),

    ResponseFactoryInterface::class => fn(App $app) => $app->getResponseFactory(),
    AuthInterface::class => fn(ContainerInterface $container) => $container->get(Auth::class),
    SessionInterface::class => function (Config $config) {
        return new Session(
            new SessionConfig(
                $config->get('session.name', 'expennies'),
                $config->get('session.flashName', 'flash'),
                $config->get('session.secure', true),
                $config->get('session.httponly', true),
                $config->get('session.samesite', SameSite::Lax),
            )
        );
    },
    RequestValidatorFactoryInterface::class => fn(ContainerInterface $container) => $container->get(
        RequestValidatorFactory::class
    ),
    'csrf' => fn(ResponseFactoryInterface $responseFactory, Csrf $csrf) => new Guard(
        $responseFactory, failureHandler: $csrf->failureHandler(), persistentTokenMode: true
    ),
    Filesystem::class => function (Config $config) {
        $adapter = match ($config->get('storage.driver')) {
            StorageDriver::Local => new LocalFilesystemAdapter(STORAGE_PATH),
        };

        return new Filesystem($adapter);
    },
    Clockwork::class => function (EntityManagerInterface $em) {
        $clockwork = new Clockwork();
        $clockwork->setStorage(new FileStorage(STORAGE_PATH . '/clockwork'));
        $clockwork->addDataSource(new DoctrineDataSource($em));

        return $clockwork;
    },
    MailerInterface::class => function (Config $config) {
        $transporter = Transport::fromDsn($config->get('mailer.dsn'));

        return new Mailer($transporter);
    },
    BodyRendererInterface::class => fn(Twig $twig) => new BodyRenderer($twig->getEnvironment()),
    RouteParserInterface::class => fn(App $app) => $app->getRouteCollector()->getRouteParser(),
    CacheInterface::class => function (Config $config) {
        $redisConfigs = $config->get('redis');

        $redis = new Redis();
        $redis->connect($redisConfigs['host'], (int) $redisConfigs['port']);
        $redis->auth($redisConfigs['password']);

//        return new RedisCache($redis);
        $adapter = new RedisAdapter($redis);

        return new Psr16Cache($adapter);
    },
];
