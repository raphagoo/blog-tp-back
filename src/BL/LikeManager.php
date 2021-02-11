<?php


namespace App\BL;


use App\Entity\Like;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class LikeManager
 * @package App\BL
 */
class LikeManager
{
    /**
     * LikeManager constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /*** @var EntityManagerInterface l'interface entity manager* nécessaire à la manipulation des opérations en base*/
    protected $em;

    /**
     * @param Like $like
     */
    public function saveData(Like $like){

        $this->em->persist($like);
        $this->em->flush();
    }

    public function getLikes(){
        return $this->em->getRepository(Like::class)->findAll();
    }

    /**
     * @param $like
     */
    public function deleteLike($like)
    {
        $this->em->remove($like);
        $this->em->flush();
    }

}
