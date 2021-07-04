<?php 

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FrenchDateToDateTimeTransformer implements DataTransformerInterface
{
   public function transform($dateTime) {
      if (is_null($dateTime)):
         return '';
      endif;
      
      return $dateTime->format('d/m/Y');
   }

   public function reverseTransform($frenchDate) {

      if (is_null($frenchDate)):
         // Exception
         throw new TransformationFailedException("Vous devez nous fournir une date");
      endif;

      $date = \DateTime::createFromFormat('d/m/Y', $frenchDate);

      if ($date === false):
         // Exception
         throw new TransformationFailedException("Nous n'arrivons plus à transformer la date que nous recevions en datetime, vous devez nous fournir une vrai date");
      endif;

      return $date;

   }
}