<?php


namespace App\BL;


use App\Entity\Share;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ShareManager
 * @package App\BL
 */
class ShareManager
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /*** @var EntityManagerInterface l'interface entity manager* nécessaire à la manipulation des opérations en base*/
    protected $em;

    /**
     * @param Share $share
     */
    public function saveData(Share $share){

        $this->em->persist($share);
        $this->em->flush();
    }

    /**
     * @return Share[]|array
     */
    public function getShares(){
        return $this->em->getRepository(Share::class)->findAll();
    }

    /**
     * @param $share
     */
    public function deleteShare($share)
    {
        $this->em->remove($share);
        $this->em->flush();
    }
}
