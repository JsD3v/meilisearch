<?php

namespace App\Factory;

use App\Entity\OrderItems;
use App\Factory\ProductFactory;
use App\Factory\OrderFactory;
use Faker\Factory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<OrderItems>
 */
final class OrderItemsFactory extends PersistentProxyObjectFactory
{
    public function __construct()
    {
        // Configuration de Faker en français
        $faker = Factory::create('fr_FR');
        self::faker($faker);
    }

    public static function class(): string
    {
        return OrderItems::class;
    }

    protected function defaults(): array|callable
    {
        $unitPrice = self::faker()->randomFloat(2, 5, 500);
        $quantity = rand(1, 5);

        return [
            'unitPrice' => number_format($unitPrice, 2, '.', ''),
            'totalPrice' => number_format($unitPrice * $quantity, 2, '.', ''),
            'orderData' => OrderFactory::new(),
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}