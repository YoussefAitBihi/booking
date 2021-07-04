<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Entity\Comment;
use App\Form\BookingType;
use App\Form\CommentType;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookingController extends AbstractController
{
    /**
     * Get All user's booking
     * 
     * @Route("/booking/all", name="booking_all", methods={"GET"})
     * @IsGranted("ROLE_USER")
     * 
     * @param BookingRepository $bookingRepository
     * @return Response
     */
    public function index(BookingRepository $bookingRepository): Response
    {
        /** @var Booking[] $bookings contains all user's booking */
        $bookings = $bookingRepository->findBy([
            'booker' => $this->getUser()
        ]);
        
        return $this->render('booking/index.html.twig', [
            'bookings' => $bookings
        ]);
    }

    /**
     * Permet à l'utilisateur de réserver l'annonce demandée
     * 
     * @Route("/ad/{id}-{slug}/book", name="booking_book", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER")
     * 
     * @param Ad $ad
     * @param Request $request
     * @param EntityManagerInterface $manager 
     * @return Response
     */
    public function book(
        Ad $ad,
        Request $request,
        EntityManagerInterface $manager 
    ): Response
    {
        $booking = new Booking();

        $form = $this
                    ->createForm(BookingType::class, $booking)
                    ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $booking
                ->setBooker($this->getUser())
                ->setAd($ad);

            if ($booking->isBookableAd()) {
                $manager->persist($booking);
                $manager->flush();
    
                $this->addFlash(
                    "success",
                    "Vous avez été réservé le logement dans le titre est <strong>{$ad->getTitle()}</strong> avec succès"
                );
    
                return $this->redirectToRoute("booking_show", [
                    'id' => $booking->getId()
                ], 301);
            } else {
                $this->addFlash(
                    "warning",
                    "Vous ne pouvez malheureusement pas réserver dans ces dates sélectionnées, veuillez donc les modifier"
                );
            }

        }

        return $this->render('booking/book.html.twig', [
            'form' => $form->createView(),
            'ad' => $ad
        ]);
    }

    /**
     * Show a book via its id
     * 
     * @Route("/booking/{id}", name="booking_show", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_USER') and user == booking.getBooker()", message="Vous ne pouvez pas voir cette réservation, car il ne vous appartient pas")
     *
     * @param Booking $booking
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function show(
        Booking $booking, 
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $comment = new Comment();

        $form = $this
                    ->createForm(CommentType::class, $comment)
                    ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment
                ->setAuthor($this->getUser())
                ->setAd($booking->getAd());

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash(
                "success",
                "Vous avez été ajouté un commentaire avec succès" 
            );

            return $this->redirectToRoute("booking_show", [
                'id' => $booking->getId()
            ], 301);
        }

        return $this->render("/booking/show.html.twig", [
            'booking' => $booking,
            'form' => $form->createView()
        ]);
    } 
    
}
