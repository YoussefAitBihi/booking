<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AdType;
use App\Service\Uploader;
use App\Repository\AdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{

    /**
     * Create new ad
     * 
     * @Route("/ad/new", name="ad_create", methods={"GET", "POST"})
     *
     * @param Request $request
     * @param Uploader $uploader
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function create(
        Request $request, 
        Uploader $uploader,
        EntityManagerInterface $entityManager
    ): Response
    {
        // Create a new Ad
        $ad = new Ad();

        // Creating form object and handling request
        $form = $this
                    ->createForm(AdType::class, $ad)
                    ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload images and add them to the form
            foreach ($form->get('images')->getData() as $key => $element) {

                /** @var UploadedFile $img contains image that already uploaded */
                $image = $form->get('images')[$key]['image']->getData();

                $newFilename = $uploader
                                    ->upload(
                                        $image, 
                                        $this->getParameter('slider_directory')
                                    );

                $element->setImage($newFilename);
            }

            /** @var UploadedFile $thumbnail contains thumbnail that already uploaded */
            $thumbnail = $form->get('thumbnail')->getData();

            // New file name
            $newFilename = $uploader
                                ->upload(
                                    $thumbnail, 
                                    $this->getParameter('thumbnails_directory')
                                );

            // Set Thumbnail
            $ad->setThumbnail($newFilename);

            $entityManager->persist($ad);
            $entityManager->flush();

            // Add flash messages
            $this->addFlash(
                'success',
                "Votre annonce dont le titre est <strong>{$ad->getTitle()}</strong> a été bien créée"
            );

            return $this->redirectToRoute('ad_show', [
                'id'    => $ad->getId(),
                'slug'  => $ad->getSlug()
            ], 301);
        }

        return $this->render('ad/new.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    /**
     * Show an ad via Slug and Id
     * 
     * @Route("/ad/{id}-{slug}", name="ad_show", methods={"GET"})
     *
     * @param Ad $ad
     * @return Response
     */
    public function show(Ad $ad): Response
    {        
        return $this->render('ad/show.html.twig', [
            'ad' => $ad
        ]);
    }

    /**
     * Retrieve ad via his slug and id and edit it 
     * 
     * @Route("/ad/{id}-{slug}/edit", name="ad_edit")
     *
     * @param Ad $ad
     * @param Request $request
     * @param Uploader $uploader 
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function edit(
        Ad $ad,
        Request $request,
        Uploader $uploader,
        EntityManagerInterface $entityManager
    ): Response 
    {

        // Creating form object and handling the coming request
        $form = $this
                    ->createForm(AdType::class, $ad)
                    ->handleRequest($request);

        // Create a back up of Originals slider
        $slider = new ArrayCollection();

        // Add images of slider to slider variable
        foreach ($ad->getImages() as $image) {
            $slider->add($image->getImage());
        }

        if ($form->isSubmitted() && $form->isValid()) {

            // Remove image of slider
            foreach ($ad->getImages() as $image) {
                if (!$slider->contains($image->getImage())) {
                    $dir_sliders = $this->getParameter('slider_directory') . "/public/uploads/sliders";
                    unlink($dir_sliders . DIRECTORY_SEPARATOR . $image->getImage());
                    $entityManager->remove($image);
                }
            }

            // Images Slider
            foreach ($form->get('images')->getData() as $key => $element) {
                
                /** @var UploadedFile|null $image contains either a null value or an UploadedFile type */
                $image = $form->get('images')[$key]['image']->getData();
    
                if ($image instanceof UploadedFile) {
                    
                    $newFilename = $uploader
                                        ->upload(
                                            $image,
                                            $this->getParameter('slider_directory')
                                        );
    
                    $element->setImage($newFilename);
                }
            }

            /** @var UploadedFile|null $thumbnail contains either a null value or an UploadedFile Type */
            $thumbnail = $form->get('thumbnail')->getData();

            if ($thumbnail instanceof UploadedFile) {
                $newFilename = $uploader->upload(
                                            $thumbnail,
                                            $this->getParameter('thumbnail_directory')
                );

                $ad->setThumbnail($newFilename);
            }

            $entityManager->persist($ad);
            $entityManager->flush();

            $this->addFlash(
                'success',
                "Vous avez été modifié votre annonce <strong><em>{$ad->getTitle()}</em></strong> avec succès"
            );

            return $this->redirectToRoute("ad_show", [
                'id'    => $ad->getId(),
                'slug'  => $ad->getSlug()
            ], 301);

        }

        return $this->render('ad/edit.html.twig', [
            'ad'    => $ad,
            'form'  => $form->createView()
        ]);
    }

    /**
     * Allow to delete an ad via id and slug
     * 
     * @Route("/ad/{id}-{slug}/delete", name="ad_delete")
     *
     * @param Ad $ad
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function delete(
        Ad $ad, 
        EntityManagerInterface $entityManager
    ): RedirectResponse 
    {
        // Deleting the thumbnail
        $thumbnail = $ad->getThumbnail();

        // Array contains all thumbnails
        $thumbnails = scandir($this->getParameter('thumbnail_directory'));

        if (!empty($thumbnails) && in_array($thumbnail, $thumbnails)) {
            if (is_file($this->getParameter('thumbnail_directory') . DIRECTORY_SEPARATOR . $thumbnail)) {
                unlink($this->getParameter('thumbnail_directory') . DIRECTORY_SEPARATOR . $thumbnail);
            }
        }
        
        // Deleting All images
        $images = scandir($this->getParameter('slider_directory'));

        if (!empty($images)) {
            foreach ($ad->getImages() as $image) {
                if (in_array($image->getImage(), $images)) {
                    unlink($this->getParameter('slider_directory') . DIRECTORY_SEPARATOR . $image->getImage());
                }
            }
        }

        $entityManager->remove($ad);
        $entityManager->flush();

        $this->addFlash(
            'success',
            "Vous avez supprimé cette annonce avec succès"
        );

        return $this->redirectToRoute("ads_index", [], 301);
    }

    /**
     * Show all ads
     * 
     * @Route("/ads", name="ads_index", methods={"GET"})
     *
     * @param AdRepository $repo 
     * @return Response
     */
    public function index(AdRepository $repo): Response
    {   
        /** @var Ad[] $ads contains all ads */
        $ads = $repo->findAll();
    
        return $this->render('ad/index.html.twig', [
            'ads' => $ads
        ]);
    }

}
