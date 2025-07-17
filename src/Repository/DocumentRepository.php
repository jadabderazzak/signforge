<?php

namespace App\Repository;

use App\Entity\Document;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 *
 * Repository class for handling document-related queries.
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    /**
     * Returns the most recent documents for a specific user.
     *
     * @param int $maxResults Number of documents to return
     * @param User $user The current user
     * @return Document[]
     */
    public function findRecentDocuments(int $maxResults, User $user): array
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.client', 'c')
            ->leftJoin('d.type', 't')
            ->addSelect('c', 't')
            ->andWhere('d.user = :user')
            ->setParameter('user', $user)
            ->orderBy('d.createdAt', 'DESC')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult();
    }

    

    /**
     * Returns the total revenue of type ID = 1 for the given user.
     *
     * @param User $user The current user
     * @return float
     */
    public function getTotalRevenue(User $user): float
    {
        $result = $this->createQueryBuilder('d')
            ->select('SUM(d.total) as total')
            ->leftJoin('d.type', 'c')
            ->where('c.id = 1')
            ->leftJoin('d.user', 'u')
            ->andWhere('u.id = :user')
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getSingleScalarResult();
        
        return $result ? (float)$result : 0;
    }

    public function getCountByType(User $user): array
{
    return $this->createQueryBuilder('d')
        ->select('t.label AS type, COUNT(d.id) AS count')
        ->join('d.type', 't')
        ->where('d.user = :user')
        ->setParameter('user', $user)
        ->groupBy('t.id')
        ->getQuery()
        ->getResult();
}

    /**
     * Returns the monthly revenue for the last 12 months (type_id = 1) for the given user.
     *
     * @param User $user The current user
     * @return array
     */
    public function getMonthlyRevenue(User $user): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(total) AS total
            FROM document
            WHERE type_id = 1 AND user_id = :userId
            GROUP BY month
            ORDER BY month DESC
            LIMIT 12
        ";

        return $conn->executeQuery($sql, ['userId' => $user->getId()])->fetchAllAssociative();
    }
}
