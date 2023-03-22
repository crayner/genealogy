<?php

namespace App\Form;

use App\Entity\Location;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;

class LocationType extends CategoryType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['doit'] = 'Save Location';
        parent::buildForm($builder, $options);
        $builder
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
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'category_location';
    }
}