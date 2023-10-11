<?php

namespace App\Controller;

//use App\Repository\SellerRepository;
//use Doctrine\ORM\EntityManagerInterface;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Repository\PosteRepository;
use App\Repository\SellerRepository;
use Doctrine\Entity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTimeImmutable;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(SellerRepository $sellerRepository, PosteRepository $posteRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'sellers' => $sellerRepository->findAll(),
            'poste' => $posteRepository->findAll(),
        ]);
    }

    #[Route('/booking/{id}/{week}', name: 'app_seller_booking', defaults: ["week" => null], methods: ['GET', 'POST'])]
    public function booking(SellerRepository $sellerRepository, $id, ?int $week, EntityManagerInterface $entityManager, Request $request, Security $security): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $seller = $sellerRepository->findOneBy(['id'=>$id]);
        $currentWeek = $week ?? date('W');
        $bookings = $seller->getBookings();

        $bookedSlots = [];
        foreach ($bookings as $booking) {
            if ($booking->getWeek() == $currentWeek) {
                $bookedSlots[$booking->getDay()][$booking->getTime()] = true;
            }
        }

        $user = $security -> getUser();

        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $day = $form->get('day')->getData();
            $time = $form->get('time')->getData();
            $booking->setDay($day)
                ->setTime($time)
                ->setClient($user)
                ->setSeller($seller)
                ->setWeek($currentWeek);
            $entityManager->persist($booking);
            $entityManager->flush();
            return $this->redirectToRoute('app_seller_booking', ['id'=>$seller->getId(), 'week=>$currentWeek']);
        }

        $duration = $seller->getDuration() ?? 30;
        $startTime = new \DateTimeImmutable('9:00');
        $endTime = new \DateTimeImmutable('18:00');

        $timeSlots = [];
        while ($startTime < $endTime) {
            $timeSlots[] = $startTime->format('h:i');
            $startTime = $startTime->modify("+$duration minutes");
        }

        $weekSlot = [
            'times' => $timeSlots,
            'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Sunday']];

        $dt = new \DateTimeImmutable();
        $dt = $dt->setISODate(date('Y'), $currentWeek);

        setlocale(LC_TIME, 'fr_FR', 'fra');

        $weekDates = [];
        for ($i = 0; $i < 5; $i++) {
            $date = new \DateTimeImmutable();
            $date = $date->setISODate((int)$date->format('o'), $currentWeek, $i + 1);
            $weekDates[] = strftime("%A %d/%m/%Y", $date->getTimestamp());
        }

        return $this->render('home/booking.html.twig', [
                'seller' => $seller,
                'bookings' => $bookings,
                'weekSlot' => $weekSlot,
                'bookedSlots' => $bookedSlots,
                'booking' => $booking,
                'form' => $form->createView(),
                'currentWeek' => $currentWeek,
                'weekDates' => $weekDates
        ]);
    }
}
