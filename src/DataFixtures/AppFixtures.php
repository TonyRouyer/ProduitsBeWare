<?php

namespace App\DataFixtures;

use Faker\Factory;
use DateTimeImmutable;
use App\Entity\Product;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{


    public function load(ObjectManager $manager): void
    {
        // instancie Faker
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new \Liior\Faker\Prices($faker));
        $faker->addProvider(new \Bezhanov\Faker\Provider\Commerce($faker));


        for ($p = 0; $p < mt_rand(15, 20); $p++) {
            $date = new DateTimeImmutable();
            $product = new Product;
            $product
                ->setName($faker->productName())
                ->setPrice($faker->price(4000, 20000))
                ->setQuantity(mt_rand(3, 15))
                ->setDateCreation($date);
                

            $manager->persist($product);
        };
        
        $manager->flush();
    }
}