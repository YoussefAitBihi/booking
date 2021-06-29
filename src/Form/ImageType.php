<?php

namespace App\Form;

use App\Entity\Image;
use App\Form\FormHelperType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ImageType extends AbstractType
{
    use FormHelperType;
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'image',
                FileType::class,
                $this->setValuesOfAttributesFileForm(
                    '2M',
                    ['image/jpeg'],
                    "L'image doit faire au maximum 2MO",
                    "Veuillez s'il vous plait insérer une image de type JPEG ou JPG"
                )
            )
            ->add(
                'caption',
                TextType::class,
                $this->setValuesOfAttributesForm(
                    "La légende",
                    "Une superbe légende pour une belle image"
                )
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Image::class
        ]);
    }
}
