<?php 

namespace App\Service;

use Exception;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class Uploader 
{
    /**
     * Le slugger
     * 
     * @var SluggerInterface $slugger
     */
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * Permet de mettre en ligne un fichier de type UploadedFile et de générer un nouveau nom pour le fichier
     *
     * @throws Exception si le paramètre $file n'est pas de type UploadedFile
     * 
     * @param UploadedFile $file
     * @param string $directory
     * 
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
        
        // Le nom orinigale
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        
        // Filename sécurisé
        $safeFilename = $this->slugger->slug($originalFilename);

        // Nouveau filename
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
