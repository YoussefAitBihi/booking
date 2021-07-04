<?php

namespace App\Form;

use App\Entity\Comment;
use App\Form\FormHelperType;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class CommentType extends AbstractType
{
    use FormHelperType;

    public function buildForm(
        FormBuilderInterface $builder, 
        array $options
    )
    {
        $builder
            ->add(
                'content',
                CKEditorType::class, [
                    'config' => [
                        'editorplaceholder' => "Un superbe commentaire si ça vous a plu"
                    ]
                ]
                
            )
            ->add(
                'rating',
                IntegerType::class, 
                $this->setValuesOfAttributesForm(
                    'Le feedback',
                    'Votre feedback', [
                        'min' => 1,
                        'max' => 5,
                        'step' => 1
                    ]
                )
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
