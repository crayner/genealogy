<?php
namespace App\Manager;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class FormManager
{
    /**
     * @var Environment
     */
    private Environment $twig;

    /**
     * @var RequestStack
     */
    private RequestStack $stack;

    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * @var FormErrorsParser
     */
    private FormErrorsParser $parser;

    /**
     * @var array
     */
    private array $data = [];

    /**
     * @var FormInterface
     */
    private FormInterface $form;

    /**
     * @var array
     */
    private array $props;

    /**
     * @var MessageManager
     */
    private MessageManager $messageManager;

    /**
     * @param Environment $twig
     * @param RequestStack $stack
     * @param FormErrorsParser $parser
     * @param TranslatorInterface $translator
     */
    public function __construct(Environment $twig, RequestStack $stack, FormErrorsParser $parser, TranslatorInterface $translator)
    {
        $this->twig = $twig;
        $this->stack = $stack;
        $this->parser = $parser;
        $this->translator = $translator;
        $this->data = [];
    }

    /**
     * getRequest
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->getStack()->getCurrentRequest();
    }

    /**
     * @return Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * @return RequestStack
     */
    public function getStack(): RequestStack
    {
        return $this->stack;
    }

    /**
     * extractForm
     *
     * @param FormInterface|FormView $formView
     * @param boolean $prototype
     * @return array
     */
    public function extractForm(FormView|FormInterface $formView, bool $prototype = false): array
    {
        if ($formView instanceof FormInterface)
        {
            $this->form = $formView;
            $formView = $this->form->createView();
        }
        if (! $formView instanceof FormView)
            trigger_error(sprintf('Argument 1 passed to %s() must be an instance of Symfony\Component\Form\FormView or Symfony\Component\Form\Form, instance of %s given.', __METHOD__, get_class($formView)), E_USER_ERROR);

        $vars = $formView->vars;

        $vars['children'] = [];
        foreach($formView->children as $child)
        {
            $vars['children'][] = $this->extractForm($child);
        }

        if (is_object($vars['value'])){
            $vars['data_id'] = null;
            $vars['data_toString'] = null;
            if (method_exists($vars['value'], 'getId'))
                $vars['data_id'] = $vars['value']->getId();
            if (method_exists($vars['value'], 'getName'))
                $vars['data_toString'] = $vars['value']->getName();
            if (method_exists($vars['value'], '__toString'))
                $vars['data_toString'] = $vars['value']->__toString();
        }
        if (isset($vars['prototype']) && $vars['prototype'] instanceof FormView)
        {
            $vars['prototype'] = $this->extractForm($vars['prototype'], true);
        }

        $vars['required'] = array_key_exists('required', $vars) ? $this->getTranslator()->trans('form.required', [], 'FormTheme') : '';

        $vars['type'] = 'unknown';
        foreach(array_reverse($vars['block_prefixes']) as $type) {
            if (in_array($type, [
                'text',
                'choice',
                'hidden',
                'submit',
                'collection'
            ])) {
                $vars['type'] = $type;
                break;
            }
        }
//        if ($vars['type'] === 'unknown') dump($vars['block_prefixes']);

        if (! empty($vars['label']))
            $vars['label'] = $this->getTranslator()->trans($vars['label'], [], $vars['translation_domain']);
        else
            $vars['label'] = '';

        if (! empty($vars['placeholder']))
            $vars['placeholder'] = $this->getTranslator()->trans($vars['placeholder'], [], $vars['translation_domain']);

        $vars['help_params'] = array_key_exists('help_params', $vars) ? $vars['help_params'] : [];
        if (! empty($vars['help']))
            $vars['help'] = $this->getTranslator()->trans($vars['help'], $vars['help_params'], $vars['translation_domain']);
        else
            $vars['help'] = '';

        if (isset($vars['choices'])) {
            $x = $this->getFormInterface($this->form, $vars['id']);
            if (empty($vars['value'])) {
                if (empty($vars['value']) && ! empty($x->getViewData()))
                    $vars['value'] = $x->getViewData();
                if (empty($vars['value']) && ! empty($x->getNormData()))
                    $vars['value'] = $x->getNormData();
                if (empty($vars['value']) && ! empty($x->getData()))
                    $vars['value'] = $x->getData();
                $vars['data'] = $vars['value'];
            }
            $vars['choices'] = $this->translateChoices($vars);
            if (empty($vars['value']) && ! empty($vars['placeholder']))
                $vars['value'] = $vars['data'] = '';
            else if (empty($vars['value']) && ! empty($vars['choices'][0]) && ! $vars['multiple'])
                $vars['value'] = $vars['data'] = $vars['choices'][0]->value;
            if ($vars['multiple'] && $vars['value'] instanceof Collection)
                $vars['value'] = $vars['value']->toArray();

            if (! empty($x->getConfig()->getOption('choice_attr')))
                foreach($vars['choices'] as $choice)
                    $choice->attr = $x->getConfig()->getOption('choice_attr');
            if ($vars['expanded'])
                $vars['children'] = [];
        }
        if (array_key_exists('errors', $vars) && $vars['errors']->count() > 0) {
            $errors = [];
            foreach($vars['errors'] as $error)
                $errors[] = $error->getMessage();
            $vars['errors'] = $errors;
        } else
            $vars['errors'] = [];

        unset($vars['form']);

        if (! $prototype)
            $vars['constraints'] = $this->extractConstraints($vars);
        else
            $vars['constraints'] = [];
        return $vars;
    }

    /**
     * extractConstraints
     *
     * @param $vars
     * @return array
     */
    private function extractConstraints($vars): array
    {
        $form = $this->getFormInterface($this->form, $vars['id']);
        $result = [];
        $required = false;
        $constraints = $form->getConfig()->getOption('constraints');
        if (!is_array($constraints)) return [];
        foreach($constraints as $q=>$constraint)
        {
            $result[$q] = (array) $constraint;
            $name = explode('\\',get_class($constraint));
            $result[$q]['class'] = end($name);

            switch($result[$q]['class']) {
                case 'NotBlank':
                    $result[$q]['message'] = $this->getTranslator()->trans($result[$q]['message'], [], 'validators');
                    $required = true;
                    break;
                case 'Colour':
                    $result[$q]['message'] = $this->getTranslator()->trans($result[$q]['message'], [], 'validators');
                    break;
                case 'Length':
                    $result[$q]['maxMessage'] = $this->getTranslator()->transChoice($result[$q]['maxMessage'], $result[$q]['max'], ['{{ limit }}' => $result[$q]['max']], 'validators');
                    $result[$q]['minMessage'] = $this->getTranslator()->transChoice($result[$q]['minMessage'], $result[$q]['min'], ['{{ limit }}' => $result[$q]['min']], 'validators');
                    $result[$q]['exactMessage'] = $this->getTranslator()->transChoice($result[$q]['exactMessage'], $result[$q]['min'], ['{{ limit }}' => $result[$q]['max']], 'validators');
                    break;
                case 'Choice':
                    $result[$q]['message'] = $this->getTranslator()->trans($result[$q]['message'], [], 'validators');
                    $result[$q]['multipleMessage'] = $this->getTranslator()->trans($result[$q]['multipleMessage'], [], 'validators');
                    $result[$q]['maxMessage'] = $this->getTranslator()->transChoice($result[$q]['maxMessage'], $result[$q]['max'], ['{{ limit }}' => $result[$q]['max']], 'validators');
                    $result[$q]['minMessage'] = $this->getTranslator()->transChoice($result[$q]['minMessage'], $result[$q]['min'], ['{{ limit }}' => $result[$q]['min']], 'validators');
                    break;
                default:
                    dump($result[$q]);
                    trigger_error(sprintf('The constraint (%s) has no handler in the React Form Manager', $result[$q]['class']), E_USER_ERROR);
            }

        }
        if (! empty($vars['required']) && ! $required)
        {
            $notBlank['message'] = $this->getTranslator()->trans('The value should not be empty!', [], 'validators');
            $notBlank['class'] = 'NotBlank';
            $result[] = $notBlank;
        }
        return $result;
    }

    /**
     * getFormInterface
     *
     * @param FormInterface $form
     * @param $id
     * @return FormInterface
     */
    private function getFormInterface(FormInterface $form, $id): FormInterface
    {
        $name = $form->getName();
        if ($id === $name)
            return $form;
        if (mb_strpos($id, $name.'_') === 0) {
            $id = mb_substr($id, mb_strlen($name . '_'));
            foreach ($form->all() as $name => $child) {
                if ($id === $name)
                    return $child;
                if (mb_strpos($id, $name.'_') === 0)
                    return $this->getFormInterface($child, $id);
            }
        }
        return $form;
    }

    /**
     * getFormErrors
     *
     * Main Twig extension. Call this in Twig to get formatted output of your form errors.
     * Note that you have to provide form as Form object, not FormView.
     * @param FormInterface $form
     * @param string $transDomain
     * @return array
     */
    public function getFormErrors(FormInterface $form, string $transDomain = 'messages'): array
    {
        if (!$form->isSubmitted() || false) return [];

        $messages = $this->getMessageManager();

        $errorList = $this->getParser()->parseErrors($form);
        $errorList = is_array($errorList) ? $errorList: [];
        

        foreach($errorList as $q=>$w) {
            $errorList[$q]['messages'] = [];
            foreach($w['errors'] as $error) {
                $errorList[$q]['messages'][] = $error->getMessage();
                $messages->addMessage('danger', $error->getMessage(), [], false);
            }
        }

        if (empty($errorList))
            $messages->addMessage('success', 'All details were saved successfully.', [], 'messages');

        return $messages->serialiseTranslatedMessages($this->getTranslator());
    }

    /**
     * @return FormErrorsParser
     */
    public function getParser(): FormErrorsParser
    {
        return $this->parser;
    }

    /**
     * validateRows
     *
     * @param $rows
     * @return array|boolean
     */
    private function validateRows($rows)
    {
        if ($rows === false)
            return $rows;
        if (empty($rows))
            return $rows ?: [];
        foreach($rows as $e=>$r){

            $rows[$e] = $this->validateRow($r);
        }
        return $rows;

    }

    /**
     * validateRow
     *
     * @param $row
     * @return array
     */
    private function validateRow($row)
    {
        if ($row === false)
            return $row;
        $resolver = new OptionsResolver();
        $resolver->setRequired([
            'class',
            'columns',
        ]);
        $resolver->setAllowedTypes('class', 'string');
        $resolver->setAllowedTypes('columns', 'array');
        $row = $resolver->resolve($row);
        if (empty($row['columns']))
            trigger_error(sprintf('An array of columns is compulsory for each row.'), E_USER_ERROR);
        $row['columns'] = $this->validateColumns($row['columns']);
        return $row;
    }

    /**
     * validateRows
     *
     * @param $rows
     * @return array
     */
    private function validateColumns($columns): array
    {
        foreach($columns as $e=>$r){
            $columns[$e] = $this->validateColumn($r);
        }
        return $columns;

    }

    /**
     * validateColumn
     *
     * @param $column
     * @return mixed
     */
    private function validateColumn($column)
    {
        if ($column === false)
            return $column;
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'form' => false,
            'label' => false,
            'label_params' => [],
            'class' => false,
            'buttons' => false,
            'container' => false,
            'rows' => false,
            'collection_actions' => false,
            'style' => false,
            'onClick' => false,
        ]);
        $resolver->setAllowedTypes('class', ['boolean','string']);
        $resolver->setAllowedTypes('buttons', ['boolean','array']);
        $resolver->setAllowedTypes('label', ['boolean', 'string']);
        $resolver->setAllowedTypes('label_params', ['array']);
        $resolver->setAllowedTypes('container', ['boolean', 'array']);
        $resolver->setAllowedTypes('rows', ['boolean', 'array']);
        $resolver->setAllowedTypes('style', ['boolean', 'array']);
        $resolver->setAllowedTypes('onClick', ['boolean', 'array']);
        $resolver->setAllowedTypes('form', ['array', 'boolean']);
        $resolver->setAllowedTypes('collection_actions', ['boolean']);
        $column = $resolver->resolve($column);

        if ($column['rows'] && $column['container'])
            trigger_error(sprintf('A column must not have a container and rows assign. Choose either container or rows or neither.'), E_USER_ERROR);

        $column['container'] = $this->validateContainer($column['container']);
        $column['rows'] = $this->validateRows($column['rows']);
        $column['onClick'] = $this->validateUrl($column['onClick']);
        $column['buttons'] = $this->validateButtons($column['buttons']);
        if (is_array($column['form']))
            $this->addFormTabMap(key($column['form']));

        $column['label'] = $column['label'] ? $this->getTranslator()->trans($column['label'], $column['label_params'], $this->getTranslationDomain()) : false;

        return $column;
    }

    /**
     * getTranslator
     *
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * validateContainer
     *
     * @param $container
     * @return array
     */
    private function validateContainer($container)
    {
        if ($container === false)
            return $container;
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'panel' => false,
            'class' => false,
            'rows' => false,
            'headerRow' => false,
            'collection' => false,
        ]);
        $resolver->setAllowedTypes('class', ['string', 'boolean']);
        $resolver->setAllowedTypes('panel', ['array', 'boolean']);
        $resolver->setAllowedTypes('rows', ['array', 'boolean']);
        $resolver->setAllowedTypes('headerRow', ['array', 'boolean']);
        $resolver->setAllowedTypes('collection', ['array', 'boolean']);
        $container = $resolver->resolve($container);

        $container['panel'] = $this->validatePanel($container['panel']);

        if (($container['panel'] !== false && $container['class'] !== false) || ( $container['panel'] === false && $container['class'] === false))
            trigger_error(sprintf('Containers must specify one of a panel (%s) or a class (%s), but not both.', $container['panel']['colour'], $container['class']), E_USER_ERROR);

        $container['collection'] = $this->validateCollection($container['collection']);

        $container['headerRow'] = $this->validateRow($container['headerRow']);
        $container['rows'] = $this->validateRows($container['rows']);

        return $container;
    }

    /**
     * validatePanel
     *
     * @param $panel
     * @return array
     */
    private function validatePanel($panel)
    {
        if ($panel === false)
            return $panel;
        $resolver = new OptionsResolver();
        $resolver->setRequired([
            'label',
        ]);
        $resolver->setDefaults([
            'colour' => 'info',
            'description' => false,
            'buttons' => false,
            'label_params' => [],
            'description_params' => [],
            'rows' => [],
            'collection' => false,
            'headerRow' => false,
        ]);
        $resolver->setAllowedTypes('colour', ['string']);
        $resolver->setAllowedTypes('label', ['string']);
        $resolver->setAllowedTypes('label_params', ['array']);
        $resolver->setAllowedTypes('rows', ['array']);
        $resolver->setAllowedTypes('headerRow', ['array', 'boolean']);
        $resolver->setAllowedTypes('collection', ['array', 'boolean']);
        $resolver->setAllowedTypes('description_params', ['array']);
        $resolver->setAllowedTypes('description', ['boolean', 'string']);
        $resolver->setAllowedTypes('buttons', ['boolean', 'array']);
        $panel = $resolver->resolve($panel);

        $panel['buttons'] = $this->validateButtons($panel['buttons']);
        $panel['rows'] = $this->validateRows($panel['rows']);
        $panel['collection'] = $this->validateCollection($panel['collection']);
        $panel['headerRow'] = $this->validateRow($panel['headerRow']);

        if ($panel['label'])
            $panel['label'] = $this->getTranslator()->trans($panel['label'], $panel['label_params'], $this->getTemplateManager()->getTranslationDomain());
        if ($panel['description'])
            $panel['description'] = $this->getTranslator()->trans($panel['description'], $panel['description_params'], $this->getTemplateManager()->getTranslationDomain());

        return $panel;
    }

    /**
     * validateButtons
     *
     * @param $buttons
     * @return mixed
     */
    private function validateButtons($buttons)
    {
        if ($buttons === false)
            return $buttons;

        foreach($buttons as $q=>$w)
        {
            $buttons[$q] = ButtonReactManager::validateButton($w,$this->getTranslator(),$this->getTemplateManager());
        }
        return $buttons;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * validateForm
     *
     * @param array $form
     * @return array
     */
    private function validateForm(array $form): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired([
            'url',
        ]);
        $resolver->setDefaults([
            'method' => 'POST',
            'encType' => 'application/x-www-form-urlencoded',
            'url_options' => [],
        ]);
        $resolver->setAllowedTypes('url', ['string']);
        $resolver->setAllowedTypes('url_options', ['array']);
        $resolver->setAllowedTypes('method', ['string']);
        $resolver->setAllowedTypes('encType', ['string']);
        $resolver->setAllowedValues('encType', ['application/x-www-form-urlencoded', 'text/plain', 'multipart/form-data']);
        $resolver->setAllowedValues('method', ['POST', 'GET']);

        $form = $resolver->resolve($form);

        if (! empty($form['url_options']))
        {
            foreach($form['url_options'] as $q=>$w)
            {
                $method = 'get' . ucfirst($w);
                $found = false;
                if (method_exists($this->getTemplateManager()->getEntity(), $method))
                {
                    $form['url'] = str_replace($q, $this->getTemplateManager()->getEntity()->$method(), $form['url']);
                    $found = true;
                }
                $method = 'is' . ucfirst($w);
                if (method_exists($this->getTemplateManager()->getEntity(), $method) && ! $found)
                {
                    $form['url'] = str_replace($q, $this->getTemplateManager()->getEntity()->$method(), $form['url']);
                    $found = true;
                }
                if (! $found)
                    trigger_error(sprintf('The form url does not have an option %s in the entity %s.', $w, get_class($this->getTemplateManager()->getEntity())), E_USER_ERROR);
            }
        }

        return $form;
    }

    /**
     * @var
     */
    private $currentTab;

    /**
     * validateTabs
     *
     * @param $tabs
     * @return array|boolean
     */
    private function validateTabs($tabs)
    {
        if ($tabs === false)
            return $tabs;

        foreach($tabs as $q=>$tab){
            $resolver = new OptionsResolver();
            $resolver->setRequired([
                'name',
                'container',
            ]);
            $resolver->setDefaults([
                'label' => false,
                'label_params' => [],
                'display' => true,
            ]);

            if (!empty($tab['display']) && is_string($tab['display'])) {
                $method = $tab['display'];
                $tab['display'] = $this->$method();
            }
            $resolver->setAllowedTypes('label', ['boolean','string']);
            $resolver->setAllowedTypes('display', ['boolean']);
            $resolver->setAllowedTypes('name', ['string']);
            $resolver->setAllowedTypes('container', ['array']);
            $resolver->setAllowedTypes('label_params', ['array']);

            $tabs[$q] = $resolver->resolve($tab);
            $this->setCurrentTab($tab['name']);
            $tabs[$q]['container'] = $this->validateContainer($tab['container']);

        }


        $x = 0;
        foreach($tabs as $tab)
            if ($tab['name'] === $this->getRequestedTab())
                break;
            else
                $x++;
        $tabs['selectedTab'] = $x;
        return $tabs;
    }

    /**
     * validateCollection
     *
     * @param $collection
     * @return array
     */
    private function validateCollection($collection)
    {
        if ($collection === false)
            return $collection;
        $resolver = new OptionsResolver();
        $resolver->setRequired([
            'form',
            'rows',
        ]);
        $resolver->setDefaults([
            'buttons' => [],
            'sortBy' => false,
            'headerRow' => false,
        ]);
        $resolver->setAllowedTypes('form', ['string']);
        $resolver->setAllowedTypes('rows', ['array']);
        $resolver->setAllowedTypes('buttons', ['array']);
        $resolver->setAllowedTypes('sortBy', ['array', 'boolean']);
        $resolver->setAllowedTypes('headerRow', ['array', 'boolean']);
        $collection = $resolver->resolve($collection);
        $this->addFormTabMap($collection['form']);
        $collection['rows'] = $this->validateRows($collection['rows']);
        $collection['headerRow'] = $this->validateRow($collection['headerRow']);
        $collection['buttons'] = $this->validateButtons($collection['buttons']);
        if (is_array($collection['sortBy']) && count($collection['sortBy']) > 3)
            trigger_error(sprintf('The depth of sort for a collection is limited to 3 levels. %d > 3', count($collection['sortBy'])), E_USER_ERROR);

        return $collection;
    }

    /**
     * translateChoices
     *
     * @param array $vars
     * @return array
     */
    private function translateChoices(array $vars): array
    {
        $domain = $vars['choice_translation_domain'];
        if ($domain === false)
            return $vars['choices'];
        if (empty($domain))
            $domain = $vars['translation_domain'];
        if ($domain === false)
            return $vars['choices'];
        if (empty($domain))
            $domain = 'messages';
        if ($domain === false)
            return $vars['choices'];

        foreach($vars['choices'] as $choice)
        {
            if (is_object($choice->data))
                return $vars['choices'];
            $choice->label = $this->getTranslator()->trans($choice->label, [], $domain);
        }

        return $vars['choices'];
    }

    /**
     * @param mixed $currentTab
     * @return FormManager
     */
    public function setCurrentTab($currentTab)
    {
        $this->currentTab = $currentTab;
        return $this;
    }

    /**
     * @return
     */
    public function getCurrentTab(): ?string
    {
        return $this->currentTab;
    }

    /**
     * addFormTabMap
     *
     * @param string $formTabMap
     * @return FormManager
     */
    public function addFormTabMap(string $formTabMap): FormManager
    {
        if (empty($this->getCurrentTab()))
            return $this;
        $this->formTabMap[$formTabMap] = $this->getCurrentTab();
        return $this;
    }

    /**
     * @return array
     */
    public function getButtonTypeList(): array
    {
        return $this->buttonTypeList;
    }

    /**
     * getLocale
     *
     * @return bool|string
     */
    private function getLocale()
    {
        if ($this->getTemplateManager()->isLocale() === false)
            return false;
        return $this->getRequest()->get('_locale') ?: 'en';
    }

    /**
     * getProps
     *
     * @return array
     */
    public function getProps(): array
    {
        return $this->props;
    }

    /**
     * getRequestedTab
     *
     * @return null|string
     */
    private function getRequestedTab(): ?string
    {
        return $this->getRequest()->get('tabName');
    }

    /**
     * getTargetDivision
     *
     * @return string
     */
    private function getTargetDivision(): string
    {
        if (method_exists($this->getTemplateManager(), 'getTargetDivision'))
            return $this->getTemplateManager()->getTargetDivision();
        return 'pageContent';
    }

    /**
     * getTranslatorDomain
     *
     * @return string
     */
    private function getTranslationDomain(): ?string
    {
        return $this->getTemplateManager()->getTranslationDomain() ?: null;
    }

    /**
     * @return MessageManager
     */
    private function getMessageManager(): MessageManager
    {
        return $this->messageManager = !isset($this->messageManager) ? new MessageManager('messages') : $this->messageManager;
    }

    /**
     * @param $url
     * @return array|false
     */
    private function validateUrl($url)
    {
        if ($url === false)
            return $url;
        $resolver = new OptionsResolver();
        $resolver->setRequired([
            'url',
        ]);
        $resolver->setDefaults([
            'url_options' => [],
            'url_type' => 'json',
        ]);
        $resolver->setAllowedTypes('url_options', ['array']);
        $resolver->setAllowedTypes('url', ['string']);
        $resolver->setAllowedTypes('url_type', ['string']);
        $resolver->setAllowedValues('url_type', ['redirect', 'json']);
        $url = $resolver->resolve($url);
        return $url;
    }

    /**
     * @param Symfony\Contracts\Translation\TranslatorInterface $translator
     * @return FormManager
     */
    public function setTranslator(Symfony\Contracts\Translation\TranslatorInterface $translator): FormManager
    {
        $this->translator = $translator;
        return $this;
    }
}