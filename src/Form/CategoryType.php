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
        if ($options['data'] instanceof Location) {
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
            ;
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
            ->add('doit', SubmitType::class,
                [
                    'label' => $options['doit'],
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
                'doit' => 'Save Category',
                'template' => [],
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
            'fetch' => null,
            'display' => true,
        ]);

        $resolver->setAllowedTypes('elements', 'array');
        $resolver->setAllowedTypes('action', 'string');
        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('remove', ['string', 'null']);
        $resolver->setAllowedTypes('fetch', ['array', 'null']);
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

        $template['address']['elements'] = ['address', 'location'];
        $template['address']['action'] = '/genealogy/category/address/save';
        $template['address']['name'] = 'address';
        $template['address']['display'] = $form->getData() instanceof Location && get_class($form->getData()) !== Location::class;

        foreach ($template as $name => $x) {
            $template[$name] = $resolver->resolve($x);
        }
        $view->vars['template'] = $template;
    }
}