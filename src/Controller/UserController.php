<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\User;
use App\Service\Uploader;
use App\Service\Paginator;
use App\Entity\PasswordUpdate;
use App\Repository\AdRepository;
use App\Repository\UserRepository;
use App\Form\AccountInfosEditionType;
use Symfony\Component\Form\FormError;
use App\Form\AccountAvatarEditionType;
use App\Form\AccountPasswordEditionType;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\AccountDescriptionEditionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{

    /**
     * Allow to a user to edit its profile
     *
     * @Route("/user/edit-profile", name="account_edit_infos")
     * @IsGranted("ROLE_USER")
     * 
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */
    public function editInfos(
        Request $request,
        EntityManagerInterface $manager
    ): Response
    {      
        // Get the user connected
        $user = $this->getUser();

        $form = $this
                    ->createForm(AccountInfosEditionType::class, $user)
                    ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                "Vos informations de base a été mis à jour avec succès. Veuillez donc vous reconnecter"
            );

            return $this->redirectToRoute("account_profile", [
                'slug' => $user->getSlug()
            ]);
        }

        return $this->render('user/edit_infos.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * Allow to edit user's avatar
     * 
     * @Route("/user/edit-avatar", name="account_edit_avatar")
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * @param Uploader $uploader
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function editAvatar(
        Request $request,
        Uploader $uploader,
        EntityManagerInterface $manager
    ): Response
    {
        $user = $this->getUser();

        // Create an object form
        $form = $this
                    ->createForm(AccountAvatarEditionType::class, $user)
                    ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $avatar contains user's avatar */
            $avatar = $form->get('avatar')->getData();

            if ($avatar instanceof UploadedFile) {
                $newFilename = $uploader->upload(
                    $avatar,
                    $this->getParameter('avatars_directory')
                );

                $user->setAvatar($newFilename);
            }
            
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                "success",
                "Vous avez été modifié votre photo de profil avec succès"
            );

            return $this->redirectToRoute("account_profile", [
                'slug' => $user->getSlug()
            ]);       
        }

        return $this->render("user/edit_avatar.html.twig", [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * Allow to edit user's description
     * 
     * @Route("/user/edit-description", name="account_edit_description")
     * @IsGranted("ROLE_USER")
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function editDescription(
        Request $request,
        EntityManagerInterface $manager
    ): Response
    {
        $user = $this->getUser();

        $form = $this
                    ->createForm(AccountDescriptionEditionType::class, $user)
                    ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                "success",
                "Vous avez été modifié votre <strong>description</strong> de profil avec succès"
            );

            return $this->redirectToRoute("account_profile", [
                'slug' => $user->getSlug()
            ]);
        }

        return $this->render("/user/edit_description.html.twig", [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * Allow to edit user's password
     * 
     * @Route("/user/edit-password", name="account_edit_password")
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function editPassword(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        EntityManagerInterface $manager
    )
    {
        // Connected user
        $user = $this->getUser();

        $password = new PasswordUpdate();

        // Create Form object and handle request
        $form = $this
                    ->createForm(AccountPasswordEditionType::class, $password)
                    ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordVerify = password_verify(
                                $password->getOldPassword(),
                                $user->getPassword()
            );

            if (!$passwordVerify) {
                $form
                    ->get('oldPassword')
                    ->addError(new FormError("Veuillez saisir correctment le mot de passe acutel"));
            } else {
                $newPassword = $password->getNewPassword();
                $encodedNewPassword = $encoder->encodePassword($user, $newPassword);
    
                $user->setPassword($encodedNewPassword);
    
                $manager->persist($user);
                $manager->flush();
    
                $this->addFlash(
                    'success',
                    "Vous avez été modifié votre <strong>mot de passe</strong> avec succès. Donc veuillez vous reconnecter"
                );
    
                return $this->redirectToRoute("account_login");
            }
            
        }

        return $this->render("user/edit_password.html.twig", [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * Allow to show the user's profile
     * 
     * @Route("/user/{slug}/{page<\d+>?1}", name="account_profile")
     * 
     * @param User $user
     * @param UserRepository $userRepository
     * @param Paginator $paginator
     * @param int $page
     * @return Response
     */
    public function profile(
        User $user,
        UserRepository $userRepository,
        Paginator $paginator,
        int $page
    ): Response
    {
        $paginator
            ->setEntityClassName(Ad::class)
            ->setOwner($user)
            ->setLimit(2)
            ->setCurrentPage($page);

        // All counters
        $counters  = $userRepository->getAllCounts($user->getId());

        return $this->render("user/profile.html.twig", [
            'user' => $user,
            'paginator' => $paginator,
            'commentsTotal' => $counters['commentsTotal'],               
            'bookingsTotal' => $counters['bookingsTotal'],
        ]);
    }
}
