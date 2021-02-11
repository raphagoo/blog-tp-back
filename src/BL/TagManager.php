<?php

namespace App\BL;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class TagManager
 * @package App\BL
 */
class TagManager
{

    /**
     * TagManager constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    /*** @var EntityManagerInterface l'interface entity manager* nécessaire à la manipulation des opérations en base*/
    protected $em;


    /**
     * @return Tag[]
     */
    public function getTagList(){
        return $this->em->getRepository(Tag::class)->findAll();

    }

    /**
     * @param $idTag
     * @return Tag|object|null
     */
    public function getTagById($idTag)
    {
        return $this->em->getRepository(Tag::class)->find($idTag);
    }

    /**
     * @param Tag $newTag
     */
    public function GetInscriptionData(Tag $newTag){
        
        $this->em->persist($newTag);
        $this->em->flush();
    }

    /**
     * @param $tag
     */
    public function gestionTag($tag)
    {
        $this->em->persist($tag);
        $this->em->flush();
    }

   /**
     * @param $tag
     */
    public function deleteTag($tag)
    {
        $this->em->remove($tag);
        $this->em->flush();
    }
}
