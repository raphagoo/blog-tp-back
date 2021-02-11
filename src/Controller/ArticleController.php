<?php

namespace App\Controller;

use Exception;
use Symfony\Component\HttpFoundation\File\File as FileObject;
use App\Api\ArticleReferenceUploadApiModel;
use App\BL\ArticleManager;
use App\BL\CategoryManager;
use App\BL\CommentManager;
use App\BL\LikeManager;
use App\BL\ShareManager;
use App\BL\UserManager;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Like;
use App\Entity\Share;
use App\Form\ArticleFormType;
use App\Form\CommentFormType;
use App\Helpers\SerializerHelper;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @var CommentManager
     */
    private $commentManager;
    /**
     * @var LikeManager
     */
    private $likeManager;
    /**
     * @var ShareManager
     */
    private $shareManager;

    /**
     * ArticleController constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {

        $this->articleManager = new ArticleManager($em);
        $this->userManager = new UserManager($em);
        $this->commentManager = new CommentManager($em);
        $this->likeManager = new LikeManager($em);
        $this->shareManager = new ShareManager($em);
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
        if (preg_match('/^data:image\/(\w+);base64,/', $uploadApiModel->data, $type)) {
            $data = substr($uploadApiModel->data, strpos($uploadApiModel->data, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif

            if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
                throw new Exception('invalid image type');
            }
            $data = str_replace( ' ', '+', $data );
            $data = base64_decode($data);

            if ($data === false) {
                throw new Exception('base64_decode failed');
            }
        } else {
            throw new Exception('did not match data URI with image data');
        }

        $fileName = "img-" . uniqid() . ".{$type}";

        file_put_contents("./uploads/images/${fileName}", $data);


        $article->setImage($fileName);

        $articleManager->GetInscriptionData($article);
        return $this->json('', 200, []);
    }

    /**
     * @Route("/article/{idArticle}", name="editArticle", methods={"PUT"})
     * @param $idArticle
     * @param Request $request
     * @param SerializerHelper $serializerHelper
     * @param ArticleManager $articleManager
     * @param CategoryManager $categoryManager
     * @param UserManager $userManager
     * @return RedirectResponse|Response
     */
    public function editArticle($idArticle, Request $request, SerializerHelper $serializerHelper, ArticleManager $articleManager, CategoryManager $categoryManager, UserManager $userManager)
    {
       $json = $request->getContent();
        $data = json_decode($request->getContent(), true);
       $article = $articleManager->findArticleById($idArticle);
       $article = $serializerHelper->deserializeRequest($json, Article::class, $article);
       $category = $categoryManager->getCategoryById($data['category_id']);
       $article->setCategory($category);
       $admin = $userManager->findUserById(1);
       $article->setAuthor($admin);
       $articleManager->GetInscriptionData($article);
       return $this->json('', 200, []);
    }

    /**
     * @Route("/article/{idArticle}", name="viewArticle", methods={"GET"})
     * @param $idArticle
     * @param SerializerHelper $serializerHelper
     * @return Response
     */
    public function viewArticle($idArticle, SerializerHelper $serializerHelper){
        $article = $this->articleManager->findArticleById($idArticle);
        $files = scandir("./uploads/images");
        $index = array_search($article->getImage(), $files);
        $path = "./uploads/images/$files[$index]";
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $serializerHelper->prepareResponse($article, 'article_details', $base64);

    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route ("/article/{idArticle}/share", name="shareArticle")
     * @param $idArticle
     * @return RedirectResponse
     */
    public function shareArticle($idArticle)
    {
        $article = $this->articleManager->findArticleById($idArticle);
        $share = new Share();
        $share->setArticle($article);
        $share->setAuthor($this->getUser());
        $share->setDateShare(new \DateTime('now'));
        $this->shareManager->saveData($share);
        return $this->redirectToRoute('viewArticle', ['idArticle' => $idArticle]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route ("/article/{idArticle}/{liked}", name="likeArticle")
     * @param $idArticle
     * @param $liked
     * @return RedirectResponse
     */
    public function likeArticle($idArticle, $liked)
    {
        $article = $this->articleManager->findArticleById($idArticle);
        if($liked === 'true'){
            $like = new Like();
            $like->setArticle($article);
            $like->setAuthor($this->getUser());
            $like->setDateLike(new \DateTime('now'));
            $this->likeManager->saveData($like);
        }
        else{
            $likes = $article->getLikes();
            foreach ($likes as $like){
                if($like->getAuthor() === $this->getUser()){
                    $this->likeManager->deleteLike($like);
                }
            }
        }
        return $this->redirectToRoute('viewArticle', ['idArticle' => $idArticle]);
    }


}
