<?php

namespace App\Repository;

use PDO;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Get user's comment total
     *
     * @param int $id
     * @return integer
     */
    private function getCommentsTotal(int $id): int
    {
        $total = $this
                    ->createQueryBuilder('u')
                    ->select('COUNT(c) as total')
                    ->join('u.ads', 'a')
                    ->join('a.comments', 'c')
                    ->where('u.id = :user_id')
                    ->setParameter('user_id', $id, PDO::PARAM_INT)
                    ->getQuery()
                    ->getResult();

        return (int) $total[0]['total'];
    }

    /**
     * Get user's booking total
     *
     * @param integer $id
     * @return integer|null
     */
    private function getBookingsTotal(int $id): ?int
    {
        $total = $this
                    ->createQueryBuilder('u')
                    ->select('COUNT(b) as total')
                    ->join('u.ads', 'a')
                    ->join('a.bookings', 'b')
                    ->where('u.id = :user_id')
                    ->setParameter('user_id', $id, PDO::PARAM_INT)
                    ->getQuery()
                    ->getResult();

        return (int) $total[0]['total'];
    }

    /**
     * Calc and retrieve user's rating
     *
     * @param integer $id
     * @return integer
     */
    public function getRating(int $id): int
    {
        $rating = $this
                    ->createQueryBuilder('u')
                    ->select('AVG(c.rating) as rating')
                    ->join('u.ads', 'a')
                    ->join('a.comments', 'c')
                    ->where('u.id', ':user_id')
                    ->setParameter('user_id', $id, PDO::PARAM_INT)
                    ->getQuery()
                    ->getResult();

        return (int) $rating[0]['rating'];
    }

    /**
     * Get All counts
     *
     * @param integer $id
     * @return array
     */
    public function getAllCounts(int $id): array
    {
        // Total count comments
        $commentsTotal = $this->getCommentsTotal($id);
        // Total count comments
        $bookingsTotal = $this->getBookingsTotal($id);

        return [
            'commentsTotal' => $commentsTotal,
            'bookingsTotal' => $bookingsTotal,
        ];
    }

    public function getTopAdUser(int $userId, int $limit)
    {
        $topAdUser = $this
                        ->createQueryBuilder('a')
                        ->select('AVG(c.rating) as rating, a')
                        ->join('u.ads', 'a')
                        ->join('a.comments', 'c')
                        ->where('u.id = :user_id')
                        ->setParameter('user_id', $userId, PDO::PARAM_INT)
                        ->groupBy('a')
                        ->orderBy('rating', 'desc')
                        ->setMaxResults($limit)
                        ->getQuery()
                        ->getResult();

        return $topAdUser;
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
