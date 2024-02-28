# PHP API SDK - Интеграция сервиса рассылок Unisender

PHP API для [интеграции сервиса для рассылок Unisender по API](https://www.unisender.com/ru/support/category/api/).
Интеграция с Unisender API значительно упрощает решение задач по email-маркетингу, позволяет полностью абстрагироваться от сетевого уровня и работать непосредственно с методами API.

---

Unisender API — это специальный интерфейс для разработчиков, который позволяет интегрировать возможности электронной рассылки практически с любым открытым веб-сервисом.

API для массовых email-рассылок позволяет управлять списками контактов, создавать и отправлять разные типы сообщений, смотреть статистику, а также предоставляет возможности для работы партнеров.

Пользоваться API можно бесплатно на любом аккаунте. Чтобы получить доступ к API для email-рассылок нужен ключ, который можно скопировать в [личном кабинете](https://cp.unisender.com/ru/v5/user/info/api).

---

<details>

<summary>DISCLAIMER</summary>

Сколько решений для работы с http api вы знаете? Можно бесконечно перечислять библиотеки, которые делают одно и то же: `guzzlehttp/guzzle`, `php-http/curl-client`, `symfony/http-client`, `laminas/laminas-http` и ещё десятки других!

А какое из них используете вы? А какое используют зависимости вашего проекта? Сколько библиотек, для реализации одного и того же PSR вы тянете в свой проект?

В отличие от большинства решений в интернете, **эта библиотека не принуждает вас к использованию какой-либо конкретной реализации PSR-18**, что делает её гораздо более гибкой и упрощает интеграцию в любое приложение.

</details>

---

### Перед началом работы

- Требуется PHP 7.4 или выше.
- Требуется наличие реализации [PSR-18 (HTTP Client)](https://www.php-fig.org/psr/psr-18/).

В SDK применяется спецификация [PSR-18 (HTTP-client)](https://www.php-fig.org/psr/psr-18/).
Это значит, что в вашем проекте должны быть зарегистрированы классы, реализующие эту спецификацию (например, [Guzzle](https://github.com/guzzle/guzzle)).

Для автоматического обнаружения зависимостей используются пакеты [psr-discovery](https://github.com/psr-discovery). Подробнее, об [автоматическом обнаружении зависимостей](#автоматическое-обнаружение-зависимостей)

---

### Установка

Установка осуществляется с помощью пакетного менеджера Composer

```shell
composer require webmasterskaya/php-unisender-api
```

---

### Инициализация

Для начала работы, создайте экземпляр клиента:

```php
$client = new \Webmasterskaya\Unisender\Client(string $api_key[, ?array $options]);
```

**Параметры:**

- `api_key` (string): API-ключ к вашему кабинету. Получить ключ можно в [личном кабинете](https://cp.unisender.com/ru/v5/user/info/api).
- `options` (array): массив с настройками клиента. Опциональное. По-умолчанию - пустой массив. Допустимые поля:
    - `lang` (string): язык сообщений сервера API (в данный момент поддерживается `ru`, `en`, `ua`). По-умолчанию - `ru`.

Названия методов SDK совпадает с методами API, которые описаны в [документации](https://www.unisender.com/ru/support/category/api/).

### [Доступные методы](docs/methods/README.md)

- #### [Работа со списками контактов](docs/methods/LISTS.md)
- #### [Создание и отправка сообщений](docs/methods/MESSAGES.md)
- #### [Получение статистики](docs/methods/STATISTICS.md)
- #### [Работа с шаблонами](docs/methods/TEMPLATES.md)
- #### [Работа с дополнительными полями и метками](docs/methods/FIELDS.md)
- #### [Работа с дополнительными полями и метками](docs/methods/FIELDS.md)
- #### [Работа с заметками](docs/methods/NOTES.md)

---

### Автоматическое обнаружение зависимостей

Ознакомиться со списком автоматически обнаруживаемых библиотек можно по ссылкам ниже:

- [PSR-18 (HTTP Client)](https://github.com/psr-discovery/http-client-implementations?tab=readme-ov-file#implementations)

Если в списке автоматического обнаружения нет библиотеки, используемой на вашем проекте, то её нужно зарегистрировать самостоятельно. Подробнее, о [ручной регистрации зависимостей](#ручная-регистрация-зависимостей)

---

### Ручная регистрация зависимостей

#### Пример регистрации HttpClient в Bitrix

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
```

#### Пример регистрации HttpClient в Joomla

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
```