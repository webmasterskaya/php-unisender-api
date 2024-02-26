# Работа со списками контактов

## `getLists`

Метод для получения перечня всех имеющихся списков рассылок.

### Использование

```php
$client->getLists(): array
```

### Параметры

У этого метода нет параметров

### Возвращаемое значение

Набор из массивов, с полями id и title. Возможно, в будущем будут добавлены и другие поля.

<details>

<summary>Пример возвращаемого значения</summary>

```php
[
    "result" => [
        ["id" => 55688, "title" => "My list number 1"],
        ["id" => 224589, "title" => "Это мой второй список"],
        ["id" => 210012, "title" => "И третий"],
    ]
]
```

</details>

---

## `createList`

Метод для создания нового списка рассылок. ([Подробнее](https://www.unisender.com/ru/support/api/contacts/createlist/))

### Использование

```php
$client->createList(string $title, [string $before_subscribe_url, string $after_subscribe_url]): array
```

### Параметры

- `$title` (string): Название нового списка.
- `$before_subscribe_url` (string): URL для редиректа на страницу «перед подпиской». Обычно на этой странице
  показывается сообщение, что контакту надо перейти по ссылке подтверждения для активации подписки. В этот URL можно
  добавлять поля подстановки - например, вы можете идентифицировать контакта по email-адресу, подставив сюда email -
  либо по коду контакта в своей базе данных, сохраняя код в дополнительное поле и подставляя его в этот URL.
- `$after_subscribe_url` (string): URL для редиректа на страницу «после подписки». Обычно на этой странице показывается
  сообщение, что подписка успешно активирована. В этот URL можно добавлять поля подстановки - например, вы можете
  идентифицировать контакта по email-адресу, подставив сюда email - либо по коду контакта в своей базе данных, сохраняя
  код в дополнительное поле и подставляя его в этот URL.

### Возвращаемое значение

Массив с единственным полем id (кодом списка).

<details>

<summary>Пример возвращаемого значения</summary>

```php
[
    "result" => [
        ["id" => 55688]
    ]
]
```

</details>

---

## `deleteList`

Метод для удаления списка рассылок по ID.

### Использование

```php
$client->deleteList(int $list_id): array
```

### Параметры

- `$list_id` (int): ID списка, который требуется удалить.

### Возвращаемое значение

Метод возвращает пустой массив.

---

## `exclude`

Метод исключает e-mail или телефон контакта из одного или нескольких списков.

### Использование

```php
$client->exclude(string $contact_type, string $contact[, array $list_ids]): array
```

### Параметры

- `$contact_type` (string): Тип исключаемого контакта - либо 'email', либо 'phone'.
- `$contact` (string): Email или телефон контакта, который нужно исключить.
- `$list_ids` (int[]): Массив с id списков, из которых необходимо исключить контакт. Если не указан, то контакт будет исключен из всех списков.

### Возвращаемое значение

Массив

### Исключения

- `Exception`
- `DependencyNotFoundException`

---

## `exportContacts`

Экспорт данных контактов из Unisender.
Принцип использования:
- Пользователь присылает запрос на экспорт контактов
- Система готовит файл
- Система отправляет пользователю, на указанный callback URL, уведомление, о готовности файла
- Пользователь скачивает файл

> **Notice**
> Для метода существует лимит на количество запросов от одного API-ключа или IP-адреса — 20 запросов/60 секунд.

### Использование

```php
$client->exportContacts(string $notify_url, [int $list_id, array $field_names, string $email, string $phone, string $tag, string $email_status, string $phone_status]): array
```

### Параметры

- `$notify_url` (string): callback URL, на который будет отправлен ответ после того, как файл экспорта будет сформирован.
- `$list_id` (int|null): Необязательный код экспортируемого списка. Если не указан, будут экспортированы все списки.
- `$field_names` (array): Массив имён системных и пользовательских полей, которые надо экспортировать.
- `$email` (string): Email адрес. Если этот параметр указан, то результат буд��т содержать только один контакт с таким e-mail адресом.
- `$phone` (string): Номер телефона. Если этот параметр указан, то результат будет содержать только один контакт с таким номером телефона.
- `$tag` (string): Метка. Если этот параметр указан, то при поиске будут учтены только контакты, имеющие такую метку.
- `$email_status` (string): Статус email адреса. Если этот параметр указан, то результат будет содержать только контакты с таким статусом email адреса.
- `$phone_status` (string): Статус телефона. Если этот параметр указан, то результат будет содержать только контакты с таким статусом телефона.

### Возвращаемое значение

Массив

### Исключения

- `Webmasterskaya\Unisender\Exception\Exception`
- `Webmasterskaya\Unisender\Exception\DependencyNotFoundException`
- `Webmasterskaya\Unisender\Exception\UnexpectedValueException`

---

## `getContactCount`

Метод для получения количества контактов в списке.

### Использование

```php
$client->getContactCount(int $list_id, [int $tag_id, string $contact_type, string $search]): array
```

### Параметры

- `$list_id` (int): id списка, по которому осуществляется поиск.
- `$tag_id` (int|null): id тега, по которому осуществляется поиск.
- `$contact_type` (string|null): Указывает тип контакта, по которому осуществляется поиск. Доступные значения "email" и "phone".
- `$search` (string): Строка поискового запроса. Можно использовать только в паре с аргументом `$contact_type`.

### Возвращаемое значение

Массив

### Исключения

- `\Webmasterskaya\Unisender\Exception\Exception`
- `\Webmasterskaya\Unisender\Exception\DependencyNotFoundException`
- `\InvalidArgumentException`
- `\Webmasterskaya\Unisender\Exception\UnexpectedValueException`

---

## `getLists`

Метод для получения перечня всех имеющихся списков рассылок.

```php
$client->getLists(): array
```

### Возвращаемое значение

Набор из массивов, с полями id и title. Возможно, в будущем будут добавлены и другие поля.

---

## `getTotalContactsCount`

Метод возвращает размер базы контактов по логину пользователя.

### Использование

```php
$client->getTotalContactsCount(string $login): array
```

### Параметры

- `$login` (string): Логин пользователя в системе.

### Возвращаемое значение

Массив

### Исключения

- `\Webmasterskaya\Unisender\Exception\Exception`
- `\Webmasterskaya\Unisender\Exception\DependencyNotFoundException`

---

## `importContacts`

Метод массового импорта контактов.

### Использование

```php
$client->importContacts(array $field_names, [array $data = ['email'], bool $overwrite_tags = false, bool $overwrite_lists = false]): array
```

### Параметры

- `$field_names` (array): Массив названий столбцов данных. Обязательно должно присутствовать хотя бы поле «email», иначе метод вернет ошибку. Могут быть указаны названия существующих пользовательских полей и названия следующих системных полей.
- `$data` (array): Массив данных конт��ктов, каждый элемент которого — массив полей, перечисленный в том порядке, в котором следуют field_names. По умолчанию ['email'].
- `$overwrite_tags` (bool): true - перезаписать существующие метки, false - только добавлять новые, не удаляя старых. По умолчанию false.
- `$overwrite_lists` (bool): true - заменить на новые все данные о том, когда и в какие списки включены и от каких отписаны контакты. По умолчанию false.

### Возвращаемое значение

Массив

### Исключения
- `\Webmasterskaya\Unisender\Exception\Exception`
- `\Webmasterskaya\Unisender\Exception\DependencyNotFoundException`
- `\Webmasterskaya\Unisender\Exception\UnexpectedValueException`

---

## `subscribe`

Метод для подписки на списки контактов.

### Использование

```php
$client->subscribe(array $list_ids, array $fields, [array $tags = []], [int $double_optin = 1], [int $overwrite = 0]): array
```

### Параметры

- `$list_ids` (array): Массив с id списков, в которые нужно добавить контакт.
- `$fields` (array): Ассоциативный массив дополнительных полей.
- `$tags` (array): Массив меток, которые необходимо добавить к контакту. Максимально допустимое количество - 10 меток.
- `$double_optin` (int): Флаг проверки контакта. Принимает значение 0, 3 или 4.
- `$overwrite` (int): Режим перезаписывания полей и меток, число от 0 до 2

### Возвращаемое значение

Массив

### Исключения

- `\Webmasterskaya\Unisender\Exception\UnexpectedValueException`

---

## `unsubscribe`

Метод для отписки контакта от рассылок.

### Использование

```php
$client->unsubscribe(string $contact_type, string $contact[, array $list_ids = []]): array
```

### Параметры

- `$contact_type` (string): Тип отписываемого контакта - либо 'email', либо 'phone'.
- `$contact` (string): E-mail или телефон, который надо отписать от рассылок.
- `$list_ids` (array): Массив кодов списков, от которых требуется отписать контакт.

### Возвращаемое значение

Массив

### Исключения

- `\Webmasterskaya\Unisender\Exception\Exception`
- `\Webmasterskaya\Unisender\Exception\DependencyNotFoundException`
- `\Webmasterskaya\Unisender\Exception\UnexpectedValueException`

---

## `updateList`

Метод для изменения свойств списка рассылки.

### Использование

```php
$client->updateList(int $list_id, [string $title, string $before_subscribe_url, string $after_subscribe_url]): array
```

### Параметры

- `$list_id` (int): Код списка, который нужно изменить.
- `$title` (string): Название списка. Должно быть уникальным в вашем аккаунте.
- `$before_subscribe_url` (string): URL для редиректа на страницу "перед подпиской".
- `$after_subscribe_url` (string): URL для редиректа на страницу "после подписки".

### Возвращаемое значение

Массив

### Исключения

- `\Webmasterskaya\Unisender\Exception\Exception`
- `\Webmasterskaya\Unisender\Exception\DependencyNotFoundException`
- `\Webmasterskaya\Unisender\Exception\UnexpectedValueException`

---

## `getContact`

Метод для получения информации о контакте.

```php
$client->getContact(string $contactId): array
```

### Параметры

- `$contactId` (string): ID контакта.

### Возвращаемое значение

Информация о контакте.

---

## `isContactInLists`

Метод для проверки наличия контакта в указанных списках.

### Использование

```php
$client->isContactInLists(string $email, array $list_ids, [string $condition = 'or']): array
```

### Параметры

- `$email` (string): Валидный email адрес.
- `$list_ids` (array): Массив id списков.
- `$condition` (string): Условие проверки. Принимает 2 значения: "or" и "and". При "or" метод возвращает true, если контакт находится хотя бы в одном из указанных списков в list_ids, при "and" - если контакт есть во всех указанных списках.

### Возвращаемое значение

Массив

### Исключения

- `\Webmasterskaya\Unisender\Exception\DependencyNotFoundException`
- `\Webmasterskaya\Unisender\Exception\Exception`
- `\Webmasterskaya\Unisender\Exception\UnexpectedValueException`