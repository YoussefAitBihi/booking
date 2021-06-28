<?php

namespace App\Form;

use App\Entity\User;
use App\Form\FormHelperType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class AccountInfosEditionType extends AbstractType
{
    use FormHelperType;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'firstName',
                TextType::class,
                $this->setValuesOfAttributesForm(
                    'Prénom',
                    "Modifier votre prénom"
                )
            )
            ->add(
                'lastName',
                TextType::class,
                $this->setValuesOfAttributesForm(
                    'Nom',
                    "Modifier votre nom"
                )
            )
            ->add(
                'email',
                EmailType::class,
                $this->setValuesOfAttributesForm(
                    'Email',
                    "Modifier votre email"
                )
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
