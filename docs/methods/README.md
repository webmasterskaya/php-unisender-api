# Доступные методы интеграции сервиса рассылок Unisender

Названия методов SDK совпадает с методами API, которые описаны в [документации](https://www.unisender.com/ru/support/category/api/).

Порядок аргументов в методах SDK могут отличаться от HTTP API. Внимательно читайте документацию к методам SDK

## Методы SDK API

- [Работа со списками контактов](LISTS.md)
  - [getLists — получить списки для рассылок с их кодами;](LISTS.md#getlists)
  - [createList — создать новый список контактов;](LISTS.md#createlist)
  - [updateList — изменить свойства списка рассылки;](LISTS.md#updatelist)
  - [deleteList — удалить список контактов;](LISTS.md#deletelist)
  - [subscribe — подписать адресата на один или несколько списков рассылки;](LISTS.md#subscribe)
  - [exclude — исключить адресата из списков рассылки;](LISTS.md#exclude)
  - [unsubscribe — отписать адресата от рассылки;](LISTS.md#unsubscribe)
  - [importContacts — массовый импорт и синхронизация контактов;](LISTS.md#importcontacts)
  - [exportContacts — экспорт данных по контактам;](LISTS.md#exportcontacts)
  - [getTotalContactsCount — получить информацию о размере базы пользователя;](LISTS.md#gettotalcontactscount)
  - [getContactCount — получить количество контактов в списке;](LISTS.md#getcontactcount)
  - [getContact — получить информацию об одном контакте.](LISTS.md#getcontact)
- [Работа с дополнительными полями и метками](FIELDS.md)
- [Создание и отправка сообщений](MESSAGES.md)
- [Работа с шаблонами](TEMPLATES.md)
- [Получение статистики](STATISTICS.md)
- [Работа с заметками](NOTES.md)