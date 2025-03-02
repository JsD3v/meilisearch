<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function search(?string $query): array
    {
        if (empty($query)) {
            return [];
        }

        return $this->createQueryBuilder('a')
            ->andWhere('a.title LIKE :query OR a.description LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}
