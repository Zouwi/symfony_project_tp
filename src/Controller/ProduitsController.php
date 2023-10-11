<?php

namespace App\Controller;

use App\Entity\Poste;
use App\Entity\Produits;
use App\Entity\Seller;
use App\Repository\AimerRepository;
use App\Repository\ProduitsRepository;
use App\Repository\SellerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProduitsController extends AbstractController
{
    #[Route('/produits', name: 'app_produits')]
    public function index(ProduitsRepository $produitsRepository, SellerRepository $sellerRepository): Response
    {
        return $this->render('produits/index.html.twig', [
            'produits' => $produitsRepository->findAll(),
        ]);
    }

    #[Route('/produits/{id}', name: 'app_produit_detail', methods: ['GET', 'POST'])]
    public function show(Request $request, EntityManagerInterface $entityManager, ProduitsRepository $produitsRepository, Produits $produits): Response
    {
        return $this->render('produits/produit_detail.html.twig', [
            'produit' => $produits,
        ]);
    }
}
