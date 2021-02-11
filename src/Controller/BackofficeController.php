<?php

namespace App\Controller;

use App\BL\ArticleManager;
use App\BL\CommentManager;
use App\BL\UserManager;
use App\Entity\Article;
use App\Entity\User;
use App\Form\ArticleFormType;
use App\Form\SearchCommentFormType;
use App\Form\UserCreateFromAdminType;
use App\Form\UserCreateType;
use App\Repository\UserRepository;
use App\Form\SearchFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BackofficeController
 * @package App\Controller
 */
class BackofficeController extends AbstractController
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
     * BackofficeController constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {

        $this->articleManager = new ArticleManager($em);
        $this->userManager = new UserManager($em);
        $this->commentManager = new CommentManager($em);
        $this->em = $em;
    }

    /**
     * @Route("/backoffice", name="backoffice")
     * @return Response
     */
    public function index(): Response
    {
        $recentArticles = $this->articleManager->getRecentArticlesBack();
        $nbWaitingComments = $this->commentManager->countWaitingComments();
        return $this->render('backoffice/index.html.twig', [
            'controller_name' => 'BackofficeController', 'recentArticles' => $recentArticles, 'nbWaitingComments' => $nbWaitingComments
        ]);
    }

    /**
     * @Route ("/backoffice/article", name="backofficeArticle")
     * @param Request $request
     * @return Response
     */
    public function articles(Request $request): Response
    {
        $form = $this->createForm(SearchFormType::class);
        $articles = $this->articleManager->listArticles($request);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $searchTerm = $form->get('search')->getData();
            $categoryTerm = $form->get('category')->getData();
            $articles = $this->articleManager->listArticles($request, $searchTerm, $categoryTerm);
        }
        return $this->render('backoffice/articles.html.twig', ['articles' => $articles, 'form' => $form->createView()]);
    }

    /**
     * @Route ("/backoffice/addArticle", name="backofficeAddArticle")
     * @return Response
     */
    public function addArticle(): Response
    {
       return $this->redirectToRoute('addArticle');
    }

    /**
     * @Route("/backoffice/editArticle/{idArticle}", name="backofficeEditArticle")
     * @param $idArticle
     * @return RedirectResponse|Response
     */
    public function editArticle($idArticle)
    {
        return $this->redirectToRoute('editArticle', ['idArticle' => $idArticle]);
    }

    /**
     * @Route ("/backoffice/comments", name="backofficeComment")
     * @param Request $request
     * @return Response
     */
    public function commentList(Request $request)
    {
        $comments = $this->commentManager->listComments($request);
        $form = $this->createForm(SearchCommentFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $searchTerm = $form->get('search')->getData();
            $statusTerm = $form->get('approved')->getData();
            $comments = $this->commentManager->listComments($request, $searchTerm, $statusTerm);
        }
        return $this->render('backoffice/comments.html.twig', ['comments' => $comments, 'form' => $form->createView()]);
    }

    /**
     * @Route ("/backoffice/comment/{idComment}", name="editComment")
     * @param $idComment
     * @return Response
     */
    public function editComment($idComment){
        $comment = $this->commentManager->getCommentById($idComment);
        return $this->render('backoffice/editComment.html.twig', ['comment' => $comment]);
    }

    /**
     * @Route ("/backoffice/comment/{idComment}/{approved}", name="approveComment")
     * @param $idComment
     * @param $approved
     * @return RedirectResponse
     */
    public function approveComment($idComment, $approved){
        $comment = $this->commentManager->getCommentById($idComment);
        $comment->setApproved($approved);
        $this->commentManager->saveData($comment);
        return $this->redirectToRoute('backofficeComment');
    }


    /**
     * @Route ("/backoffice/users/addUser", name="addUser")
     * @param Request $request
     * @return RedirectResponse
     */
    public function register(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserCreateFromAdminType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userWithEmail = $userRepository->findOneByEmail($user->getEmail());

            // Verify email not already attributed
            if (!$userWithEmail) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'The new user has been successfully created');
            } else {
                $this->addFlash('danger', 'An account already exists with this email');
            }
        }

        return $this->render('user/addFromBackoffice.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
