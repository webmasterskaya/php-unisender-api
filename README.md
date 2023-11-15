# PHP-SDK для работы с API Unisender

Легкий и понятный инструмент, с минимальным набором зависемостей, для быстрого построения интеграции с Unisender API.

## DISCLAIMER

Сколько решений для работы с http api вы знаете? Можно бесконечно перечислять библиотеки, которые делают одно и то же: `guzzlehttp/guzzle`, `php-http/curl-client`, `symfony/http-client`, `laminas/laminas-http` и ещё десятки других!
И икогда ваш проект растёт и количество зависимостей увеличивается, то разработчик сталкивается с сущим адом, когда ему приходится "подружить", например, guzzle7 и guzzle5.
Так же существует проблема CSM и монолитных фреймворков, которые имеют собственные реализации PSR'ов. Зачем тащить guzzle в Joomla, если там есть свой встроенный http-client?

В отличии от большенства решений в интернете, **эта библиотка автоматически обнаружит и подключит любой загруженный в проект класс**, реализующий PSR-18 HTTP Client, PSR-17 HTTP Factories и PSR-3 Logs.

Список автоматически обнаруживаемых
реализаций: [PSR-18](https://github.com/psr-discovery/http-client-implementations#implementations), [PSR-17](https://github.com/psr-discovery/http-factory-implementations#implementations), [PSR-3](https://github.com/psr-discovery/log-implementations#implementations)

И даже если в списке обнаруживаемых классов, нет того инструмента, который вы используете в проекте, вы всегда можете его зарегистрировать в зависимостях и не тянуть лишние пакеты в проекты. См. [как подключать свои классы](#как_подключать_свои_классы)

### Как подключать свои классы
Если вы используете фреймворк или CMS, в котором есть собственная реализация одного из перечисленных PSR, то её можно подключить к библиотеке вручную.

#### Подключение логгирования Joomla
```php
\PsrDiscovery\Implementations\Psr3\Logs::add(
    \PsrDiscovery\Entities\CandidateEntity::create(
        'joomla/log',
        '~1',
        static function (string $class = '\Joomla\Log\Log') {
            return call_user_func([$class, 'createDelegatedLogger']);
        }
    )
);

$log = PsrDiscovery\Discover::log(); // В переменную $log будет записана ссылка на синглтон \Joomla\Log\DelegatingPsrLogger, который реализует интерфейс \Psr\Log\AbstractLogger
```

#### Подключение HTTP Client Joomla
```php
\PsrDiscovery\Implementations\Psr18\Clients::add(
    \PsrDiscovery\Entities\CandidateEntity::create(
        'joomla/http',
        '~3',
        static function (string $class = '\Joomla\Http\Http') {
            return new $class;
        }
    )
);

$client = PsrDiscovery\Discover::httpClient(); // В переменную $client будет записана ссылка на экземпляр \Joomla\Http\Http, который реализует интерфейс \Psr\Http\Client\ClientInterface
```

#### Подключение HTTP Client Bitrix

```php
\PsrDiscovery\Implementations\Psr18\Clients::add(
    \PsrDiscovery\Entities\CandidateEntity::create(
        'bitrix/main',
        '~23',
        static function (string $class = '\Bitrix\Main\Web\HttpClient') {
            return new $class;
        }
    )
);

$client = PsrDiscovery\Discover::httpClient(); // В переменную $client будет записана ссылка на экземпляр \Bitrix\Main\Web\HttpClient, который реализует интерфейс \Psr\Http\Client\ClientInterface
```