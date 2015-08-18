<?php

namespace Core\GoogleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Core\GoogleBundle\Entity\GoogleHelper;

class CategoryCategoryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('googleCategory', 'choice', array(
                'choices' =>  GoogleHelper::getCategories($options['locale']),
                'required'    => true,
                'label'       => 'Google',  
                'empty_value' => 'Choose category',
                'empty_data'  => null)
            )
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Core\GoogleBundle\Entity\CategoryCategory',
            'locale' => 'sk'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'core_googlebundle_categorycategory';
    }
}
