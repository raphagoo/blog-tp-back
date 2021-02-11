<?php


namespace App\BL;


use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CommentManager
 * @package App\BL
 */
class CommentManager
{
    /**
     * CommentManager constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /*** @var EntityManagerInterface l'interface entity manager* nécessaire à la manipulation des opérations en base*/
    protected $em;

    /**
     * @param Comment $comment
     */
    public function saveData(Comment $comment){

        $this->em->persist($comment);
        $this->em->flush();
    }

    /**
     * @return Comment[]|array
     */
    public function getComments(){
        return $this->em->getRepository(Comment::class)->findAll();
    }

    /**
     * @param $idComment
     * @return Comment|null
     */
    public function getCommentById($idComment){
        return $this->em->getRepository(Comment::class)->find($idComment);
    }

    /**
     * @param Request $request
     * @param null $searchTerm
     * @param null[] $statusTerm
     * @return mixed
     */
    public function listComments(Request $request, $searchTerm = null, $statusTerm = [null]){
        return $this->em->getRepository(Comment::class)->listComments($request, $searchTerm, $statusTerm);
    }

    /**
     * @return mixed
     */
    public function countWaitingComments(){
        return $this->em->getRepository(Comment::class)->count(['approved' => null]);
    }
}
