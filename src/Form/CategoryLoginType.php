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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class WikiTreeLoginType
 * @selectPure App\Form
 * @author  Craig Rayner <craig@craigrayner.com>
 * 19/12/2021 07:38
 */
class CategoryLoginType extends CategoryType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['show_login'] = true;

        parent::buildForm($builder, $options);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'wikitreecategory';
    }
}
