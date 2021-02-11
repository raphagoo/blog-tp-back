<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    /**
     * @var PaginatorInterface
     */
    private $knp;

    /**
     * ArticleRepository constructor.
     * @param ManagerRegistry $registry
     * @param PaginatorInterface $knp
     */
    public function __construct(ManagerRegistry $registry, PaginatorInterface $knp)
    {
        parent::__construct($registry, Article::class);
        $this->knp = $knp;
    }

    /**
     * @param Request $request
     * @param null $searchTerm
     * @param array $categoryTerm
     * @return PaginationInterface
     */
    public function listArticles(Request $request, $searchTerm = null, $categoryTerm = [])
    {
        $entityManager = $this->getEntityManager();
        if ($categoryTerm == []) {
            $query = $entityManager->createQuery(
                'SELECT a
            FROM App\Entity\Article a
            LEFT JOIN a.author au
            LEFT JOIN a.tag t
            WHERE a.title LIKE :searchTerm
            OR t.name LIKE :searchTerm'
            );
        } else {
            $query = $entityManager->createQuery(
                'SELECT a
            FROM App\Entity\Article a
            LEFT JOIN a.author au
            LEFT JOIN a.tag t
            WHERE a.title LIKE :searchTerm
            OR t.name LIKE :searchTerm
            AND a.category IN (:categoryTerm)'
            );
            $query->setParameter('categoryTerm', $categoryTerm);
        }

        $query->setParameter('searchTerm', '%' . $searchTerm . '%');

        return $this->knp->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            5 /*limit per page*/
        );

    }

    /**
     * @param $idArticle
     * @return Article[]
     */
    public function findRecents($idArticle): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT a
            FROM App\Entity\Article a
            WHERE a.id != :idArticle
            ORDER BY a.publicationDate DESC'
        )
            ->setParameter('idArticle', $idArticle)
            ->setMaxResults(4);

        // returns an array of Article objects
        return $query->getResult();
    }

    /**
     * @return Article[]
     */
    public function findRecentsBack(): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT a
            FROM App\Entity\Article a
            ORDER BY a.publicationDate DESC'
        )
            ->setMaxResults(5);

        // returns an array of Article objects
        return $query->getResult();
    }

    /**
     * @param UserInterface $user
     * @param Request $request
     * @param null $searchTerm
     * @param array $categoryTerm
     * @return PaginationInterface Returns an array of Article objects
     */
    public function findLikedArticles(UserInterface $user, Request $request, $searchTerm = null, $categoryTerm = [])
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.author', 'au')
            ->leftJoin('a.likes', 'l')
            ->andWhere('l.author = :user')
            ->setParameter('user', $user);
        if ($categoryTerm == []) {
            $qb->andWhere($qb->expr()->like('a.title', ':searchTerm'));
        } else {
            $qb->andWhere($qb->expr()->like('a.title', ':searchTerm'))
                ->andWhere("a.category IN(:categoryTerm)")
                ->setParameter('categoryTerm', $categoryTerm);
        }
        $query = $qb->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->getQuery();

        return $this->knp->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            5 /*limit per page*/
        );
    }

    /**
     * @param UserInterface $user
     * @return Article[] Returns an array of Article objects
     */
    public function findLastLikedArticles(UserInterface $user)
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.likes', 'l')
            ->andWhere('l.author = :user')
            ->setParameter('user', $user)
            ->orderBy('l.dateLike', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param UserInterface $user
     * @param Request $request
     * @param null $searchTerm
     * @param array $categoryTerm
     * @return PaginationInterface Returns an array of Article objects
     */
    public function findSharedArticles(UserInterface $user, Request $request, $searchTerm = null, $categoryTerm = [])
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.author', 'au')
            ->leftJoin('a.shares', 's')
            ->andWhere('s.author = :user')
            ->setParameter('user', $user);
        if ($categoryTerm == []) {
            $qb->andWhere($qb->expr()->like('a.title', ':searchTerm'));
        } else {
            $qb->andWhere($qb->expr()->like('a.title', ':searchTerm'))
                ->andWhere("a.category IN(:categoryTerm)")
                ->setParameter('categoryTerm', $categoryTerm);
        }
        $query = $qb->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->getQuery();

        return $this->knp->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            5 /*limit per page*/
        );
    }

    /**
     * @param UserInterface $user
     * @return Article[] Returns an array of Article objects
     */
    public function findLastSharedArticles(UserInterface $user)
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.shares', 's')
            ->andWhere('s.author = :user')
            ->setParameter('user', $user)
            ->orderBy('s.dateShare', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param UserInterface $user
     * @param Request $request
     * @param null $searchTerm
     * @param array $categoryTerm
     * @return PaginationInterface Returns an array of Article objects
     */
    public function findCommentedArticles(UserInterface $user, Request $request, $searchTerm = null, $categoryTerm = [])
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.author', 'au')
            ->leftJoin('a.comments', 'c')
            ->andWhere('c.author = :user')
            ->setParameter('user', $user);
        if ($categoryTerm == []) {
            $qb->andWhere($qb->expr()->like('a.title', ':searchTerm'));
        } else {
            $qb->andWhere($qb->expr()->like('a.title', ':searchTerm'))
                ->andWhere("a.category IN(:categoryTerm)")
                ->setParameter('categoryTerm', $categoryTerm);
        }
        $query = $qb->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->getQuery();

        return $this->knp->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            5 /*limit per page*/
        );
    }

    /**
     * @param UserInterface $user
     * @return Article[] Returns an array of Article objects
     */
    public function findLastCommentedArticles(UserInterface $user)
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.comments', 'c')
            ->andWhere('c.author = :user')
            ->setParameter('user', $user)
            ->orderBy('c.creationDate', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }
    // $qb = $this->_em->createQueryBuilder();
    // $qb->select('t, c')
    //     ->from('AppBundle:Transactional','t')
    //     ->join('t.fkCustomer', 'c');

    // return $qb->getQuery()->execute();


    //     SELECT article.* FROM article , like
    // WHERE like.Article =:article AND like.user = :user


    // /**
    //  * @return Article[] Returns an array of Article objects
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
    public function findOneBySomeField($value): ?Article
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
