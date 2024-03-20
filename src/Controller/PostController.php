<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostTranslation;
use App\Form\PostType;
use App\Repository\PostRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/posts")
 */
class PostController extends AbstractController
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    /**
     * @Route("/", name="app_post_index", methods={"GET"})
     */
    public function index(PostRepository $postRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $locale = $request->getLocale();

        $query = $postRepository->getPaginationQuery($locale);

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/post/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/new", name="app_post_new", methods={"GET", "POST"})
     */
    public function new(Request $request, PostRepository $postRepository): Response
    {
        $locales = $this->params->get('kernel.enabled_locales');

        $post = new Post();

        $form = $this->createForm(PostType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser());
            $post->setPublishedAt(new DateTimeImmutable($form->get('published_at')->getData()));
            $post->setImage('default.jpg');

            foreach ($locales as $locale) {
                $translation = new PostTranslation();
                $translation->setLocale($locale);
                $translation->setTitle($form->get("title_$locale")->getData());
                $translation->setContent($form->get("content_$locale")->getData());
                $post->addTranslation($translation);
            }

            $postRepository->add($post, true);

            $this->addFlash('success', 'Post created successfully!');

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_post_show", methods={"GET"})
     */
    public function show(Post $post, PostRepository $postRepository): Response
    {
        return $this->render('admin/post/show.html.twig', [
            'post' => $postRepository->getPostData($post),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_post_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Post $post, PostRepository $postRepository, EntityManagerInterface $entityManager): Response
    {
        $locales = $this->params->get('kernel.enabled_locales');

        $postData = $postRepository->getPostData($post);

        $form = $this->createForm(PostType::class, $postData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUpdatedAt(new DateTime());

            foreach ($locales as $locale) {
                $translation = $post->getTranslations()->filter(function (PostTranslation $translation) use ($locale) {
                    return $translation->getLocale() === $locale;
                })->first();

                $translation->setTitle($form->get("title_$locale")->getData());
                $translation->setContent($form->get("content_$locale")->getData());
            }

            $entityManager->flush();

            $this->addFlash('success', 'Post updated successfully!');

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/post/edit.html.twig', [
            'post' => $postData,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="app_post_delete", methods={"POST"})
     */
    public function delete(Request $request, Post $post, PostRepository $postRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $postRepository->remove($post, true);

        }

        $this->addFlash('success', 'Post deleted successfully!');

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
