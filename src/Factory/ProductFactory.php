<?php

namespace App\Factory;

use App\Entity\Product;
use Faker\Factory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Product>
 */
final class ProductFactory extends PersistentProxyObjectFactory
{
    public function __construct()
    {
        $faker = Factory::create('fr_FR');
        self::faker($faker);
    }

    public static function class(): string
    {
        return Product::class;
    }

    /**
     * Cette méthode définit les valeurs par défaut pour chaque propriété
     * en utilisant Faker configuré en français
     */
    protected function defaults(): array|callable
    {
        return [
            'Description' => self::faker()->paragraph(),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween('-1 year')),
            'name' => self::faker()->word(),
            'price' => self::faker()->randomFloat(2, 5, 1000),
            'image' => sprintf('https://picsum.photos/640/480?random=%d', self::faker()->unique()->randomNumber()),
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}
