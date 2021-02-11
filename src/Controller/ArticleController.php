<?php

namespace App\Controller;

use Exception;
use App\Api\ArticleReferenceUploadApiModel;
use App\BL\ArticleManager;
use App\BL\CategoryManager;
use App\BL\UserManager;
use App\Entity\Article;
use App\Helpers\SerializerHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class ArticleController
 * @package App\Controller
 */
class ArticleController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var ArticleManager
     */
    private $articleManager;
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * ArticleController constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->articleManager = new ArticleManager($em);
        $this->userManager = new UserManager($em);
        $this->em = $em;
    }

    /**
     * @Route("/article", name="listArticle", methods={"GET"})
     * @param Request $request
     * @param SerializerHelper $serializerHelper
     * @param ArticleManager $articleManager
     * @return Response
     */
    public function listArticle(Request $request, SerializerHelper $serializerHelper, ArticleManager $articleManager): Response
    {
        $articles = $articleManager->getArticles();
        for ($i = 0; $i <  count($articles); $i++) {
            $files = scandir("./uploads/images");
            if(!is_null($articles[$i]->getImage())) {
                $index = array_search($articles[$i]->getImage(), $files);
                if($index !== false) {
                    $path = "./uploads/images/$files[$index]";
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    $articles[$i]->setImage($base64);
                }
            }
        }
        return $serializerHelper->prepareResponse($articles, 'list_articles');
    }

    /**
     * @Route("/article/{idArticle}", name="deleteArticle", methods={"DELETE"})
     * @param Request $request
     * @param $idArticle
     * @param SerializerHelper $serializerHelper
     * @param ArticleManager $articleManager
     * @return Response
     */
    public function deleteArticle(Request $request, $idArticle, SerializerHelper $serializerHelper, ArticleManager $articleManager): Response
    {
        $article = $articleManager->findArticleById($idArticle);
        $articleManager->deleteArticle($article);
        return $this->json('', 200, []);
    }

    /**
     * @Route("/article", name="addArticle", methods={"POST", "OPTIONS"})
     * @param Request $request
     * @param SerializerHelper $serializerHelper
     * @param SerializerInterface $serializer
     * @param ArticleManager $articleManager
     * @param CategoryManager $categoryManager
     * @param UserManager $userManager
     * @return Response
     * @throws Exception
     */
    public function addArticle(Request $request, SerializerHelper $serializerHelper, SerializerInterface $serializer, ArticleManager $articleManager, CategoryManager $categoryManager, UserManager $userManager): Response
    {
        $json = $request->getContent();
        $data = json_decode($request->getContent(), true);
        $article = new Article();
        $article = $serializerHelper->deserializeRequest($json, Article::class, $article);
        $category = $categoryManager->getCategoryById($data['category_id']);
        $article->setCategory($category);
        $admin = $userManager->findUserById(1);
        $article->setAuthor($admin);
        $uploadApiModel = $serializer->deserialize(
            $request->getContent(),
            ArticleReferenceUploadApiModel::class,
            'json'
        );
        if(!is_null($uploadApiModel->data)) {
            $fileName = $articleManager->saveImageFile($uploadApiModel);

            $article->setImage($fileName);
        }

        $articleManager->GetInscriptionData($article);
        return $this->json('', 201, []);
    }

    /**
     * @Route("/article/{idArticle}", name="editArticle", methods={"PUT"})
     * @param $idArticle
     * @param Request $request
     * @param SerializerHelper $serializerHelper
     * @param ArticleManager $articleManager
     * @param CategoryManager $categoryManager
     * @param UserManager $userManager
     * @param SerializerInterface $serializer
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function editArticle($idArticle, Request $request, SerializerHelper $serializerHelper, ArticleManager $articleManager, CategoryManager $categoryManager, UserManager $userManager, SerializerInterface $serializer)
    {
       $json = $request->getContent();
        $data = json_decode($request->getContent(), true);
       $article = $articleManager->findArticleById($idArticle);
       $article = $serializerHelper->deserializeRequest($json, Article::class, $article);
       $category = $categoryManager->getCategoryById($data['category_id']);
       $article->setCategory($category);
       $admin = $userManager->findUserById(1);
       $article->setAuthor($admin);

        $uploadApiModel = $serializer->deserialize(
            $request->getContent(),
            ArticleReferenceUploadApiModel::class,
            'json'
        );

        $fileName = $articleManager->saveImageFile($uploadApiModel);

        $article->setImage($fileName);

       $articleManager->GetInscriptionData($article);
       return $this->json('', 200, []);
    }

    /**
     * @Route("/article/{idArticle}", name="viewArticle", methods={"GET"})
     * @param $idArticle
     * @param SerializerHelper $serializerHelper
     * @return Response
     */
    public function viewArticle($idArticle, SerializerHelper $serializerHelper): Response
    {
        $article = $this->articleManager->findArticleById($idArticle);
        $files = scandir("./uploads/images");
        if(!is_null($article->getImage())) {
            $index = array_search($article->getImage(), $files);
            if($index !== false) {
                $path = "./uploads/images/$files[$index]";
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                $article->setImage($base64);
            }
        }
        return $serializerHelper->prepareResponse($article, 'article_details');

    }


}
