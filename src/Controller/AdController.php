<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AdType;
use App\Service\Uploader;
use App\Service\Paginator;
use App\Repository\AdRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{

    /**
     * Sert à créer une nouvelle annonce
     * 
     * @Route("/ad/new", name="ad_create", methods={"GET", "POST"})
     * 
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * @param Uploader $uploader
     * @param EntityManagerInterface $entityManager
     * 
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

        // L'utilisateur connecté
        $user = $this->getUser();

        // Création l'objet form and manipulation la request
        $form = $this
                    ->createForm(AdType::class, $ad)
                    ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload les images de carousel
            foreach ($form->get('images')->getData() as $key => $element) {

                /** @var UploadedFile $img contains image that already uploaded */
                $image = $form->get('images')[$key]['image']->getData();

                $newFilename = $uploader
                                    ->upload(
                                        $image, 
                                        $this->getParameter('sliders_directory')
                                    );

                $element->setImage($newFilename);
            }

            /** @var UploadedFile $thumbnail la minuature de l'annonce */
            $thumbnail = $form->get('thumbnail')->getData();

            // Nouveau filename
            $newFilename = $uploader
                                ->upload(
                                    $thumbnail, 
                                    $this->getParameter('thumbnails_directory')
                                );

            // Set la minuature et le propriétaire (owner) au sein de l'objet
            $ad
                ->setThumbnail($newFilename)
                ->setOwner($user);

            $entityManager->persist($ad);
            $entityManager->flush();

            // Ajouter le message flash de succès
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
     * Sert à récupérer l'annonce demandée
     * 
     * @Route("/ad/{id}-{slug}", name="ad_show", methods={"GET"})
     * 
     * @param Ad $ad
     * @param AdRepository $adRepository
     * @param UserRepository $userRepository
     * 
     * @return Response
     */
    public function show(
        Ad $ad,
        AdRepository $adRepository,
        UserRepository $userRepository
    ): Response
    {  
        // Le feedback (rating) de l'annonce
        $ratingAd = $adRepository->getRating($ad->getId());
        // La totalité 
        $counters = $userRepository->getAllCounts($ad->getOwner()->getId());
        // Top Ads
        $topAds = $adRepository->getTopAdsUser($ad->getOwner()->getId(), 2);

        return $this->render('ad/show.html.twig', [
            'ad'         => $ad,
            'ratingAd'   => $ratingAd,
            'commentsTotal' => $counters['commentsTotal'],
            'bookingsTotal' => $counters['bookingsTotal'],
            'topAds'         => $topAds
        ]);
    }

    /**
     * Retrieve ad via his slug and id and edit it 
     * 
     * @Route("/ad/{id}-{slug}/edit", name="ad_edit")
     * @Security("is_granted('ROLE_USER') and user === ad.getOwner()", message="vous ne pouvez pas modifier cette annonce, car il ne vous appartient pas")
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
                    $dir_sliders = $this->getParameter('sliders_directory') . "/public/uploads/sliders";
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
                                            $this->getParameter('sliders_directory')
                                        );
    
                    $element->setImage($newFilename);
                }
            }

            /** @var UploadedFile|null $thumbnail contains either a null value or an UploadedFile Type */
            $thumbnail = $form->get('thumbnail')->getData();

            if ($thumbnail instanceof UploadedFile) {
                $newFilename = $uploader->upload(
                                            $thumbnail,
                                            $this->getParameter('thumbnails_directory')
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
     * @Security("is_granted('ROLE_USER') and user === ad.getOwner()", message="Vous ne pouvez pas supprimer cette annonce, car il ne vous appartient pas")
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
        $thumbnails = scandir($this->getParameter('thumbnails_directory'));

        if (!empty($thumbnails) && in_array($thumbnail, $thumbnails)) {
            if (is_file($this->getParameter('thumbnails_directory') . DIRECTORY_SEPARATOR . $thumbnail)) {
                unlink($this->getParameter('thumbnails_directory') . DIRECTORY_SEPARATOR . $thumbnail);
            }
        }
        
        // Deleting All images
        $images = scandir($this->getParameter('sliders_directory'));

        if (!empty($images)) {
            foreach ($ad->getImages() as $image) {
                if (in_array($image->getImage(), $images)) {
                    unlink($this->getParameter('sliders_directory') . DIRECTORY_SEPARATOR . $image->getImage());
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
     * @Route("/ads/{page<\d+>?1}", name="ads_index", methods={"GET"})
     *
     * @param AdRepository $repo 
     * @param int $page
     * @param Paginator $paginator
     * @return Response
     */
    public function index(
        AdRepository $repo,
        int $page,
        Paginator $paginator
    ): Response
    {   
        
        $paginator
            ->setEntityClassName(Ad::class)
            ->setCurrentPage($page);
    
        return $this->render('ad/index.html.twig', [
            'paginator' => $paginator,
            'repo' => $repo
        ]);
    }

}
