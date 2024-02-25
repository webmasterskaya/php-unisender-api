<?php

namespace Webmasterskaya\Unisender;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientExceptionInterface;
use PsrDiscovery\Discover;
use PsrDiscovery\Exceptions\SupportPackageNotFoundException;
use Webmasterskaya\Unisender\Exception\InvalidArgumentException;
use Webmasterskaya\Unisender\Exception\UnisenderException;

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
     * @throws \Webmasterskaya\Unisender\Exception\UnisenderException
     */
    public function __construct(string $api_key, $options = [])
    {
        if (empty($api_key)) {
            throw new UnisenderException('API KEY not defined!');
        }

        $this->api_key = trim($api_key);

        if (!\is_array($options) && !($options instanceof \ArrayAccess)) {
            throw new InvalidArgumentException(
                'The options param must be an array or implement the ArrayAccess interface.'
            );
        }

        if (array_key_exists('lang', $options)) {
            $options['lang'] = strtolower((string)$options['lang']);
            if (in_array($options['lang'], self::AVAILABLE_LANGUAGES)) {
                $this->language = $options['lang'];
            }
        }
//        compression


    }

    /**
     * @throws \Webmasterskaya\Unisender\Exception\UnisenderException
     */
    public function __call(string $method, array $data = [])
    {
        return $this->send($method, $data);
    }


    /**
     * @param string $method
     * @param array $data
     * @return array
     * @throws UnisenderException
     */
    protected function send(string $method, array $data = []): array
    {
        if (!in_array($method, self::AVAILABLE_METHODS)) {
            throw new UnisenderException('The method "' . $method . '" doesn\'t exist!');
        }

        return $this->execute($method, $data);
    }

    /**
     * @param string $method
     * @param array $data
     * @return array
     * @throws UnisenderException
     */
    protected function execute(string $method, array $data = []): array
    {
        /**
         * @var \Psr\Http\Client\ClientInterface $client
         */
        static $client;

        if (!isset($client)) {
            try {
                /** @var \Joomla\Http\Http $client */
                $client = Discover::httpClient();
                if (!($client instanceof \Psr\Http\Client\ClientInterface)) {
                    throw new UnisenderException('PSR-18 HTTP Client not found');
                }
            } catch (SupportPackageNotFoundException $e) {
                throw new UnisenderException('PSR-18 HTTP Client not found');
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
            throw new UnisenderException('HTTP Client error: ' . $e->getMessage());
        }

        $status = $response->getStatusCode();
        if ($status >= 400 && $status < 500) {
            throw new UnisenderException('HTTP Client error: ' . $response->getReasonPhrase() . '. Text: ' . $response->getBody());
        }

        if ($status >= 500) {
            throw new UnisenderException('HTTP Server error: ' . $response->getReasonPhrase() . '. Text: ' . $response->getBody());
        }

        $result = $response->getBody()->__toString();

        try {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING | JSON_OBJECT_AS_ARRAY | JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new UnisenderException('API Client error: error on parse response');
        }

        if (array_key_exists('error', $result)) {
            throw new UnisenderException($result['error']);
        }

        return $result;
    }

    public function getOption($key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * @param string $title
     * @param string $before_subscribe_url
     * @param string $after_subscribe_url
     * @return array
     * @throws UnisenderException
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
     * @param int $list_id
     * @return array
     * @throws UnisenderException
     */
    public function deleteList(int $list_id): array
    {
        return $this->send('deleteList', ['list_id' => $list_id]);
    }

    /**
     * @param string $contact_type
     * @param string $contact
     * @param int[] $list_ids
     * @return array
     * @throws UnisenderException
     */
    public function exclude(string $contact_type, string $contact, array $list_ids = []): array
    {
        $data = [
            'contact_type' => $contact_type,
            'contact' => $contact,
        ];

        if (!empty($list_ids)) {
            $data['list_ids'] = implode(',', $list_ids);
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
     * @throws UnisenderException
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

        $available_email_statuses = ['new', 'invited', 'active', 'inactive', 'unsubscribed', 'blocked', 'activation_requested',];
        if (!empty($email_status) && in_array($email_status, $available_email_statuses)) {
            $data['email_status'] = $email_status;
        }

        $available_phone_statuses = ['new', 'active', 'inactive', 'unsubscribed', 'blocked'];
        if (!empty($phone_status) && in_array($phone_status, $available_phone_statuses)) {
            $data['email_status'] = $email_status;
        }

        return $this->send('async/exportContacts', $data);
    }

    /**
     * Метод для получения количества контактов в списке.
     *
     * @note Должен быть передан хотя бы один из параметров $tag_id, $type, $search, иначе будет выброшено исключение.
     *
     * @param int $list_id id списка, по которому осуществляется поиск.
     * @param int|null $tag_id поиск по тегу с определенным id
     * @param string|null $type поиск по определенному типу контактов, возможные значения
     * @param string $search поиск в email/телефоне по подстроке. Используется только с заданным params [type].
     * @return array
     * @throws UnisenderException
     */
    public function getContactCount(int $list_id, ?int $tag_id = null, ?string $type = null, string $search = ''): array
    {
        $params = [];
        $data = ['list_id' => $list_id];

        if (!empty($tag_id)) {
            $params['tagId'] = $tag_id;
        }

        if (!empty($type) && in_array($type, ['address', 'phone'])) {
            $params['type'] = $type;
        }

        if (!empty($search) && !empty($params['type'])) {
            $params['search'] = $search;
        }

        if (empty($params)) {
            throw new \InvalidArgumentException('Должен быть передан хотя бы один из параметров $tag_id, $type, $search');
        }

        $data['params'] = $params;

        return $this->send('getContactCount', $data);
    }

    /**
     * Метод возвращает размер базы контактов по логину пользователя.
     *
     * @param string $login Логин пользователя в системе.
     * @return array
     * @throws UnisenderException
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
     * @throws UnisenderException
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
     * @throws UnisenderException
     */
    public function subscribe(array $list_ids, array $fields, array $tags = [], int $double_optin = 1, int $overwrite = 0): array
    {
        if (!in_array($double_optin, [0, 3, 4])) {
            throw new \InvalidArgumentException('Параметр $double_optin принимает только значение 0, 3 или 4');
        }

        if ($overwrite < 0 || $overwrite > 2) {
            throw new \InvalidArgumentException('Параметр $overwrite принимает только значения от 0 до 2');
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
     * @throws UnisenderException
     */
    public function unsubscribe(string $contact_type, string $contact, array $list_ids = []): array
    {
        if (!in_array($contact_type, ['email', 'phone'])) {
            throw new \InvalidArgumentException('Параметр $contact_type принимает только значения email или phone');
        }

        $data = [
            'contact_type' => $contact_type,
            'contact' => $contact,
        ];

        if (!empty($list_ids)) {
            $data['list_ids'] = implode(',', $list_ids);
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
     * @throws UnisenderException
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
     * Метод возвращает информации об одном контакте.
     *
     * @param string $email E-mail адрес контакта, информацию по которому нужно получить
     * @param bool $include_lists Вывод информации о списках, в которые добавлен контакт.
     * @param bool $include_fields Вывод информации о дополнительных полях контакта.
     * @param bool $include_details Вывод дополнительной информации о контакте.
     * @return array
     * @throws UnisenderException
     */
    public function getContact(string $email, bool $include_lists = false, bool $include_fields = false, bool $include_details = false): array
    {
        $data = ['email' => $email];

        $data['include_lists'] = $include_lists;
        $data['include_fields'] = $include_fields;
        $data['include_details'] = $include_details;

        return $this->send('getContact', $data);
    }
}