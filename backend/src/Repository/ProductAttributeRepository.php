<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProductAttribute;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ProductAttributeRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(ProductAttribute::class));
    }

    public function findByProductAndKey(string $productId, string $key): ?ProductAttribute
    {
        return $this->createQueryBuilder('pa')
            ->andWhere('pa.product = :productId')
            ->andWhere('pa.key = :key')
            ->setParameter('productId', $productId)
            ->setParameter('key', $key)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByProduct(string $productId): array
    {
        return $this->createQueryBuilder('pa')
            ->andWhere('pa.product = :productId')
            ->setParameter('productId', $productId)
            ->orderBy('pa.key', 'ASC')
            ->getQuery()
            ->getResult();
    }
}