<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Form\AccountType;
use App\Service\Uploader;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountController extends AbstractController
{
    
    /**
     * Allow to create an account
     * 
     * @Route("/account/register", name="account_register")
     * @IsGranted("IS_ANONYMOUS")
     *
     * @param Request $request
     * @param Uploader $uploader
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        Uploader $uploader,
        EntityManagerInterface $manager
    ): Response
    {
        // new user
        $user = new User();

        // Create form object and handle it
        $form = $this
                    ->createForm(AccountType::class, $user)
                    ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Password encoding

            $password = $user->getPassword();
            $encodedPassword = $encoder->encodePassword(
                $user,
                $password
            );

            $user->setPassword($encodedPassword);

            /** @var UploadedFile $avatar contains the user's picture */
            $avatar = $form->get('avatar')->getData();

            $newFilename = $uploader->upload(
                                        $avatar,
                                        $this->getParameter('avatars_directory')
            );

            $user->setAvatar($newFilename);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                "success",
                "Votre compte a été bien crée avec succès, veuillez donc vous connecter"
            );

            return $this->redirectToRoute("account_login");
        }

        return $this->render("/account/new.html.twig", [
            'form' => $form->createView()
        ]);
    }
    
    /**
     * Allow to make the connection
     * 
     * @Route("/account/login", name="account_login")
     * @IsGranted("IS_ANONYMOUS")
     * 
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // The last user name
        $lastUsername = $authenticationUtils->getLastUsername();

        // All errors
        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('account/login.html.twig', [
            'lastUsername' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * Allow to logout an user
     * 
     * @Route("/account/logout", name="account_logout")
     *
     * @return void
     */
    public function logout(): void
    {
        throw new Exception("N'oubliez pas de bien définir le paramètre logout dans le fichier security.yaml");
    }

}
