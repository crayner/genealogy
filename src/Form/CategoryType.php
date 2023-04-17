<?php
namespace App\Form;

use App\Entity\Category;
use App\Entity\Location;
use App\Form\DataTransformer\EntityCollectionTransformer;
use App\Manager\CategoryManager;
use Doctrine\ORM\EntityRepository;
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

class CategoryType extends AbstractType
{
    /**
     * @var EntityCollectionTransformer
     */
    private EntityCollectionTransformer $transformer;

    /**
     * @param EntityCollectionTransformer $transformer
     */
    public function __construct(EntityCollectionTransformer $transformer) {
        $this->transformer = $transformer;
        $this->transformer->setEntityClass(Category::class);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
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
                    'help_translation_parameters' => ['category' => basename(get_class($options['data'])) ?: 'Category'],
                ]
            )
            ->add('aka', TextType::class,
                [
                    'label' => 'Alternate Names (AKA)',
                    'help' => 'A list of alternate names for this category, separated by "|".'
                ]
            )
            ->add('displayName', TextType::class,
                [
                    'label' => 'Display Name',
                    'help' => 'The display name will default to the name of the category.'
                ]
            )
            ->add('sortName', TextType::class,
                [
                    'label' => 'Sort Name',
                    'help' => 'The sort name is used by the system to sort lists of which this category is a part. It defaults to the category name.'
                ]
            )
        ;
        if (is_subclass_of($options['data'], Location::class)) {
            $builder
                ->add('location', EntityType::class,
                    [
                        'choice_label' => 'name',
                        'class' => Location::class,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('l')
                                ->where("l INSTANCE OF :location")
                                ->setParameter('location', 'location')
                                ->orderBy('l.name', 'ASC');
                        },
                        'choice_value' => 'id',
                        'placeholder' => '',
                        'label' => 'Location',
                        'help' => 'This item requires a location',
                    ]
                )
                ->add('address', TextType::class,
                    [
                        'label' => 'Address',
                        'help' => 'This item requires an address',
                    ]
                )
                ->add('coordinates', TextType::class,
                    [
                        'label' => 'Coordinates',
                        'help' => 'GPS Longitude and Latitude separated by a comma.'
                    ]
                );
        } else if ($options['data'] instanceof Location) {
            $builder
                ->add('coordinates', TextType::class,
                    [
                        'label' => 'Coordinates',
                        'help' => 'GPS Longitude and Latitude separated by a comma.'
                    ]
                );

        } else {
            $builder
                ->add('location', HiddenType::class,
                    [
                        'data' => null,
                    ]
                );
        }
        $builder
            ->add('parents', CollectionType::class,
                [
                    'label' => 'Parent Categories',
                    'help' => 'Add/Remove a parent category.',
                    'required' => false,
                    'entry_type' => EntityType::class,
                    'entry_options' => [
                        'class' => Category::class,
                        'choice_value' => 'id',
                        'choices' => [],
                        'multiple' => true,
                    ],
                ]
            )
            ->add('webpages', CollectionType::class,
                [
                    'label' => 'Web Pages',
                    'entry_type' => CategoryWebPageType::class,
                    'allow_add' => true,
                    'entry_options' => [
                        'category_class' => get_class($options['data']) ?? Category::class,
                    ],
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
        $builder->get('parents')
            ->addModelTransformer($this->transformer);

    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Category::class,
                'translation_domain' => 'messages',
                'doit' => 'Save Category',
                'template' => [],
                'validation_groups' => false,
            ]
        );
        $resolver->setRequired(['manager']);
        $resolver->setAllowedTypes('manager', CategoryManager::class);
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
        $template['name']['elements'] = ['name', 'categoryType', 'displayName', 'aka', 'sortName'];
        $template['name']['action'] = '/genealogy/category/name/save';
        $template['name']['name'] = 'name';

        $template['parents']['elements'] = ['parents'];
        $template['parents']['action'] = '/genealogy/category/parents/save';
        $template['parents']['remove'] = '/genealogy/category/parent/{category}/{parent}/remove';
        $template['parents']['fetch']['parents'] = '/genealogy/category/parents/fetch';
        $template['parents']['name'] = 'parents';

        $template['address']['elements'] = ['address', 'location', 'coordinates'];
        $template['address']['action'] = '/genealogy/category/address/save';
        $template['address']['name'] = 'address';
        $template['address']['display'] = $form->getData() instanceof Location && get_class($form->getData()) !== Location::class;

        $template['webpages']['elements'] = ['webpages'];
        $template['webpages']['action'] = '/genealogy/category/webpages/save';
        $template['webpages']['name'] = 'webpages';
        $template['webpages']['prototype'] = ['value' => 'id', 'label' => 'prompt'];
        $template['webpages']['remove'] = '/genealogy/category/webpage/{category}/{item}/remove';

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