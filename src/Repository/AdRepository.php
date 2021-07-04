<?php

namespace App\Repository;

use PDO;
use App\Entity\Ad;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Ad|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ad|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ad[]    findAll()
 * @method Ad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ad::class);
    }

    /**
     * Calc & retrieve ad rating
     *
     * @param integer $id
     * @return integer
     */
    public function getRating(int $id): int
    {
        $rating = $this
                    ->createQueryBuilder('a')
                    ->select('AVG(c.rating) as rating')
                    ->join('a.comments', 'c')
                    ->where('a.id = :ad_id')
                    ->setParameter('ad_id', $id, PDO::PARAM_INT)
                    ->getQuery()
                    ->getResult();

        return (int) $rating[0]['rating'];
    }

    /**
     * Get Top user's ads
     *
     * @param integer $id
     * @param integer $limit
     * @return array
     */
    public function getTopAdsUser(int $ownerId, int $limit): array
    {
        $topAdsUser = $this
                        ->createQueryBuilder('a')
                        ->select('a as ad, AVG(c.rating) as rating')
                        ->join('a.comments', 'c')
                        ->where('a.owner = :owner_id')
                        ->setParameter('owner_id', $ownerId, PDO::PARAM_INT)
                        ->groupBy('a')
                        ->orderBy('rating', 'DESC')
                        ->setMaxResults($limit)
                        ->getQuery()
                        ->getResult();

        return $topAdsUser;
    }

    // /**
    //  * @return Ad[] Returns an array of Ad objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ad
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
