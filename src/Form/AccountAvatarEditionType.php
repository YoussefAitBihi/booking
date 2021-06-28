<?php

namespace App\Form;

use App\Entity\User;
use App\Form\FormHelperType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class AccountAvatarEditionType extends AbstractType
{
    use FormHelperType;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'avatar',
                FileType::class,
                $this->setValuesOfAttributesFileForm(
                    "2M",
                    ['image/jpeg'],
                    "La taille de la photo de profile doit faire au maximum 2MO, veuillez donc la changer",
                    "Veuillez insérer s'il vous plait une photo de profil de type JPEG ou JPG"
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
