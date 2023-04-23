<?php
namespace App\Form;

use App\Entity\Category;
use App\Entity\CategoryWebPage;
use App\Entity\Enum\CategoryWebPageEnum;
use App\Entity\Enum\CemeteryWebPageEnum;
use App\Entity\Enum\LocationWebPageEnum;
use App\Form\DataTransformer\ChoiceToValueTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryWebPageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->getDefinedType($builder, $options['category_class']);
        $builder
            ->add('key', TextType::class,
                [
                    'label' => 'Unique Identifier',
                    'required' => false,
                ]
            )
            ->add('name', TextType::class,
                [
                    'label' => 'Web Page Name',
                    'required' => false,
                ]
            )
            ->add('prompt', TextType::class,
                [
                    'label' => 'Web Page Text / Prompt',
                    'required' => false,
                ]
            )
            ->add('url', TextType::class,
                [
                    'label' => 'Web Address (URL)',
                    'required' => false,
                ]
            )
            ->add('category', HiddenType::class)
            ->add('id', HiddenType::class)
        ;
        $builder->get('definedType')->addModelTransformer(new ChoiceToValueTransformer());
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'webpage';
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'class' => CategoryWebPage::class,
                'choice_value' => 'id',
                'data_class' => CategoryWebPage::class,
            ]
        );
        $resolver->setRequired('category_class');
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Category $category
     * @return void
     */
    public function getDefinedType(FormBuilderInterface $builder, string $categoryClass): void
    {
        switch (basename($categoryClass)) {
            case 'Cemetery':
                $builder->add('definedType', EnumType::class,
                    [
                        'label' => 'Defined Web Pages',
                        'class' => CemeteryWebPageEnum::class,
                        'required' => false,
                        'choice_label' => fn ($choice) => match ($choice) {
                            CemeteryWebPageEnum::NotUsed => 'webpage.category.notused',
                            CemeteryWebPageEnum::ACI => 'webpage.cemetery.aci',
                            CemeteryWebPageEnum::CWGC => 'webpage.cemetery.cwgc',
                            CemeteryWebPageEnum::FaG => 'webpage.cemetery.fag',
                            CemeteryWebPageEnum::Wikipedia => 'webpage.category.wikipedia',
                            CemeteryWebPageEnum::BillionGraves => 'webpage.cemetery.billiongraves',
                        },
                    ]
                );
                break;
            case 'Location':
                $builder->add('definedType', EnumType::class,
                    [
                        'label' => 'Defined Web Pages',
                        'class' => LocationWebPageEnum::class,
                        'required' => false,
                        'choice_label' => fn ($choice) => match ($choice) {
                            LocationWebPageEnum::NotUsed => 'webpage.category.notused',
                            LocationWebPageEnum::Wikipedia => 'webpage.category.wikipedia',
                        },
                    ]
                );
                break;
            case 'Category':
            case 'Collection':
                $builder->add('definedType', EnumType::class,
                    [
                        'label' => 'Defined Web Pages',
                        'class' => CategoryWebPageEnum::class,
                        'required' => false,
                        'choice_label' => fn ($choice) => match ($choice) {
                            CategoryWebPageEnum::NotUsed => 'webpage.category.notused',
                            CategoryWebPageEnum::Wikipedia => 'webpage.category.wikipedia',
                        },
                    ]
                );
                break;
            default:
                dd($categoryClass);
        }
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     * @return void
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $rules = [];
        
        $view->vars['rules'] = $rules;
    }

}