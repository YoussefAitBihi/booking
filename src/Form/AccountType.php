<?php

namespace App\Form;

use App\Entity\User;
use App\Form\FormHelperType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class AccountType extends AbstractType
{
    use FormHelperType;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'firstName',
                TextType::class,
                $this->setValuesOfAttributesForm(
                    "Le prénom",
                    "Taper votre prénom"
                )
            )
            ->add(
                'lastName',
                TextType::class,
                $this->setValuesOfAttributesForm(
                    "Le nom",
                    "Taper votre nom"
                )
            )
            ->add(
                'email',
                EmailType::class,
                $this->setValuesOfAttributesForm(
                    "L'email",
                    "Taper votre email"
                )
            )
            ->add(
                'password',
                PasswordType::class,
                $this->setValuesOfAttributesForm(
                    "Le mot de passe",
                    "Taper un mot de passe valide"
                )
            )
            ->add(
                'passwordConfirm',
                PasswordType::class,
                $this->setValuesOfAttributesForm(
                    "Confimation du mot de passe",
                    "Confirmer correctement le mot de passe"
                )
            )
            ->add(
                'avatar',
                FileType::class,
                $this->setValuesOfAttributesFileForm(
                    "2M",
                    ['image/jpeg'],
                    "La taille de la photo de profile doit faire au maximum 2 méga, veuillez donc la changer",
                    "Veuillez insérer s'il vous plait une photo de profile de type JPEG ou JPG"
                )
            )
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
