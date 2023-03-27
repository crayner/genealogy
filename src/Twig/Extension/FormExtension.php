<?php
namespace App\Twig\Extension;

use App\Manager\FormManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FormExtension extends AbstractExtension
{
    /**
     * getName
     *
     * @return string
     */
    public function getName()
    {
        return 'form_extension';
    }

    /**
     * getFunctions
     *
     * @return array|TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('formTranslations', array($this, 'formTranslations')),
            new TwigFunction('renderForm', [$this->formManager, 'renderForm'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * formTranslations
     *
     * @return array
     */
    public function formTranslations(): array
    {
        return [
            'form.required',
        ];
    }

    /**
     * @var FormManager
     */
    private FormManager $formManager;

    /**
     * FormExtension constructor.
     * @param FormManager $formManager
     */
    public function __construct(FormManager $formManager)
    {
        $this->formManager = $formManager;
    }
}