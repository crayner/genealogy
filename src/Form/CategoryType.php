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
 * Date: 6/12/2022
 * Time: 09:05
 */

namespace App\Form;

use App\Manager\CategoryManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * CategoryType
 */
class CategoryType extends AbstractType
{
    /**
     * @var CategoryManager
     */
    private CategoryManager $manager;

    /**
     * @param CategoryManager $manager
     */
    public function __construct(CategoryManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return CategoryManager
     */
    public function getManager(): CategoryManager
    {
        return $this->manager;
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
                        'data' => 'craig@craigrayner.com',
                        'required' => true,
                    ]
                )
                ->add('wikiTreePassword', TextType::class,
                    [
                        'label' => 'WikiTree Password',
                        'data' => '7DQQGVovqS*41k9P',
                        'required' => true,
                    ]
                );
        } else {
            $builder
                ->add('wikiTreeUser', HiddenType::class)
                ->add('wikiTreePassword', HiddenType::class);
        }
        $builder
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Next Category',
                ]
            )
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'wikitree_category';
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('show_login', false);
        $resolver->setDefault('attr', ['id' => $this->getBlockPrefix()]);
    }

}