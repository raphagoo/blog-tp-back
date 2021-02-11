<?php

namespace App\Controller;

use App\BL\TagManager;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\TagFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TagController
 * @package App\Controller
 */
class TagController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var TagManager
     */
    private $tagManager;

    /**
     * TagController constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {

        $this->tagManager = new TagManager($em);
        $this->em = $em;
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/tag", name="tag")
     */
    public function index(): Response
    {
        $listTag =  $this->tagManager->getTagList();
        return $this->render('tag/index.html.twig', ['listTag' => $listTag]);
        
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("tag/add", name="addTag")
     * @param Request $request
     * @return Response
     */
    public function addTag(Request $request)
    {

        $tag = new Tag();
        $form = $this->createForm(TagFormType::class, $tag);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->tagManager->GetInscriptionData($tag);
            return $this->redirectToRoute('tag');
        }
        return $this->render('tag/tagAdd.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("tag/edit/{idTag}", name="editTag")
     * @param $idTag
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function getModifyCategory($idTag, Request $request)
    {
        $tag = $this->tagManager->getTagById($idTag);
        $form = $this->createForm(TagFormType::class, $tag);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->tagManager->GetInscriptionData($tag);
            return $this->redirectToRoute('tag');
        }
        return $this->render('tag/tagEdit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("tag/delete/{idTag}",name="deleteTag")
     * @param $idTag
     * @return RedirectResponse|Response
     */
    public function deleteTag($idTag)
    {
        $tag = $this->tagManager->getTagById($idTag);
        $this->tagManager->deleteTag($tag);
        return $this->redirectToRoute('tag');
    }
}
