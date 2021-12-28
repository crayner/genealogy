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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class WikiTreeLoginType
 * @package App\Form
 * @author  Craig Rayner <craig@craigrayner.com>
 * 19/12/2021 07:38
 */
class WikiTreeLoginType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
            )
            ->add('wikiTreeUserID', TextType::class,
                [
                    'label' => 'WikiTree User ID',
                    'help' => 'The WikiTree User ID for which you are creating a biography',
                    'required' => true,
                ]
            )
            ->add('interredSite', TextType::class,
                [
                    'label' => 'Interred @',
                    'help' => 'An entry here will add interment details to the biography.',
                    'required' => false,
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Login & Generate Biography',
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
}
