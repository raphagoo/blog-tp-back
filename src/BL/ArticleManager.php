<?php


namespace App\BL;

use Exception;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ArticleManager
 * @package App\BL
 */
class ArticleManager
{
    /**
     * ArticleManager constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /*** @var EntityManagerInterface l'interface entity manager* nécessaire à la manipulation des opérations en base*/
    protected $em;


    /**
     * @return Article[]
     */
    public function getArticles(){
        return $this->em->getRepository(Article::class)->findAll();
    }

    /**
     * @param Request $request
     * @param null $searchTerm
     * @param array $categoryTerm
     * @return mixed
     */
    public function listArticles(Request $request, $searchTerm = null, $categoryTerm = [])
    {
        return $this->em->getRepository(Article::class)->listArticles($request,$searchTerm, $categoryTerm);
    }

    /**
     * @param Request $request
     * @param UserInterface $user
     * @param null $searchTerm
     * @param array $categoryTerm
     * @return mixed
     */
    public function listLikedArticles(Request $request, UserInterface $user, $searchTerm = null, $categoryTerm = [])
    {
        return $this->em->getRepository(Article::class)->findLikedArticles($user, $request, $searchTerm, $categoryTerm);
    }

    /**
     * @param Request $request
     * @param UserInterface $user
     * @param null $searchTerm
     * @param array $categoryTerm
     * @return mixed
     */
    public function listSharedArticles(Request $request, UserInterface $user, $searchTerm = null, $categoryTerm = [])
    {
        return $this->em->getRepository(Article::class)->findSharedArticles($user, $request, $searchTerm, $categoryTerm);
    }

    /**
     * @param Request $request
     * @param UserInterface $user
     * @param null $searchTerm
     * @param array $categoryTerm
     * @return mixed
     */
    public function listCommentedArticles(Request $request, UserInterface $user, $searchTerm = null, $categoryTerm = [])
    {
        return $this->em->getRepository(Article::class)->findCommentedArticles($user, $request, $searchTerm, $categoryTerm);
    }

    /**
     * @param Article $article
     * @return Article
     */
    public function GetInscriptionData(Article $article){

        $this->em->persist($article);
        $this->em->flush();
        return $article;
    }

    /**
     * @param $idArticle
     * @return Article|null
     */
    public function findArticleById($idArticle){
        return $this->em->getRepository(Article::class)->find($idArticle);
    }

    /**
     * @param $idArticle
     * @return mixed
     */
    public function getRecentArticles($idArticle)
    {
        return $this->em->getRepository(Article::class)->findRecents($idArticle);
    }

    /**
     * @return mixed
     */
    public function getRecentArticlesBack()
    {
        return $this->em->getRepository(Article::class)->findRecentsBack();
    }

    /**
     * @param $article
     */
    public function deleteArticle($article)
    {
        $this->em->remove($article);
        $this->em->flush();
    }

    /**
     * Transform the base64 data and stores it as a file on the server
     * @param $uploadApiModel
     * @return string
     * @throws Exception
     */
    public function saveImageFile($uploadApiModel): string
    {

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
        return $fileName;
    }
}
