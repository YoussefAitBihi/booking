<?php

namespace App\Form;

use App\Entity\Ad;
use App\Form\ImageType;
use App\Form\FormHelperType;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AdType extends AbstractType
{
    use FormHelperType;
    
    public function buildForm(
        FormBuilderInterface $builder, 
        array $options
    )
    {
        $builder
            ->add(
                'title', 
                TextType::class, 
                $this->setValuesOfAttributesForm(
                    "Le titre", 
                    "Un beau titre pour un beau logement"
                )
            )
            ->add(
                'introduction', 
                TextType::class, 
                $this->setValuesOfAttributesForm(
                    "L'introduction",
                    "Une petite introduction pour votre logement"
                )
            )
            ->add(
                'description',
                CKEditorType::class, [
                    'config' => [
                        'editorplaceholder' => 'Taper une description intéressante pour avoir plus de la chance'
                    ]
                ]
            )
            ->add(
                'thumbnail',
                FileType::class,
                $this->setValuesOfAttributesFileForm(
                    '2M',
                    ['image/jpeg'],
                    "La taille de la miniature doit faire au maximum 2MO",
                    "Veuilez s'il vous plait insérer une miniature de type JPEG ou JPG"
                )
            )
            ->add(
                'city',
                ChoiceType::class, [
                    "choices" => [
                        "Casablanca" => "Casablanca", 
                        "Rabat" => "Rabat", 
                        "Fes" => "Fes", 
                        "Meknes" => "Meknes", 
                        "Tanger" => "Tanger", 
                        "Tetouane" => "Tetouane", 
                        "Oujda" => "Oujda", 
                        "Mohammedia" => "Mohammedia"
                    ]
                ]
            )
            ->add(
                'rooms',
                IntegerType::class,
                $this->setValuesOfAttributesForm(
                    'Les chambres', 
                    "Tapez le nombre de chambres de votre logement"
                )
            )
            ->add(
                'price',
                MoneyType::class, [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Le prix de votre de logement',
                        'currency' => 826
                    ]
                ]
            )
            ->add(
                'images',
                CollectionType::class, 
                [
                    'entry_type'    => ImageType::class,
                    'label'         => 'Veuillez insérer une image pour le vôtre',
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'by_reference'  => false
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ad::class,
        ]);
    }
}
