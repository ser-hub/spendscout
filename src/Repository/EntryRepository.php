<?php

namespace App\Repository;

use App\Entity\Entry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Entry>
 *
 * @method Entry|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entry|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entry[]    findAll()
 * @method Entry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entry::class);
    }

    /**
     * @return Entry[] Returns an array of Entry objects
     */
    public function findByFilters(
        $userId = null,
        $currencyId = null,
        $tagId = null,
        $dateFrom = null,
        $dateTo = null
    ): array {
        $qb = $this->createQueryBuilder('e');

        if ($userId != null) {
            $qb->andWhere('e.user = :userId');
            $qb->setParameter('userId', $userId);
        }

        if ($currencyId != null) {
            $qb->andWhere('e.currency = :currencyId');
            $qb->setParameter('currencyId', $currencyId);
        }

        if ($tagId != null) {
            $qb->andWhere('e.tag = :tagId');
            $qb->setParameter('tagId', $tagId);
        }

        if ($dateFrom != null) {
            $qb->andWhere('e.date >= :dateFrom');
            $qb->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo != null) {
            $qb->andWhere('e.date <= :dateTo');
            $qb->setParameter('dateTo', $dateTo);
        }

        return $qb->getQuery()->execute();
    }

    //    /**
    //     * @return Entry[] Returns an array of Entry objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Entry
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
