<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Form\AccountType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AccountController extends AbstractController
{
    
    /**
     * Permet de faire la connexion
     * 
     * @Route("/account/login", name="account_login")
     * 
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function index(AuthenticationUtils $authenticationUtils): Response
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

    /**
     * Allow to create an account
     * 
     * @Route("/account/register", name="account_register")
     *
     * @return Response
     */
    public function create(Request $request): Response
    {
        // new user
        $user = new User();

        // Create form object and handle it
        $form = $this
                    ->createForm(AccountType::class, $user)
                    ->handleRequest($request);

        return $this->render("/account/new.html.twig", [
            'form' => $form->createView()
        ]);
    }
}
