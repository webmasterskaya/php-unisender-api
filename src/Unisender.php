<?php

namespace Webmasterskaya\Unisender;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientExceptionInterface;
use PsrDiscovery\Discover;
use PsrDiscovery\Exceptions\SupportPackageNotFoundException;
use Webmasterskaya\Unisender\Exception\InvalidArgumentException;
use Webmasterskaya\Unisender\Exception\UnisenderException;

/**
 * @method array createList(array $data) Метод для создания нового списка контактов.
 * @method array deleteList(array $data) Метод для удаления списка.
 * @method array exclude(array $data) Метод исключает e-mail или телефон контакта из одного или нескольких списков. В отличие от метода unsubscribe, он не помечает контакт как "отписавшийся", и его позднее снова можно включить в список с помощью метода subscribe.
 * @method array exportContacts(array $data) Экспорт данных контактов из Unisender.
 * @method array getContactCount(array $data) Метод для получения количества контактов в списке. Для данного метода существует лимит — 60 запросов/60 секунд.
 * @method array getLists() Метод для получения перечня всех имеющихся списков рассылок.
 * @method array getTotalContactsCount() Метод возвращает размер базы контактов по логину пользователя.
 * @method array importContacts(array $data) Метод массового импорта контактов. Может использоваться также для периодической синхронизации с базой контактов
 * @method array subscribe(array $data) Метод добавляет контакты (email адрес и/или мобильный телефон) контакта в один или несколько списков, а также позволяет добавить/поменять значения дополнительных полей и меток.
 * @method array unsubscribe(array $data) Метод отписывает e-mail или телефон контакта от одного или нескольких списков. Помечает контакт как «отписавшийся». Вернуть статус на «активный» через API нельзя!
 * @method array updateList(array $data) Метод для изменения свойств списка рассылки.
 * @method array getContact(array $data) Получение информации об одном контакте.
 * @method array isContactInLists(array $data) Метод используется для проверки, находится ли контакт в указанном(ых) списках пользователя.
 * @method array sendEmail(array $data) Метод для отправки одного индивидуального email-сообщения без использования персонализации и с ограниченными возможностями получения статистики.
 */
class Unisender
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
        return $this->send($method, reset($data) ?: []);
    }

    /**
     * @throws \Webmasterskaya\Unisender\Exception\UnisenderException
     */
    public function send(string $method, array $data = [])
    {
        if (!in_array($method, self::AVAILABLE_METHODS)) {
            throw new UnisenderException('The method "' . $method . '" doesn\'t exist!');
        }

        return $this->execute($method, $data);
    }

    /**
     * @throws \Webmasterskaya\Unisender\Exception\UnisenderException
     */
    protected function execute(string $method, array $data = [])
    {
        /**
         * @var \Psr\Http\Client\ClientInterface          $client
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
        $data['format']  = 'json';

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
            throw new UnisenderException('HTTP Client error: ' . $response->getReasonPhrase());
        }

        if ($status >= 500) {
            throw new UnisenderException('HTTP Server error: ' . $response->getReasonPhrase());
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
}