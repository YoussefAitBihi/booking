<?php

namespace App\Controller;

use App\Repository\BookingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * La page d'accueil
     * 
     * @Route("/", name="homepage")
     * 
     * @return Response
     */
    public function index(BookingRepository $bookingRepository): Response
    {
        /** @var Booking[] $bookings contenant des réservations sont déjà pris par l'utilisateur connecté */
        $bookings = $bookingRepository
                        ->findBy(
                            ['booker' => $this->getUser()], 
                            ['startDate' => 'DESC'], 
                            6
                        );

        return $this->render('home/index.html.twig', [
            'bookings' => $bookings
        ]);
    }
}
