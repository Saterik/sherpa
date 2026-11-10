<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProductImage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ProductImageRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(ProductImage::class));
    }

    public function findByProduct(string $productId): array
    {
        return $this->createQueryBuilder('pi')
            ->andWhere('pi.product = :productId')
            ->setParameter('productId', $productId)
            ->orderBy('pi.sortOrder', 'ASC')
            ->addOrderBy('pi.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findMainImage(string $productId): ?ProductImage
    {
        return $this->createQueryBuilder('pi')
            ->andWhere('pi.product = :productId')
            ->andWhere('pi.isMain = true')
            ->setParameter('productId', $productId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}