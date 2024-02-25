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
 * Работа со списками контактов
 * @method array getLists(array $data) Метод для получения списков рассылок с их кодами.
 * @method array createList(array $data) Метод для создания нового списка контактов.
 * @method array updateList(array $data) Метод для изменения свойств списка рассылки.
 * @method array deleteList(array $data) Метод для удаления списка контактов.
 * @method array subscribe(array $data) Метод для подписки адресата на один или несколько списков рассылки.
 * @method array exclude(array $data) Метод для исключения адресата из списков рассылки.
 * @method array unsubscribe(array $data) Метод для отписывания адресата от рассылки.
 * @method array importContacts(array $data) Метод для массового импорта и синхронизации контактов.
 * @method array exportContacts(array $data) Метод для экспорта данных по контактам.
 * @method array getTotalContactsCount(array $data) Метод для получения информации о размере базы пользователя.
 * @method array getContactCount(array $data) Метод для получения количества контактов в списке.
 * @method array getContact(array $data) Метод для получения информации об одном контакте.
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
}