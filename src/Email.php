<?php

namespace Webmasterskaya\Unisender;

use Webmasterskaya\Unisender\Exception\UnexpectedValueException;

/**
 * Класс индивидуального e-mail сообщения.
 */
class Email
{
    /**
     * Адрес получателя сообщения.
     * @var string
     */
    protected string $email;

    /**
     * E-mail адрес отправителя.
     * @var string
     */
    protected string $sender_email;

    /**
     * Имя отправителя.
     * @var string
     */
    protected string $sender_name;

    /**
     * Тема письма.
     * @var string
     */
    protected string $subject;

    /**
     * Текст письма в формате HTML.
     * @var string
     */
    protected string $body;

    /**
     * Вложенные в письмо файлы.
     * @var array
     * @see \Webmasterskaya\Unisender\Email::setAttachments()
     */
    protected array $attachments = [];

    /**
     * Заголовки письма.
     * @var array
     * @see \Webmasterskaya\Unisender\Email::setHeaders()
     */
    protected array $headers = [];

    protected const ALLOW_HEADERS = ['Reply-To', 'Priority'];

    public function __construct(string $to = '', string $from = '', string $name = '', string $subject = '', string $body = '')
    {
        $this->email = $to;
        $this->sender_email = $from;
        $this->sender_name = $name;
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * @see \Webmasterskaya\Unisender\Email::setEmail()
     */
    public function setTo(string $email): Email
    {
        return $this->setEmail($email);
    }

    /**
     * Метод устанавливает адрес получателя сообщения. Также можно передавать имя получателя: `Vasya Pupkin <vpupkin@gmail.com>`
     *
     * @param string $email Адрес получателя сообщения.
     * @return $this
     */
    public function setEmail(string $email): Email
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @see \Webmasterskaya\Unisender\Email::setSenderEmail()
     */
    public function setFrom(string $from_email): Email
    {
        return $this->setSenderEmail($from_email);
    }

    /**
     * Метод устанавливает e-mail адрес отправителя.
     *
     * @param string $sender_email E-mail адрес отправителя.
     * @return $this
     */
    public function setSenderEmail(string $sender_email): Email
    {
        $this->sender_email = $sender_email;
        return $this;
    }

    /**
     * @see \Webmasterskaya\Unisender\Email::setSenderName()
     */
    public function setName(string $from_name): Email
    {
        return $this->setSenderName($from_name);
    }


    /**
     * Метод устанавливает имя отправителя.
     *
     * @param string $sender_name Имя отправителя.
     * @return $this
     */
    public function setSenderName(string $sender_name): Email
    {
        $this->sender_name = $sender_name;
        return $this;
    }

    /**
     * Метод устанавливает тему письма.
     *
     * @param string $subject Тема письма.
     * @return $this
     */
    public function setSubject(string $subject): Email
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Метод устанавливает текст письма.
     *
     * @param string $body Текст письма в формате HTML.
     * @return $this
     */
    public function setBody(string $body): Email
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Метод устанавливает список вложенных в письмо файлов (их бинарное содержимое, base64 использовать нельзя!).
     *
     * @note Содержимое файла можно получить через функцию file_get_contents()
     *
     * @param array $attachments Ассоциативный массив файлов-вложений. В качестве ключа указывается имя файла, в качестве значения - содержимое файла
     * @return $this
     */
    public function setAttachments(array $attachments): Email
    {
        $this->attachments = $attachments;
        return $this;
    }

    /**
     * Метод добавляет запись к списку вложенных в письмо файлов.
     *
     * @note Содержимое файла можно получить через функцию file_get_contents()
     *
     * @param string $file_name Имя файла.
     * @param string $content Содержимое файла (бинарное содержимое, base64 использовать нельзя!).
     * @return $this
     */
    public function addAttachment(string $file_name, string $content): Email
    {
        $this->attachments[$file_name] = $content;
        return $this;
    }

    /**
     * Метод устанавливает заголовки письма.
     *
     * @param array $headers Массив заголовков письма. Пока поддерживаются только два заголовка, Reply-To и Priority.
     * @return $this
     */
    public function setHeaders(array $headers): Email
    {
        $this->headers = [];
        foreach ($headers as $key => $value) {
            if (($tmp = $this->checkHeader($value, $key)) !== false) {
                $this->headers = array_merge($this->headers, $tmp);
            }
        }
        return $this;
    }

    /**
     * Метод добавляет заголовки письма.
     *
     * @param string $name Заголовок. Пока поддерживаются только два заголовка, Reply-To и Priority.
     * @param string $value Значение.
     * @return $this
     */
    public function addHeader(string $name, string $value): Email
    {
        if (($tmp = $this->checkHeader($value, $name)) !== false) {
            $this->headers = array_merge($this->headers, $tmp);
        }
        return $this;
    }

    /**
     * Метод парсит заголовки письма и проверяет их на соответствие правилам. Неподходящие заголовки будут проигнорированы
     *
     * @param string $value Значение.
     * @param string|int $key Заголовок.
     * @return array|false
     */
    protected function checkHeader(string $value, $key)
    {
        if (strpos($value, ':') !== false) {
            list($key, $value) = explode(':', $value);
        }

        $key = trim($key);

        if (in_array($key, self::ALLOW_HEADERS)) {
            return [$key => trim($value)];
        }

        return false;
    }

    /**
     * Метод подготавливает письмо в виде массива, пригодного для отправки через клиент.
     * При подготовке проверяются обязательные параметры письма. Если обязательные параметры не заданы, то будет выброшено исключение.
     *
     * @return array
     */
    public function getData(): array
    {
        $properties = get_object_vars($this);
        $data = [];
        foreach ($properties as $key => $value) {
            if (substr($key, 0, 1) === '_') {
                continue;
            }

            if (empty($value)) {
                continue;
            }

            if ($key === 'headers') {
                $headers = [];
                foreach ($value as $header_key => $header_value) {
                    $headers[] = trim($header_key) . ': ' . trim($header_value);
                }
                $data['headers'] = implode(PHP_EOL, $headers);
                continue;
            }

            $data[$key] = $value;
        }

        $required_fields = ['email', 'sender_name', 'sender_email', 'subject', 'body'];
        foreach ($required_fields as $field_name) {
            if (empty($data[$field_name])) {
                throw new UnexpectedValueException(sprintf('Missing required parameter "%s::$%s"', __CLASS__, $field_name));
            }
        }

        return $data;
    }
}