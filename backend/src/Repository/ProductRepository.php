<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ProductRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(Product::class));
    }

    public function findByExternalCode(string $externalCode): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.externalCode = :code')
            ->setParameter('code', $externalCode)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPaginated(
        int $page,
        int $limit,
        ?string $search = null,
        ?float $priceMin = null,
        ?float $priceMax = null
    ): array {
        // Base query for counting (without JOINs to avoid duplicates)
        $countQb = $this->createQueryBuilder('p');

        if ($search !== null && $search !== '') {
            $countQb->andWhere('p.name LIKE :search OR p.externalCode LIKE :search OR p.sku LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($priceMin !== null) {
            $countQb->andWhere('p.price >= :priceMin')
                ->setParameter('priceMin', $priceMin);
        }

        if ($priceMax !== null) {
            $countQb->andWhere('p.price <= :priceMax')
                ->setParameter('priceMax', $priceMax);
        }

        $countQb->select('COUNT(p.id)');
        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        // Main query with JOINs for fetching data
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.attributes', 'a')
            ->leftJoin('p.images', 'i')
            ->addSelect('a', 'i')
            ->orderBy('p.createdAt', 'DESC');

        if ($search !== null && $search !== '') {
            $qb->andWhere('p.name LIKE :search OR p.externalCode LIKE :search OR p.sku LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($priceMin !== null) {
            $qb->andWhere('p.price >= :priceMin')
                ->setParameter('priceMin', $priceMin);
        }

        if ($priceMax !== null) {
            $qb->andWhere('p.price <= :priceMax')
                ->setParameter('priceMax', $priceMax);
        }

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => (int) ceil($total / $limit),
        ];
    }

    public function findWithRelations(string $id): ?Product
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.attributes', 'a')
            ->leftJoin('p.images', 'i')
            ->addSelect('a', 'i')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}