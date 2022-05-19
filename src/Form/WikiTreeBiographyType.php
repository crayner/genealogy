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
 * Date: 16/12/2021
 * Time: 12:40
 */

namespace App\Form;

use App\Manager\WikiTreeManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WikiTreeBiographyType
 * @selectPure App\Form
 * @author  Craig Rayner <craig@craigrayner.com>
 * 19/12/2021 07:38
 */
class WikiTreeBiographyType extends AbstractType
{
    /**
     * @var WikiTreeManager
     */
    private WikiTreeManager $manager;

    /**
     * @var array
     */
    private array $passedAwayJoiners;

    /**
     * @param WikiTreeManager $manager
     */
    public function __construct(WikiTreeManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return WikiTreeManager
     */
    public function getManager(): WikiTreeManager
    {
        return $this->manager;
    }

    /**
     * @return array
     */
    private function getCemeteryChoices(): array
    {
        $result = [];
        foreach ($this->getManager()->getCemeteries() as $q=>$w)
            $result[$q] = $q;
        ksort($result);

        return $result;
    }

    /**
     * @return array
     */
    private function getCongregationChoices(): array
    {
        $result = [];
        foreach ($this->getManager()->getCongregations() as $q=>$w)
            $result[$q] = $q;
        ksort($result);

        return $result;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['show_login']) {
            $builder
                ->add('wikiTreeUser', TextType::class,
                    [
                        'label' => 'WikiTree User name',
                        'help' => 'Usually an email address',
                        'required' => true,
                    ]
                )
                ->add('wikiTreePassword', TextType::class,
                    [
                        'label' => 'WikiTree Password',
                        'required' => true,
                    ]
                );
        } else {
            $builder
                ->add('wikiTreeUser', HiddenType::class,
                    [
                        'label' => 'WikiTree User name',
                        'help' => 'Usually an email address',
                        'required' => true,
                    ]
                )
                ->add('wikiTreePassword', HiddenType::class,
                    [
                        'label' => 'WikiTree Password',
                        'required' => true,
                    ]
                );
        }
        $builder->add('wikiTreeUserID', TextType::class,
                [
                    'label' => 'WikiTree User ID',
                    'help' => 'The WikiTree User ID for which you are creating a biography',
                    'required' => true,
                ]
            )
            ->add('interredCemetery', ChoiceType::class,
                [
                    'label' => 'Cemetery Name',
                    'help' => 'An entry here will add interment details to the biography.',
                    'choices' => $this->getCemeteryChoices(),
                    'required' => false,
                    'choice_translation_domain' => false,
                    'multiple' => true,
                ]
            )
            ->add('interredLocation', TextType::class,
                [
                    'label' => 'Location in Cemetery',
                    'help' => 'Additional information to find the grave in the cemetery.',
                    'required' => false,
                ]
            )
            ->add('passedAwayJoiner', ChoiceType::class,
                [
                    'label' => 'Passed away Joiner',
                    'help' => 'Wording used for connection of the place where this person passed away.',
                    'required' => false,
                    'placeholder' => 'in',
                    'choices' => $this->getPassedAwayJoiners(),
                ]
            )
            ->add('baptismDate', DateType::class,
                [
                    'label' => 'Date of Baptism',
                    'help' => 'Can be left blank.',
                    'required' => false,
                    'widget' => 'single_text',
                    'input' => 'datetime_immutable',
                ]
            )
            ->add('baptismLocation', TextType::class,
                [
                    'label' => 'Location of Baptism',
                    'help' => 'Can be left blank.',
                    'required' => false,
                ]
            )
            ->add('congregations', ChoiceType::class,
                [
                    'label' => 'Congregations',
                    'help' => 'Add one or more congregation categories to your record.',
                    'choices' => $this->getCongregationChoices(),
                    'required' => false,
                    'multiple' => true,
                    'choice_translation_domain' => false,
                    'placeholder' => 'No Congregation Selected',
                ]
            )
            ->add('locations', ChoiceType::class,
                [
                    'label' => 'Locations',
                    'help' => 'Add one or more location categories to your record.',
                    'choices' => $this->getLocationChoices(),
                    'required' => false,
                    'multiple' => true,
                    'choice_translation_domain' => false,
                    'placeholder' => 'No Location Selected',
                    'attr' => [
 //                       'rows' => 10,
 //                       'class' => 'multipleChoice',
                    ],
                ]
            )
            ->add('raynerPage', TextType::class,
                [
                    'label' => 'Rayner Book Page',
                    'help' => 'Where does this person appear in the Rayner book?',
                    'required' => false,
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Generate Biography',
                ]
            )
            ->add('reset', SubmitType::class,
                [
                    'label' => 'Reset Biography',
                ]
            )
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'wikitreebiography';
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('show_login', false);
    }

    /**
     * @return array
     */
    private function getLocations(): array {
        return $this->getManager()->getLocations();
    }

    private function getLocationChoices(): array
    {
        $result = [];
        foreach($this->getLocations() as $location) $result[$location] = $location;
        return $result;
    }

    /**
     * @return array
     */
    public function getPassedAwayJoiners(): array
    {
        if (isset($this->passedAwayJoiners)) return $this->passedAwayJoiners;
        return $this->passedAwayJoiners = $this->getManager()->getJoiners('passedAway');
    }
}
