<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class LikeController extends AbstractController
{
    private $postRepository;
    private $entityManager;
    private $translator;

    public function __construct(PostRepository $postRepository, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->postRepository = $postRepository;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * @Route("/posts/{id}/like", name="app_post_like", methods={"POST"}))
     * @IsGranted("IS_AUTHENTICATED")
     */
    public function like(Request $request, Post $post): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $this->postRepository->likePost($post->getId(), $user->getId());

        return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('app_home'));
    }

    /**
     * @Route("/posts/{id}/unlike", name="app_post_unlike", methods={"POST"}))
     * @IsGranted("IS_AUTHENTICATED")
     */
    public function unlike(Request $request, Post $post): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $this->postRepository->unlikePost($post->getId(), $user->getId());

        return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('app_home'));
    }
}
