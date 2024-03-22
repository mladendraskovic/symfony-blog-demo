<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileController extends AbstractController
{
    private $userRepository;
    private $translator;

    public function __construct(UserRepository $userRepository, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->translator = $translator;
    }

    /**
     * @Route("/profile", name="app_profile", methods={"GET"}))
     * @IsGranted("IS_AUTHENTICATED")
     */
    public function show(Request $request): Response
    {
        $locale = $request->getLocale();

        $user = $this->userRepository->getProfileData($this->getUser()->getId(), $locale);

        return $this->render('pages/profile.html.twig', [
            'user' => $user
        ]);
    }

//    /**
//     * @Route("/profile/edit", name="app_profile", methods={"GET"}))
//     */
//    public function edit(Request $request): Response
//    {
//        $locale = $request->getLocale();
//        $userId = $this->getUser() ? $this->getUser()->getId() : null;
//
//        $post = $this->postRepository->findPostBySlug($slug, $locale, $userId);
//        $likesCount = $this->postRepository->getLikesCount($post->getId());
//
//        if (!$post) {
//            throw $this->createNotFoundException('No post found for slug ' . $slug);
//        }
//
//        $comment = new Comment();
//
//        $form = $this->createForm(CommentType::class, $comment);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            if (!$this->getUser()) {
//                throw $this->createAccessDeniedException('You must be logged in to comment.');
//            }
//
//            $comment->setPost($post);
//            $comment->setAuthor($this->getUser());
//
//            $this->entityManager->persist($comment);
//            $this->entityManager->flush();
//
//            $this->addFlash('success', $this->translator->trans('Your comment was saved successfully.'));
//
//            return $this->redirectToRoute('app_post', ['slug' => $slug]);
//        }
//
//        return $this->render('pages/post.html.twig', [
//            'post' => $post,
//            'likesCount' => $likesCount,
//            'form' => $form->createView()
//        ]);
//    }
}
