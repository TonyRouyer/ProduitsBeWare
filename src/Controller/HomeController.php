<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    /**
     * @Route("/", name="home")
     */
    public function index(ProductRepository $productRepository)
    {   
        $product = $productRepository->findAll();

        return $this->render('index.html.twig', [
            'products' => $product,
        ]);
    }
    
}
