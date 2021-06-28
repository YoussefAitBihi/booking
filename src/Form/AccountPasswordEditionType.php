<?php

namespace App\Form;

use App\Form\FormHelperType;
use App\Entity\PasswordUpdate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class AccountPasswordEditionType extends AbstractType
{

    use FormHelperType;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'oldPassword',
                PasswordType::class,
                $this->setValuesOfAttributesForm(
                    'Mot de passe actuel',
                    "Tapez le mot de passe actuel"
                )
            )
            ->add(
                'newPassword',
                PasswordType::class,
                $this->setValuesOfAttributesForm(
                    'Nouveau mot de passe',
                    "Tapez un nouveau mot de passe"
                )
            )
            ->add(
                'confirmNewPassword',
                PasswordType::class,
                $this->setValuesOfAttributesForm(
                    'Confirmation du mot de passe',
                    "Confirmer correctement le mot de passe"
                )
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PasswordUpdate::class,
        ]);
    }
}
