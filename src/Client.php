<?php

namespace Webmasterskaya\Unisender;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use PsrDiscovery\Discover;
use PsrDiscovery\Exceptions\SupportPackageNotFoundException;
use Webmasterskaya\Unisender\Exception\DependencyNotFoundException;
use Webmasterskaya\Unisender\Exception\InvalidArgumentException;
use Webmasterskaya\Unisender\Exception\Exception;
use Webmasterskaya\Unisender\Exception\RuntimeException;
use Webmasterskaya\Unisender\Exception\UnexpectedValueException;

/**
 * Клиент для работы с рассылками
 *
 * @method array getLists() Метод для получения списков рассылок с их кодами.
 *
 * Работа с дополнительными полями и метками
 * @method array getFields(array $data) Метод для получения списка пользовательских полей.
 * @method array createField(array $data) Метод для создания нового пользовательского поля.
 * @method array updateField(array $data) Метод для изменения параметров пользовательского поля.
 * @method array deleteField(array $data) Метод для удаления пользовательского поля.
 * @method array getTags(array $data) Метод для получения списка пользовательских меток.
 * @method array deleteTag(array $data) Метод для удаления пользовательской метки.
 *
 * Создание и отправка сообщений
 * @method array createEmailMessage(array $data) Метод для создания email для рассылки.
 * @method array createSmsMessage(array $data) Метод для создания SMS для массовой рассылки.
 * @method array createCampaign(array $data) Метод для планирования массовой отправки email или SMS сообщения.
 * @method array cancelCampaign(array $data) Метод для отмены запланированной массовой рассылки.
 * @method array getActualMessageVersion(array $data) Метод для получения актуальной версии письма.
 * @method array sendSms(array $data) Метод для отправки SMS-сообщения.
 * @method array checkSms(array $data) Метод для проверки статуса доставки SMS.
 * @method array sendEmail(array $data) Метод для упрощенной отправки индивидуальных email-сообщений.
 * @method array sendTestEmail(array $data) Метод для отправки тестовой email-рассылки на собственный адрес.
 * @method array checkEmail(array $data) Метод для проверки статуса доставки email.
 * @method array updateOptInEmail(array $data) Метод для изменения текста письма со ссылкой подтверждения подписки.
 * @method array getWebVersion(array $data) Метод для получения ссылки на веб-версию отправленного письма.
 * @method array deleteMessage(array $data) Метод для удаления сообщения.
 * @method array updateEmailMessage(array $data) Метод для редактирования email для массовой рассылки.
 *
 * Работа с шаблонами
 * @method array createEmailTemplate(array $data) Метод для создания шаблона сообщения для массовой рассылки.
 * @method array updateEmailTemplate(array $data) Метод для редактирования существующего шаблона сообщения.
 * @method array deleteTemplate(array $data) Метод для удаления шаблона.
 * @method array getTemplate(array $data) Метод для получения информации о шаблоне.
 * @method array getTemplates(array $data) Метод для получения списка всех шаблонов, созданных в системе.
 * @method array listTemplates(array $data) Метод для получения списка всех шаблонов без body.
 *
 * Получение статистики
 * @method array getCampaignDeliveryStats(array $data) Метод для получения отчета о статусах доставки сообщений для заданной рассылки.
 * @method array getCampaignCommonStats(array $data) Метод для получения общих сведений о результатах доставки для заданной рассылки.
 * @method array getVisitedLinks(array $data) Метод для получения статистики переходов по ссылкам.
 * @method array getCampaigns(array $data) Метод для получения списка рассылок.
 * @method array getCampaignStatus(array $data) Метод для получения статуса рассылки.
 * @method array getMessages(array $data) Метод для получения списка сообщений.
 * @method array getMessage(array $data) Метод для получения информации об SMS или email-сообщении.
 * @method array listMessages(array $data) Метод для получения списка сообщений без тела и вложений.
 *
 * Работа с заметками
 * @method array createSubscriberNote(array $data) Метод для создания заметки о контакте.
 * @method array updateSubscriberNote(array $data) Метод для редактирования заметки.
 * @method array deleteSubscriberNote(array $data) Метод для удаления заметки.
 * @method array getSubscriberNote(array $data) Метод для получения информации о заметке.
 * @method array getSubscriberNotes(array $data) Метод для получения информации о всех заметках контакта.
 */
class Client
{
    protected string $language = 'ru';
    /**
     * @var string
     */
    protected string $api_key;

    protected string $encoding = 'UTF-8';

    /**
     * @var string[]
     */
    protected const AVAILABLE_LANGUAGES = ['ru', 'en', 'ua',];

    protected const AVAILABLE_METHODS
        = [
            //Методы: работа со списками контактов
            'createList',
            'deleteList',
            'exclude',
            'exportContacts',
            'getContactCount',
            'getLists',
            'getTotalContactsCount',
            'importContacts',
            'subscribe',
            'unsubscribe',
            'updateList',
            'getContact',
            'isContactInLists',
            // Методы: создание и отправка сообщений
            'cancelCampaign',
            'checkEmail',
            'checkSms',
            'createCampaign',
            'createEmailMessage',
            'createSmsMessage',
            'deleteMessage',
            'getActualMessageVersion',
            'getWebVersion',
            'sendEmail',
            'sendSms',
            'sendTestEmail',
            'updateEmailMessage',
            'updateOptInEmail',
            'getSenderDomainList',
            // Методы: получение статистики
            'getCampaignCommonStats',
            'getCampaignDeliveryStats',
            'getCampaignStatus',
            'getMessages',
            'getVisitedLinks',
            'listMessages',
            'getCampaigns',
            'getMessage',
            // Методы: работа с шаблонами
            'createEmailTemplate',
            'deleteTemplate',
            'getTemplate',
            'getTemplates',
            'listTemplates',
            'updateEmailTemplate',
            // Методы: работа с дополнительными полями и метками
            'createField',
            'deleteField',
            'deleteTag',
            'getFields',
            'getTags',
            'updateField',
            'getContactFieldValues',
            // Методы: работа с заметками
            'createSubsciberNote',
            'updateSubcriberNote',
            'deleteSubscriberNote',
            'getSubscriberNote',
            'getSubscriberNotes',
        ];

    protected array $options = [];

    /**
     * @throws \Webmasterskaya\Unisender\Exception\InvalidArgumentException
     * @throws \Webmasterskaya\Unisender\Exception\Exception
     */
    public function __construct(string $api_key, ?array $options = [])
    {
        $api_key = trim($api_key);

        if (empty(trim($api_key))) {
            throw new Exception('API KEY not defined!');
        }

        $this->api_key = trim($api_key);

        if (array_key_exists('lang', $options)) {
            $options['lang'] = strtolower((string)$options['lang']);
            if (in_array($options['lang'], self::AVAILABLE_LANGUAGES)) {
                $this->language = $options['lang'];
            }
        }

        //TODO: Нужно добавить настройки сжатия
    }

    /**
     * @throws \Webmasterskaya\Unisender\Exception\Exception
     * @throws \Webmasterskaya\Unisender\Exception\DependencyNotFoundException
     * @deprecated
     */
    public function __call(string $method, array $data = [])
    {
        return $this->send($method, $data);
    }


    /**
     * Метод вызывает API методы "напрямую"
     *
     * @param string $method Имя метода API
     * @param array $data Аргументы метода API
     * @return array
     * @throws \Webmasterskaya\Unisender\Exception\Exception
     * @throws \Webmasterskaya\Unisender\Exception\DependencyNotFoundException
     * @see https://www.unisender.com/ru/support/category/api/
     *
     */
    public function send(string $method, array $data = []): array
    {
        if (!in_array($method, self::AVAILABLE_METHODS)) {
            throw new RuntimeException('The method "' . $method . '" doesn\'t exist!');
        }

        return $this->execute($method, $data);
    }

    /**
     * Метод выполняет запрос к HTTP API
     *
     * @param string $method Имя метода API
     * @param array $data Аргументы метода API
     * @return array
     * @throws \Webmasterskaya\Unisender\Exception\Exception
     * @throws \Webmasterskaya\Unisender\Exception\DependencyNotFoundException
     */
    protected function execute(string $method, array $data = []): array
    {
        /**
         * @var \Psr\Http\Client\ClientInterface $client
         */
        static $client;

        if (!isset($client)) {
            try {
                $client = Discover::httpClient();
                if (!($client instanceof ClientInterface)) {
                    throw new DependencyNotFoundException('PSR-18 HTTP Client not found');
                }
            } catch (SupportPackageNotFoundException $e) {
                throw new DependencyNotFoundException('PSR-18 HTTP Client not found');
            }
        }

        // TODO: Валидация аргументов
        $data['api_key'] = $this->api_key;
        $data['format'] = 'json';

        $uri = sprintf('https://api.unisender.com/%s/api/%s',
            $this->language,
            $method
        );

        $query = http_build_query(
            $data,
            '',
            '&',
            PHP_QUERY_RFC1738);

        $psr17Factory = new Psr17Factory();

        $stream = $psr17Factory->createStream($query);

        $request = $psr17Factory
            ->createRequest('POST', $uri)
            ->withHeader('Content-type', "application/x-www-form-urlencoded; charset=utf-8")
            ->withBody($stream);

        try {
            $response = $client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new Exception('HTTP Client error: ' . $e->getMessage());
        }

        $status = $response->getStatusCode();
        if ($status >= 400 && $status < 500) {
            throw new Exception('HTTP Client error: ' . $response->getReasonPhrase() . '. Text: ' . $response->getBody());
        }

        if ($status >= 500) {
            throw new Exception('HTTP Server error: ' . $response->getReasonPhrase() . '. Text: ' . $response->getBody());
        }

        $result = $response->getBody()->__toString();

        try {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING | JSON_OBJECT_AS_ARRAY | JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new Exception('API Client error: error on parse response');
        }

        if (array_key_exists('error', $result)) {
            $this->handleApiException($result['code'] ?? '', $result['error']);
        }

        return $result;
    }

    /**
     * Метод для создания нового списка контактов.
     *
     * @param string $title Название списка. Должно быть уникальным в вашем аккаунте.
     * @param string $before_subscribe_url URL для редиректа на страницу «перед подпиской».
     * @param string $after_subscribe_url URL для редиректа на страницу «после подписки».
     * @return array
     * @throws \Webmasterskaya\Unisender\Exception\Exception
     * @throws \Webmasterskaya\Unisender\Exception\DependencyNotFoundException
     */
    public function createList(string $title, string $before_subscribe_url = '', string $after_subscribe_url = ''): array
    {
        $data = ['title' => $title];

        if (!empty($before_subscribe_url)) {
            $data['before_subscribe_url'] = $before_subscribe_url;
        }

        if (!empty($after_subscribe_url)) {
            $data['after_subscribe_url'] = $after_subscribe_url;
        }

        return $this->send('createList', $data);
    }

    /**
     * Метод для удаления списка рассылок по ID.
     *
     * @param int $list_id ID списка, который требуется удалить.
     * @return array
     * @throws \Webmasterskaya\Unisender\Exception\Exception
     * @throws \Webmasterskaya\Unisender\Exception\DependencyNotFoundException
     */
    public function deleteList(int $list_id): array
    {
        return $this->send('deleteList', ['list_id' => $list_id]);
    }

    /**
     * Метод исключает e-mail или телефон контакта из одного или нескольких списков.
     *
     * @param string $contact_type Тип исключаемого контакта - либо 'email', либо 'phone'.
     * @param string $contact Email или телефон контакта, который нужно исключить.
     * @param int[] $list_ids Массив с id списков, из которых необходимо исключить контакт. Если не указан, то контакт будет исключен из всех списков.
     * @return array
     * @throws \Webmasterskaya\Unisender\Exception\Exception
     * @throws \Webmasterskaya\Unisender\Exception\DependencyNotFoundException
     */
    public function exclude(string $contact_type, string $contact, array $list_ids = []): array
    {
        $this->checkAllowedContactType($contact_type);

        $data = [
            'contact_type' => $contact_type,
            'contact' => $contact,
        ];

        if (!empty($list_ids)) {
            $data['list_ids'] = implode(',', array_filter($list_ids, fn($id) => is_int($id)));
        }

        return $this->send('exclude', $data);
    }

    /**
     * Экспорт данных контактов из Unisender.
     * Принцип использования:
     * - Пользователь присылает запрос на экспорт контактов
     * - Система готовит файл
     * - Система отправляет пользователю, на указанный callback URL, уведомление, о готовности файла
     * - Пользователь скачивает файл
     *
     * @note Для метода существует лимит на количество запросов от одного API-ключа или IP-адреса — 20 запросов/60 секунд.
     *
     * @param string $notify_url callback URL, на который будет отправлен ответ после того, как файл экспорта будет сформирован.
     * @param int|null $list_id Необязательный код экспортируемого списка. Если не указан, будут экспортированы все списки.
     * @param array $field_names Массив имён системных и пользовательских полей, которые надо экспортировать.
     * @param string $email Email адрес. Если этот параметр указан, то результат будет содержать только один контакт с таким e-mail адресом.
     * @param string $phone Номер телефона. Если этот параметр указан, то результат будет содержать только один контакт с таким номером телефона.
     * @param string $tag Метка. Если этот параметр указан, то при поиске будут учтены только контакты, имеющие такую метку.
     * @param string $email_status Статус email адреса. Если этот параметр указан, то результат будет содержать только контакты с таким статусом email адреса.
     * @param string $phone_status Статус телефона. Если этот параметр указан, то результат будет содержать только контакты с таким статусом телефона.
     * @return array
     * @throws \Webmasterskaya\Unisender\Exception\Exception
     * @throws \Webmasterskaya\Unisender\Exception\DependencyNotFoundException
     */
    public function exportContacts(string $notify_url, ?int $list_id = null, array $field_names = [], string $email = '', string $phone = '', string $tag = '', string $email_status = '', string $phone_status = ''): array
    {
        $data = ['notify_url' => $notify_url];

        if (!empty($list_id)) {
            $data['list_id'] = $list_id;
        }

        if (!empty($field_names)) {
            $data['field_names'] = $field_names;
        }

        if (!empty($email)) {
            $data['email'] = $email;
        }

        if (!empty($phone)) {
            $data['phone'] = $phone;
        }

        if (!empty($tag)) {
            $data['tag'] = $tag;
        }

        $available_email_statuses = ['new', 'invited', 'active', 'inactive', 'unsubscribed', 'blocked', 'activation_requested'];
        if (!empty($email_status)) {
            if (!in_array($email_status, $available_email_statuses)) {
                throw new UnexpectedValueException(sprintf('Unexpected argument value provided of "%s". Expected: "%s". Passed: "%s"',
                    'email_status',
                    implode('" or "', $available_email_statuses),
                    $email_status), 0);
            }
            $data['email_status'] = $email_status;
        }

        $available_phone_statuses = ['new', 'active', 'inactive', 'unsubscribed', 'blocked'];
        if (!empty($phone_status)) {
            if (!in_array($phone_status, $available_phone_statuses)) {
                throw new UnexpectedValueException(sprintf('Unexpected argument value provided of "%s". Expected: "%s". Passed: "%s"',
                    'phone_status',
                    implode('" or "', $available_phone_statuses),
                    $phone_status), 0);
            }
            $data['email_status'] = $email_status;
        }

        return $this->send('async/exportContacts', $data);
    }

    /**
     * Метод для получения количества контактов в списке.
     *
     * @note Должен быть передан хотя бы один из аргументов $tag_id или $contact_type.
     *
     * @param int $list_id id списка, по которому осуществляется поиск.
     * @param int|null $tag_id id тега, по которому осуществляется поиск.
     * @param string|null $contact_type Указывает тип контакта, по которому осуществляется поиск. Доступные значения "email" и "phone"
     * @param string $search Строка поискового запроса. Можно использовать только в паре с аргументом $contact_type.
     * @return array
     * @throws \Webmasterskaya\Unisender\Exception\Exception
     * @throws \Webmasterskaya\Unisender\Exception\DependencyNotFoundException
     */
    public function getContactCount(int $list_id, ?int $tag_id = null, ?string $contact_type = null, string $search = ''): array
    {
        $data = ['list_id' => $list_id];

        if (empty($tag_id) && empty($contact_type)) {
            throw new InvalidArgumentException(sprintf('Either "%s" or "%s" argument must be provided.',
                'tag_id',
                'contact_type'), 0);
        }

        $params = [];
        if (!empty($tag_id)) {
            $params['tagId'] = $tag_id;
        }

        if (!empty($contact_type)) {
            $this->checkAllowedContactType($contact_type);

            $params['type'] = $contact_type;

            if (empty($search)) {
                throw new InvalidArgumentException(sprintf('The "%s" argument must be provided if the "%s" is given.',
                    'search',
                    'type'), 0);
            }

            $params['search'] = $search;
        }


        if (!empty($search) && empty($params['type'])) {
            throw new InvalidArgumentException(sprintf('The "%s" argument must be provided if the "%s" is given.',
                'search',
                'type'), 0);
        }

        $data['params'] = $params;

        return $this->send('getContactCount', $data);
    }

    /**
     * Метод возвращает размер базы контактов по логину пользователя.
     *
     * @param string $login Логин пользователя в системе.
     * @return array
     * @throws \Webmasterskaya\Unisender\Exception\Exception
     * @throws \Webmasterskaya\Unisender\Exception\DependencyNotFoundException
     */
    public function getTotalContactsCount(string $login): array
    {
        return $this->send('getTotalContactsCount', ['login' => $login]);
    }

    /**
     * Метод массового импорта контактов.
     *
     * @param array $field_names Массив названий столбцов данных. Обязательно должно присутствовать хотя бы поле «email», иначе метод вернет ошибку. Могут быть указаны названия существующих пользовательских полей и названия следующих системных полей.
     * @param array $data Массив данных контактов, каждый элемент которого — массив полей, перечисленный в том порядке, в котором следуют field_names.
     * @param bool $overwrite_tags true - перезаписать существующие метки, false - только добавлять новые, не удаляя старых.
     * @param bool $overwrite_lists true - заменить на новые все данные о том, когда и в какие списки включены и от каких отписаны контакты.
     * @return array
     * @throws \Webmasterskaya\Unisender\Exception\Exception
     * @throws \Webmasterskaya\Unisender\Exception\DependencyNotFoundException
     */
    public function importContacts(array $field_names, array $data = ['email'], bool $overwrite_tags = false, bool $overwrite_lists = false): array
    {
        $params = [
            'field_names' => $field_names,
            'data' => $data,
            'overwrite_tags' => $overwrite_tags,
            'overwrite_lists' => $overwrite_lists,
        ];

        if (empty($params['field_names'])) {
            $params['field_names'] = ['email'];
        }

        return $this->send('importContacts', $params);
    }

    /**
     * @param array $list_ids Массив с id списков, в которые нужно добавить контакт.
     * @param array $fields Ассоциативный массив дополнительных полей.
     * @param array $tags Массив меток, которые необходимо добавить к контакту. Максимально допустимое количество - 10 меток.
     * @param int $double_optin Флаг проверки контакта. Принимает значение 0, 3 или 4.
     * @param int $overwrite Режим перезаписывания полей и меток, число от 0 до 2
     * @return array
     * @throws \Webmasterskaya\Unisender\Exception\Exception
     * @throws \Webmasterskaya\Unisender\Exception\DependencyNotFoundException
     */
    public function subscribe(array $list_ids, array $fields, array $tags = [], int $double_optin = 1, int $overwrite = 0): array
    {
        $allowed_double_optin = [0, 3, 4];
        if (!in_array($double_optin, $allowed_double_optin)) {
            throw new UnexpectedValueException(sprintf('Unexpected argument value provided of "%s". Expected: "%s". Passed: "%s"',
                'double_optin',
                implode('" or "', $allowed_double_optin),
                $double_optin), 0);
        }

        if ($overwrite < 0 || $overwrite > 2) {
            throw new UnexpectedValueException(sprintf('Unexpected argument value provided of "%s". Expected: "%s". Passed: "%s"',
                'overwrite',
                implode('" or "', [0, 1, 2]),
                $overwrite), 0);
        }

        $data = [
            'list_ids' => implode(',', $list_ids),
            'fields' => $fields,
            'double_optin' => $double_optin,
            'overwrite' => $overwrite
        ];

        if (!empty($tags)) {
            if (count($tags) > 10) {
                $tags = array_splice($tags, 0, 10);
            }
            $data['tags'] = implode(',', $tags);
        }

        return $this->send('subscribe', $data);
    }

    /**
     * @param string $contact_type Тип отписываемого контакта - либо 'email', либо 'phone'.
     * @param string $contact E-mail или телефон, который надо отписать от рассылок.
     * @param array $list_ids Массив кодов списков, от которых требуется отписать контакт.
     * @return array
     * @throws \Webmasterskaya\Unisender\Exception\Exception
     * @throws \Webmasterskaya\Unisender\Exception\DependencyNotFoundException
     */
    public function unsubscribe(string $contact_type, string $contact, array $list_ids = []): array
    {
        $this->checkAllowedContactType($contact_type);

        $data = [
            'contact_type' => $contact_type,
            'contact' => $contact,
        ];

        if (!empty($list_ids)) {
            $data['list_ids'] = implode(',', array_filter($list_ids, fn($id) => is_int($id)));
        }

        return $this->send('unsubscribe', $data);
    }

    /**
     * Метод для изменения свойств списка рассылки.
     *
     * @param int $list_id Код списка, который нужно изменить
     * @param string $title Название списка. Должно быть уникальным в вашем аккаунте.
     * @param string $before_subscribe_url URL для редиректа на страницу "перед подпиской".
     * @param string $after_subscribe_url URL для редиректа на страницу "после подписки".
     * @return array
     * @throws \Webmasterskaya\Unisender\Exception\Exception
     * @throws \Webmasterskaya\Unisender\Exception\DependencyNotFoundException
     */
    public function updateList(int $list_id, string $title = '', string $before_subscribe_url = '', string $after_subscribe_url = ''): array
    {
        $data = ['list_id' => $list_id];

        if (!empty($title)) {
            $data['title'] = $title;
        }

        if (!empty($before_subscribe_url)) {
            $data['before_subscribe_url'] = $before_subscribe_url;
        }

        if (!empty($after_subscribe_url)) {
            $data['after_subscribe_url'] = $after_subscribe_url;
        }

        return $this->send('updateList', $data);
    }

    /**
     * Метод возвращает информацию об одном контакте.
     *
     * @param string $email E-mail адрес контакта, информацию по которому нужно получить
     * @param bool $include_lists Вывод информации о списках, в которые добавлен контакт.
     * @param bool $include_fields Вывод информации о дополнительных полях контакта.
     * @param bool $include_details Вывод дополнительной информации о контакте.
     * @return array
     * @throws \Webmasterskaya\Unisender\Exception\Exception
     * @throws \Webmasterskaya\Unisender\Exception\DependencyNotFoundException
     */
    public function getContact(string $email, bool $include_lists = false, bool $include_fields = false, bool $include_details = false): array
    {
        $data = ['email' => $email];

        $data['include_lists'] = $include_lists;
        $data['include_fields'] = $include_fields;
        $data['include_details'] = $include_details;

        return $this->send('getContact', $data);
    }

    /**
     * Метод обрабатывает ошибки в ответах от сервиса и преобразует их в исключения
     *
     * @param string $code Код ошибки в ответе от сервиса
     * @param string $error Текст ошибки в ответе от сервиса
     * @return mixed
     */
    protected function handleApiException(string $code, string $error)
    {
        /**
         * Сервис может отвечать подобными ошибками "OB13012016 [Can't find user by provided login]",
         * поэтому нужно привести их в человеческий вид
         */
        if (($open_char_pos = strpos($error, '[')) !== false) {
            $close_char_pos = strripos($error, ']');
            $error = substr($open_char_pos, $close_char_pos - $open_char_pos);
        }

        switch ($code) {
            case 'invalid_arg':
                throw new InvalidArgumentException($error);
            default:
                throw new RuntimeException($error);
        }
    }

    /**
     * Метод проверяет, является ли переданное значение допустимым значением для типа контакта
     *
     * @param string $contact_type Проверяемое значение
     * @return void
     */
    protected function checkAllowedContactType(string $contact_type)
    {
        $allowed_contact_type = ['email', 'phone'];
        if (!in_array($contact_type, $allowed_contact_type)) {
            throw new UnexpectedValueException(sprintf('Unexpected argument value provided of "%s". Expected: "%s". Passed: "%s"',
                'contact_type',
                implode('" or "', $allowed_contact_type),
                $contact_type), 0);
        }
    }
}