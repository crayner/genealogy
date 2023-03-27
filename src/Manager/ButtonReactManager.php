<?php

namespace App\Manager;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ButtonReactManager
 * @package Hillrange\Form\Util
 */
class ButtonReactManager
{
    /**
     * @var array
     */
    private static $buttonTypeList = ['save','submit','add','edit','delete','return','duplicate','refresh','close','up','down','misc'];

    /**
     * getButtonReactInterface
     *
     * @return ButtonReactInterface
     */
    public static function getButtonReactInterface(): ButtonReactInterface
    {
        return self::$buttonReactInterface;
    }

    /**
     * setButtonReactInterface
     *
     * @param ButtonReactInterface $buttonReactInterface
     */
    public static function setButtonReactInterface(ButtonReactInterface $buttonReactInterface): void
    {
        self::$buttonReactInterface = $buttonReactInterface;
    }

    /**
     * getButtonTypeList
     *
     * @return array
     */
    public static function getButtonTypeList(): array
    {
        return self::$buttonTypeList;
    }

    /**
     * @var ButtonReactInterface
     */
    private static $buttonReactInterface;

    /**
     * validateButton
     *
     * @param $button
     * @param TranslatorInterface $translator
     * @param $manager
     * @return array
     */
    public static function validateButton($button, TranslatorInterface $translator, ButtonReactInterface $manager)
    {
        if ($button === false)
            return $button;
        self::setButtonReactInterface($manager);
        $resolver = new OptionsResolver();
        $resolver->setRequired([
            'type',
        ]);
        $resolver->setDefaults([
            'mergeClass' => '',
            'style' => false,
            'redirect_options' => [],
            'collection_options' => [],
            'json_options' => [],
            'url' => false,
            'url_options' => [],
            'url_type' => 'json',
            'display' => true,
            'colour' => '',
            'class' => false,
            'icon' => false,
            'title' => '',
            'title_params' => [],
            'prompt' => '',
            'prompt_params' => [],
        ]);
        $resolver->setAllowedTypes('type', ['string']);
        $resolver->setAllowedTypes('mergeClass', ['string']);
        $resolver->setAllowedTypes('style', ['boolean','array']);
        $resolver->setAllowedTypes('redirect_options', ['array']);
        $resolver->setAllowedTypes('collection_options', ['array']);
        $resolver->setAllowedTypes('json_options', ['array']);
        $resolver->setAllowedTypes('title', ['string']);
        $resolver->setAllowedTypes('title_params', ['array']);
        $resolver->setAllowedTypes('url_options', ['array']);
        $resolver->setAllowedTypes('url', ['boolean','string']);
        $resolver->setAllowedTypes('class', ['boolean','string']);
        $resolver->setAllowedTypes('icon', ['boolean','array']);
        $resolver->setAllowedTypes('colour', ['null','string']);
        $resolver->setAllowedTypes('url_type', ['string']);
        $resolver->setAllowedValues('type', self::getButtonTypeList());
        $resolver->setAllowedTypes('display', ['boolean', 'string']);
        $resolver->setAllowedValues('url_type', ['redirect', 'json']);
        $resolver->setAllowedTypes('prompt', ['string']);
        $resolver->setAllowedTypes('prompt_params', ['array']);
        $button = $resolver->resolve($button);
        $button['display'] = self::displayButton($button['display']);
        if (! empty($button['title']))
            $button['title'] = $translator->trans($button['title'], $button['title_params'], self::$buttonReactInterface->getTranslationDomain());
        if (! empty($button['prompt']))
            $button['prompt'] = $translator->trans($button['prompt'], $button['prompt_params'], self::$buttonReactInterface->getTranslationDomain());
        return $button;
    }

    /**
     * displayButton
     *
     * @param $display
     * @return bool
     */
    private static function displayButton($display): bool
    {
        if (is_bool($display))
            return $display;

        if (!method_exists(self::getButtonReactInterface(), $display))
            trigger_error(sprintf('The manager "%s" must have a method "%s"',get_class(self::getButtonReactInterface()), $display), E_USER_ERROR);

        return self::getButtonReactInterface()->$display();
    }
}