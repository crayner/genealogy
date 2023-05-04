<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NameSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('familyName', TextType::class,
                [
                    'label'  => 'Family Name',
                    'required' => false,
                ]
            )
            ->add('givenNames', TextType::class,
                [
                    'label' => 'Given Names',
                    'required' => false,
                ]
            )
            ->add('list', ChoiceType::class,
                [
                    'label' => 'Search Name List',
                    'required' => false,
                    'choice_translation_domain' => false,
                    'choices' => $this->getListChoices($options['list_choices']),
                ]
            )
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'name_search';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'list_choices' => [],
            ]
        );
    }

    /**
     * @param array $choices
     * @return array
     */
    private function getListChoices(array $choices): array
    {
        $result = [];
        foreach($choices as $item) {
            $result[trim(str_replace(['    ','   ','  '], ' ', $item['label']))] = $item['value'];
        }
        return $result;
    }
}