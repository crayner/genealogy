<?php
namespace App\Form;

use App\Entity\Category;
use App\Entity\Location;
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
            ->add('location', EntityType::class,
                [
                    'choice_label' => 'name',
                    'class' => Location::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('l')
                            ->orderBy('l.name', 'ASC');
                    },
                    'choice_value' => 'id',
                    'placeholder' => '',
                    'label' => 'Location',
                    'help' => 'This item requires a location',
                ]
            )
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
                    ]
                ]
            )
            ->add('doit', SubmitType::class,
                [
                    'label' => $options['doit'],
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

        $resolver->setAllowedTypes('elements', 'array');
        $resolver->setAllowedTypes('action', 'string');
        $resolver->setAllowedTypes('name', 'string');
        $template = $options['template'];
        $template['name']['elements'] = ['name', 'categoryType'];
        $template['name']['action'] = '/genealogy/category/name/save';
        $template['name']['name'] = 'name';

        $template['parents']['elements'] = ['location', 'parents'];
        $template['parents']['action'] = '/genealogy/category/parents/save';
        $template['parents']['name'] = 'parents';

        foreach ($template as $name => $x) {
            $template[$name] = $resolver->resolve($x);
        }
        $view->vars['template'] = $template;
    }
}