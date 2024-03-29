<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Message
{
    /**
     * @var string
     */
    private $level;

    /**
     * @var string
     */
    private $message;

    /**
     * @var array
     */
    private $options;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var mixed
     */
    private $transChoice = false;

    /**
     * @var bool
     */
    private $useRaw = false;

    /**
     * @var string
     */
    private $translatedMessage = '';

    /**
     * Message constructor.
     */
    public function __construct(string $message = '', array $options = [], string $domain = 'messages')
    {
        $this->setMessage($message);
        $this->setOptions($options);
        $this->setDomain($domain);
    }

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * @param string $level
     */
    public function setLevel(string $level)
    {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param $name
     * Special Options
     *     useRaw: The twig template will return raw content of the message.
     *     transChoice: The twig template use transChoice (and the transChoice value.)
     * @param $element
     * @return $this|Message
     */
    public function addOption($name, $element)
    {
        if ($name === 'transChoice')
            return $this->setTransChoice($element);

        if ($name === 'useRaw')
            return $this->setUseRaw();

        if ($name === 'closeButton')
            return $this->addButton($name, $element);

        if ($name === 'resetButton')
            return $this->addButton($name, $element);

        if ($name === 'fixedMessage')
            return $this->setFixedMessage($element);

        $this->options[$name] = $element;

        return $this;
    }

    public function removeOption($name)
    {
        if (isset($this->options[$name]))
            unset($this->options[$name]);

        return $this;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        if (empty($this->domain))
            return 'messages';

        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain)
    {
        $this->domain = $domain ?: 'messages';

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTransChoice()
    {
        return $this->transChoice;
    }

    /**
     * @param mixed $transChoice
     * @return Message
     */
    public function setTransChoice($transChoice)
    {
        $this->transChoice = $transChoice;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUseRaw(): bool
    {
        return $this->useRaw;
    }

    /**
     * @return Message
     */
    public function setUseRaw(): Message
    {
        $this->useRaw = true;
        return $this;
    }

    /**
     * @var ArrayCollection
     */
    private $buttons;

    /**
     * @return ArrayCollection
     */
    public function getButtons(): ArrayCollection
    {
        if (empty($this->buttons))
            $this->buttons = new ArrayCollection();
        return $this->buttons;
    }

    /**
     * @param $name
     * @param $element
     * @return Message
     */
    private function addButton($name, $element): Message
    {
        $element['name'] = $name;
        $this->getButtons()->add($element);
        return $this;
    }

    /**
     * @var bool
     */
    private $fixedMessage = false;

    /**
     * @return bool
     */
    public function isFixedMessage(): bool
    {
        return $this->fixedMessage;
    }

    /**
     * @param bool $fixedMessage
     * @return Message
     */
    public function setFixedMessage(bool $fixedMessage): Message
    {
        $this->fixedMessage = $fixedMessage;
        return $this->removeOption('fixedMessage');
    }

    /**
     * @return string
     */
    public function getTranslatedMessage(): string
    {
        return $this->translatedMessage;
    }

    /**
     * @param string $translatedMessage
     * @return Message
     */
    public function setTranslatedMessage(string $translatedMessage): Message
    {
        $this->translatedMessage = $translatedMessage;
        return $this;
    }
}