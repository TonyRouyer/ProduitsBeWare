<?php

namespace App\Controller;

use Fpdf\Fpdf;
use App\Form\ProductEditType;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    protected $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @Route("/product/view/{id}", name="product_view")
     */    
    public function view($id, ProductRepository $productRepository)
     {
        $product = $productRepository->findOneBy([
            'id' => $id
        ]);

        if (!$product) {
            throw $this->createNotFoundException('Le produit demander n\'existe pas');
        }

        return $this->render('product/view.html.twig', [
            'product' => $product,

        ]);
    }

    /**
     * @Route("/product/edit/{id}", name="product_edit")
     */    
    public function edit($id, ProductRepository $productRepository, Request $request, EntityManagerInterface $em)
     {
        $product = $productRepository->find($id);

        $form = $this->createForm(ProductEditType::class, $product);

        $name = $product->getName();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', "Le produit $name à bien été modifié" );
            return $this->redirectToRoute('home');
        }

        $formView = $form->createView();

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'formView' => $formView
        ]);
    }

    /**
     * @Route("/product/decrement/{id}", name="product_decrement")
     */    
    public function decrement($id, ProductRepository $productRepository, EntityManagerInterface $em)
     {
        $product = $productRepository->find($id);


        $product->setQuantity(($product->getQuantity()-1));
        $name = $product->getName();

        if($product->getQuantity() <= 0){
            $email = new Email();
            $email
            ->from(new Address("contact@hotmail.fr", "test BeWare"))
            ->to("admin@hotmail.fr")
            ->text("La quantité de produit '$name' est de 0")
            ->subject("Quantité du produit $name");
            $this->mailer->send($email);
        }
        $em->flush();


        return $this->redirectToRoute('home');
    
    }

    /**
     * @Route("/product/augment/{id}", name="product_augment")
     */    
    public function augment($id, ProductRepository $productRepository, EntityManagerInterface $em)
     {
        $product = $productRepository->find($id);

        $form = $this->createForm(ProductEditType::class, $product);

        $product->setQuantity(($product->getQuantity()+1));
        $em->flush();

        
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/makepdf/{id}", name="product_make_pdf")
     */    
    public function makepdf($id, ProductRepository $productRepository)
     {
        $product = $productRepository->find($id);

        $name = $product->getName();
        $price = $product->getPrice();
        $quantity = $product->getQuantity();

        $pdf = new Fpdf();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(40,10, "nom : $name");
        $pdf->Ln(20);
        $pdf->Cell(40,10, "Prix : $price");
        $pdf->Ln(20);
        $pdf->Cell(40,10, "Quantité : $quantity");

        
        return new Response($pdf->Output(), 200, array(
            'Content-Type' => 'application/pdf'));
    }
    
}
