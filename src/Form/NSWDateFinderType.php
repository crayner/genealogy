<?php
/**
 * Created by PhpStorm.
 *
 * genealogy
 * (c) 2021 Craig Rayner <craig@craigrayner.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: Craig Rayner
 * Date: 1/03/2022
 * Time: 07:55
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class NSWDateFinderType
 * @selectPure App\Form
 * @author  Craig Rayner <craig@craigrayner.com>
 * 1/03/2022 08:04
 */
class NSWDateFinderType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('searchIn', ChoiceType::class,
                [
                    'label' => 'Search for date of',
                    'choices' => [
                        'finder.birth' => 'births',
                        'finder.marriage' => 'marriages',
                        'finder.death' => 'deaths'
                    ],
                    'placeholder' => 'Type of search',
                ]
            )
        ;
        if (!key_exists('searchIn', $options['data']))
        {
            $options['data']['searchIn'] = '';
        }
        $this->buildSubForm($options['data']['searchIn'],$builder, $options);
        $builder
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Find Date',
                ]
            )
            ->add('reset', SubmitType::class,
                [
                    'label' => 'Reset Finder',
                ]
            )
        ;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    private function buildSubForm(string $name, FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('registration', IntegerType::class,
                [
                    'required' => true,
                ]
            )
            ->add('registration_year', IntegerType::class,
                [
                    'required' => true,
                ]
            )
        ;
    }
}
