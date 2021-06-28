<?php

namespace App\Form;

use App\Entity\User;
use App\Form\FormHelperType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountDescriptionEditionType extends AbstractType
{
    
    use FormHelperType;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'description',
                CKEditorType::class, [
                    'config' => [
                        'editorplaceholder' => 'Une superbe description qui représente votre profile'
                    ]
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
