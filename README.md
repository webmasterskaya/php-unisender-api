# PHP SDK для API UniSender

[![CI](https://github.com/webmasterskaya/php-unisender-api/actions/workflows/ci.yml/badge.svg)](https://github.com/webmasterskaya/php-unisender-api/actions/workflows/ci.yml)
[![Stable version](https://poser.pugx.org/webmasterskaya/php-unisender-api/v/stable)](https://packagist.org/packages/webmasterskaya/php-unisender-api)
[![PHP version](https://poser.pugx.org/webmasterskaya/php-unisender-api/require/php)](https://packagist.org/packages/webmasterskaya/php-unisender-api)
[![License](https://poser.pugx.org/webmasterskaya/php-unisender-api/license)](LICENSE)

Неофициальный PHP SDK для [API UniSender](https://www.unisender.com/ru/support/category/api/). Клиент отправляет безопасные POST-запросы, не привязан к конкретной HTTP-библиотеке и поддерживает все API-методы без ожидания обновления SDK.

> Это независимый проект сообщества. Он не связан с UniSender, не поддерживается и не одобрен компанией.

## Требования

- PHP 8.2 или новее;
- Composer;
- реализация PSR-18 и фабрики PSR-17.

Совместимые зависимости обнаруживаются автоматически через `php-http/discovery`. Для нового проекта проще всего использовать Guzzle.

## Установка

```bash
composer require webmasterskaya/php-unisender-api guzzlehttp/guzzle
```

## Быстрый старт

```php
<?php

require 'vendor/autoload.php';

use Webmasterskaya\Unisender\Client;

$unisender = new Client((string) getenv('UNISENDER_API_KEY'));

$response = $unisender->send('createList', [
    'title' => 'Новости компании',
]);

$listId = $response['result']['id'];
```

Метод API можно вызвать и динамически:

```php
$response = $unisender->getFields();
$response = $unisender->subscribe([
    'list_ids' => '12345',
    'fields' => [
        'email' => 'user@example.com',
        'Name' => 'Иван',
    ],
    'double_optin' => 3,
]);
```

Оба варианта возвращают полный ответ API с полями `result` и, если они есть, `warnings`. Имена и параметры методов соответствуют [официальной документации](https://www.unisender.com/ru/support/category/api/).

### HTML в `sendEmail`

HTML-разметку в параметре `body` нужно передавать без предварительного экранирования — клиент сам кодирует параметры для HTTP-запроса:

```php
$name = htmlspecialchars($userName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$response = $unisender->sendEmail([
    'email' => 'user@example.com',
    'sender_name' => 'Company',
    'sender_email' => 'mail@example.com',
    'subject' => 'Тема письма',
    'body' => "<p>Привет, <strong>{$name}</strong>!</p>",
    'list_id' => 123,
]);
```

Использовать `htmlspecialchars()` для всей строки `body` не следует: иначе HTML-теги могут отобразиться как обычный текст. Экранировать нужно только динамические значения, особенно данные, полученные от пользователя.

## Настройка клиента

```php
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;

$unisender = new Client(
    api_key: (string) getenv('UNISENDER_API_KEY'),
    options: ['lang' => 'en'],
    httpClient: $httpClient,
    requestFactory: $requestFactory,
    streamFactory: $streamFactory,
    logger: $logger,
);
```

Все зависимости после API-ключа необязательны. Язык ответа — `ru` или `en`. Старый формат `new Client($key, ['lang' => 'en'])` сохранён.

## Ошибки

```php
use Webmasterskaya\Unisender\Exception\ApiException;
use Webmasterskaya\Unisender\Exception\NetworkException;

try {
    $response = $unisender->createList(['title' => 'Новости']);
} catch (ApiException $exception) {
    echo $exception->getApiCode();
    echo $exception->getStatusCode();
    echo $exception->getResponseBody();
} catch (NetworkException $exception) {
    // Ошибка PSR-18 транспорта: таймаут, DNS, TLS и т. п.
}
```

Все исключения SDK реализуют `UnisenderExceptionInterface`.

## Миграция со старой версии

Методы теперь принимают единый ассоциативный массив с именами параметров из API. Например, вместо `createList($title)` используется `createList(['title' => $title])`. Ответы больше не преобразуются в частичные DTO: клиент всегда возвращает исходный JSON-конверт с `result` и `warnings`. Это позволяет использовать новые поля API без обновления SDK.

Полный список методов, параметров и форматов ответа находится в [официальной документации UniSender](https://www.unisender.com/ru/support/category/api/).

## Разработка

```bash
composer install
composer lint
composer test
composer cs:check
composer cs:fix
composer analyse
composer phpstan
composer psalm
```

Команда `lint` проверяет синтаксис PHP-файлов; `cs:check` проверяет код на соответствие PER Coding Style 2.0 и применимым правилам миграции PHP 8.2, а `cs:fix` применяет те же правила. Тесты PHPUnit используют поддельный HTTP-клиент, поэтому не требуют настоящего API-ключа и не обращаются к UniSender. Команда `composer analyse` запускает PHPStan на уровне `max` и Psalm с уровнем ошибок 1; при необходимости анализаторы можно запускать отдельно командами `phpstan` и `psalm`. CI выполняет эти проверки на PHP 8.2–8.5 и дополнительно проверяет минимально поддерживаемые версии зависимостей.

## Лицензия

Проект распространяется на условиях лицензии MIT. Вы можете свободно использовать, копировать, изменять и распространять его, в том числе в коммерческих проектах, при условии сохранения уведомления об авторских правах и текста лицензии.

Подробнее см. в файле [LICENSE](LICENSE).
