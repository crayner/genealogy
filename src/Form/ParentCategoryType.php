<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Location;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;

class ParentCategoryType extends CategoryType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['doit'] = 'Save Parent Categories';
        parent::buildForm($builder, $options);
        $builder
            ->add('parent', EntityType::class,
                [
                    'choice_label' => 'name',
                    'class' => Category::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                            ->where('c NOT INSTANCE OF :location')
                            ->setParameter('location', 'location')
                            ->orderBy('c.name', 'ASC');
                    },
                    'choice_value' => 'id',
                    'placeholder' => '',
                    'label' => 'Parent Category',
                    'help' => 'Add a parent category.',
                    'required' => false,
                ]
            )
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'parent_category';
    }
}