<?php

namespace App\BL;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class AdminManager
 * @package App\BL
 */
class CategoryManager
{
    /**
     * CategoryManager constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    /*** @var EntityManagerInterface l'interface entity manager* nécessaire à la manipulation des opérations en base*/
    protected $em;


    /**
     * @return Category[]
     */
    public function getCategoryList(){
        return $this->em->getRepository(Category::class)->findAll();

    }

    /**
     * @param $idCategory
     * @return Category|object|null
     */
    public function getCategoryById($idCategory)
    {
        return $this->em->getRepository(Category::class)->find($idCategory);
    }

    /**
     * @param Category $newCategory
     */
    public function GetInscriptionData(Category $newCategory){
        
        $this->em->persist($newCategory);
        $this->em->flush();
    }

    /**
     * @param $category
     */
    public function gestionCategory($category)
    {
        $this->em->persist($category);
        $this->em->flush();
    }

   /**
     * @param $category
     */
    public function deleteCategory($category)
    {
        $this->em->remove($category);
        $this->em->flush();
    }
}
