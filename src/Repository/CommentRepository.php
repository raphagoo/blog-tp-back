<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    /**
     * @var PaginatorInterface
     */
    private $knp;

    /**
     * CommentRepository constructor.
     * @param ManagerRegistry $registry
     * @param PaginatorInterface $knp
     */
    public function __construct(ManagerRegistry $registry, PaginatorInterface $knp)
    {
        parent::__construct($registry, Comment::class);
        $this->knp = $knp;
    }

    /**
     * @param Request $request
     * @param null $searchTerm
     * @param null[] $statusTerm
     * @return PaginationInterface
     */
    public function listComments(Request $request, $searchTerm = null, $statusTerm = [null])
    {
        $entityManager = $this->getEntityManager();
        if(in_array(NULL, $statusTerm)){
            $query = $entityManager->createQuery(
                'SELECT c
            FROM App\Entity\Comment c
            LEFT JOIN c.article a
            LEFT JOIN c.author au
            WHERE a.title LIKE :searchTerm
            AND c.approved IN (:statusTerm) OR c.approved IS NULL'
            );
        }
        else{
            $query = $entityManager->createQuery(
                'SELECT c
            FROM App\Entity\Comment c
            LEFT JOIN c.article a
            LEFT JOIN c.author au
            WHERE a.title LIKE :searchTerm
            AND c.approved IN (:statusTerm)'
            );
        }

        $query->setParameter('searchTerm', '%'.$searchTerm.'%');
        $query->setParameter('statusTerm', $statusTerm);

        return $this->knp->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            5 /*limit per page*/
        );

    }

    // /**
    //  * @return Comment[] Returns an array of Comment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
