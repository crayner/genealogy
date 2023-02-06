<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MedalUpdaterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('biography', TextareaType::class,
                [
                    'label' => 'Biography',
                    'help' => 'The Wikitree Biography.',
                    'required' => true,
                    'attr' => [
                        'rows' => 25,
                    ],
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Modify Biography',
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
        parent::configureOptions($resolver);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return "wikitree_medal_updater";
    }

}