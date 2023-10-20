<?php
namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Categorie;
use App\Entity\Produit;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // Créer 3 catégories
        $categories = [];
        for ($i = 1; $i <= 3; $i++) {
            $categorie = new Categorie();
            $categorie->setNom('Categorie ' . $i);
            $manager->persist($categorie);
            $categories[] = $categorie;
        }

        // Créer 10 produits
        for ($i = 1; $i <= 10; $i++) {
            $produit = new Produit();
            $produit->setNom('Produit ' . $i);
            $produit->setPrix(mt_rand(10, 100)); // Prix aléatoire pour l'exemple

            // Associer chaque produit à une catégorie de manière aléatoire
            $produit->setCategorie($categories[mt_rand(0, count($categories) - 1)]);
            $manager->persist($produit);
        }

        $manager->flush();
    }
}
