<?php 

namespace App\Service;

use Exception;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class Uploader 
{
   /**
    * @var SluggerInterface $slugger
    */
   private SluggerInterface $slugger;

   public function __construct(SluggerInterface $slugger)
   {
      $this->slugger = $slugger;
   }

   /**
    * Upload a file on the server and return its filename
    *
    * @param UploadedFile $file
    * @param string $directory
    * @return string
    */
   public function upload(
      UploadedFile $file, 
      string $directory
   ): string 
   {  
      if (!$file instanceof UploadedFile) {
         throw new Exception("Format inattendu, essayer de passer un paramètre dont le type-hinting est UploadedFile");
      }
      
      // Original Filename
      $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
      
      // Safe Filename
      $safeFilename = $this->slugger->slug($originalFilename);

      // New filename
      $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->getClientOriginalExtension();

      try {
         $file->move(
            $directory,
            $newFilename
         );
      } catch (FileException $e) {
         "Erreur suspendu lors du téléchargement de ce fichier la " . $e->getMessage();
      }

      return $newFilename;
   }

}
