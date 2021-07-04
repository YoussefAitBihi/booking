<?php

namespace App\Form;

use App\Entity\Booking;
use App\Form\DataTransformer\FrenchDateToDateTimeTransformer;
use App\Form\FormHelperType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class BookingType extends AbstractType
{
    use FormHelperType;

    private FrenchDateToDateTimeTransformer $transformer;

    public function __construct(FrenchDateToDateTimeTransformer $transformer)
    {
        $this->transformer = $transformer;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'startDate',
                TextType::class,
                $this->setValuesOfAttributesForm(
                    "Date d'arrivée",
                    "La date d'arrivée",
                )
            )
            ->add(
                'endDate',
                TextType::class,
                $this->setValuesOfAttributesForm(
                    "Date de départ",
                    "La date de départ",
                )
            )
        ;

        $builder
            ->get('startDate')
            ->addModelTransformer($this->transformer);

        $builder
            ->get('endDate')
            ->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Booking::class
        ]);
    }
}
