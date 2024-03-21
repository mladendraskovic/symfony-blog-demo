<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    private $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    /**
     * @Route("/posts/{slug}", name="app_post", methods={"GET"}))
     */
    public function show(Request $request, string $slug): Response
    {
        $locale = $request->getLocale();
        $post = $this->postRepository->findPostBySlug($slug, $locale);

        return $this->render('pages/post.html.twig', [
            'post' => $post,
        ]);
    }
}
