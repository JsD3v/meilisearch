<?php

namespace App\DataFixtures;

use App\Factory\ArticleFactory;
use App\Factory\CategoryFactory;
use App\Factory\OrderFactory;
use App\Factory\OrderItemsFactory;
use App\Factory\ProductFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création des données de base
        CategoryFactory::createMany(10);
        UserFactory::createMany(120);
        ArticleFactory::createMany(50);
        // Création des produits avec leurs catégories
        ProductFactory::createMany(50, function() {
            return [
                'category' => CategoryFactory::random(),
            ];
        });

        // Création des commandes
        OrderFactory::createMany(20, function() {
            $order = [
                'customer' => UserFactory::random(),
            ];

            // Après la création de la commande, on va créer ses items
            $currentOrder = OrderFactory::new($order)->create();

            // Création de 1 à 5 items pour chaque commande
            $itemsCount = rand(1, 5);
            $totalAmount = 0;

            for ($i = 0; $i < $itemsCount; $i++) {
                $product = ProductFactory::random();
                $unitPrice = $product->getPrice();
                $totalPrice = $unitPrice;
                $totalAmount += $totalPrice;

                // Création de l'item
                OrderItemsFactory::new([
                    'orderData' => $currentOrder,
                    'product' => $product,
                    'unitPrice' => number_format($unitPrice, 2, '.', ''),
                    'totalPrice' => number_format($totalPrice, 2, '.', ''),
                ])->create();
            }

            // Mise à jour du montant total de la commande
            $currentOrder->setTotalAmount(number_format($totalAmount, 2, '.', ''));

            return $order;
        });
    }
}