<?php

namespace App\Controller;

use App\Entity\Category;
use App\Helpers\SerializerHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\BL\CategoryManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CategoryController
 * @package App\Controller
 */
class CategoryController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * CategoryController constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {

        $this->categoryManager = new CategoryManager($em);
        $this->em = $em;
    }

    /**
     * @var CategoryManager
     */
    private CategoryManager $categoryManager;

    /**
     * @Route("/category", name="category")
     * @param SerializerHelper $serializerHelper
     * @return Response
     */
    public function index(SerializerHelper $serializerHelper): Response
    {
        $listCategory =  $this->categoryManager->getCategoryList();
        return $serializerHelper->prepareResponse($listCategory, 'list_categories');
        
    }

    /**
     * @Route("/category", name="addCategory", methods={"POST"})
     * @param Request $request
     * @param SerializerHelper $serializerHelper
     * @param CategoryManager $categoryManager
     * @return Response
     */
    public function addCategory(Request $request, SerializerHelper $serializerHelper, CategoryManager $categoryManager): Response
    {
        $json = $request->getContent();
        $category = new Category();
        $category = $serializerHelper->deserializeRequest($json, Category::class, $category);
        $categoryManager->GetInscriptionData($category);
        return $this->json('', 201, []);
    }

}
