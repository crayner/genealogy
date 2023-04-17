<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryAddType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,
                [
                    'label' => 'Category Name',
                ]
            )
            ->add('categoryType', ChoiceType::class,
                [
                    'label' => 'Category Type',
                    'choices' => Category::getCategoryTypeList(true),
                    'placeholder' => 'You must select a category type',
                    'help' => 'categoryTypeHelp',
                    'help_translation_parameters' => ['category' => 'Category'],
                ]
            )
            ->add('doit', SubmitType::class,
                [
                    'label' => $options['doit'],
                ]
            )
            ->add('search', CollectionType::class,
                [
                    'label' => false,
                    'help' => 'Search for a category.',
                    'required' => false,
                    'entry_type' => EntityType::class,
                    'mapped' => false,
                    'entry_options' => [
                        'class' => Category::class,
                        'choice_value' => 'id',
                        'choice_label' => 'name',
                        'choices' => [],
                        'multiple' => false,
                    ],
                ]
            )
        ;
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'doit' => 'Add Category',
            'template' => [],
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'category';
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     * @return void
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired([
            'elements',
            'action',
            'name',
        ]);
        $resolver->setDefaults([
            'remove' => null,
            'fetch' => false,
            'display' => true,
            'prototype' => [],
        ]);

        $resolver->setAllowedTypes('elements', 'array');
        $resolver->setAllowedTypes('action', 'string');
        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('remove', ['string', 'null']);
        $resolver->setAllowedTypes('fetch', ['array', 'boolean']);
        $resolver->setAllowedTypes('display', 'boolean');

        $template = $options['template'];
        $template['name']['elements'] = ['name', 'categoryType'];
        $template['name']['action'] = '/genealogy/category/add';
        $template['name']['name'] = 'name';

        $template['search']['action'] = '/genealogy/category/{category}/modify';
        $template['search']['name'] = 'search';
        $template['search']['fetch']['search'] = '/genealogy/category/parents/fetch';
        $template['search']['elements'] = ['search'];

        foreach ($template as $name => $x) {
            $template[$name] = $resolver->resolve($x);
        }
        $view->vars['template'] = $template;
    }
}