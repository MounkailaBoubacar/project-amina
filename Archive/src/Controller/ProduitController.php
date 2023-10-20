<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/produit')]
class ProduitController extends AbstractController
{

    #[Route('/my_products', name: 'my_products')]
    public function myProducts(Security $security,ProduitRepository $produitRepository)
    {
        $user = $security->getUser(); // Récupérer l'utilisateur connecté

        if ($user) {
            $products = $user->getProduits(); // Récupérer les produits liés à l'utilisateur
        } else {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir vos produits.');
        }

        $produitsUtilisateur = [];
        foreach ($this->getUser()->getProduits() as $produit) {
            $produitsUtilisateur[] = $produit->getId();
        }
        return $this->render('produit/index.html.twig', [
            'produits' => $products,
            'show' => false,
            'produitsUtilisateur' => $produitsUtilisateur,

        ]);
    }
    #[Route('/ajouter-produit/{id}', name: 'ajouter_produit')]
    public function ajouterProduit(Produit $product): Response
    {
        $user = $this->getUser();

        $user->addProduit($product);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_produit_index');
    }

    #[Route('/retirer-produit/{id}', name: 'retirer_produit')]
    public function retirerProduit(Produit $product): Response
    {
        $user = $this->getUser();

        $user->removeProduit($product);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('my_products');
    }

    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        $produitsUtilisateur = [];
        foreach ($this->getUser()->getProduits() as $produit) {
            $produitsUtilisateur[] = $produit->getId();
        }
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
            'show' => true,
            'produitsUtilisateur' => $produitsUtilisateur,

        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
