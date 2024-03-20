<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\TagTranslation;
use App\Form\TagType;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/tags")
 */
class TagController extends AbstractController
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    /**
     * @Route("/", name="app_tag_index", methods={"GET"})
     */
    public function index(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        $locale = $request->getLocale();

        $query = $entityManager->createQuery('
            SELECT t, tr FROM App\Entity\Tag t
            INNER JOIN t.translations tr
            WHERE tr.locale = :locale
        ')
            ->setParameter('locale', $locale);

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/tag/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/new", name="app_tag_new", methods={"GET", "POST"})
     */
    public function new(Request $request, TagRepository $tagRepository): Response
    {
        $locales = $this->params->get('kernel.enabled_locales');

        $tag = new Tag();

        $form = $this->createForm(TagType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($locales as $locale) {
                $translation = new TagTranslation();
                $translation->setLocale($locale);
                $translation->setName($form->get("name_$locale")->getData());
                $tag->addTranslation($translation);
            }

            $tagRepository->add($tag, true);

            $this->addFlash('success', 'Tag created successfully!');

            return $this->redirectToRoute('app_tag_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/tag/new.html.twig', [
            'tag' => $tag,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_tag_show", methods={"GET"})
     */
    public function show(int $id, TagRepository $tagRepository): Response
    {
        $tag = $tagRepository->find($id);

        return $this->render('admin/tag/show.html.twig', [
            'tag' => [
                'id' => $tag->getId(),
                'name_en' => $tag->getTranslations()->filter(function(TagTranslation $translation) {
                    return $translation->getLocale() === 'en';
                })->first()->getName(),
                'name_hr' => $tag->getTranslations()->filter(function(TagTranslation $translation) {
                    return $translation->getLocale() === 'hr';
                })->first()->getName(),
            ]
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_tag_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, int $id, TagRepository $tagRepository, EntityManagerInterface $entityManager): Response
    {
        $locales = $this->params->get('kernel.enabled_locales');

        $tag = $tagRepository->find($id);

        $form = $this->createForm(TagType::class, [
            'name_en' => $tag->getTranslations()->filter(function(TagTranslation $translation) {
                return $translation->getLocale() === 'en';
            })->first()->getName(),
            'name_hr' => $tag->getTranslations()->filter(function(TagTranslation $translation) {
                return $translation->getLocale() === 'hr';
            })->first()->getName(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($locales as $locale) {
                $translation = $tag->getTranslations()->filter(function(TagTranslation $translation) use ($locale) {
                    return $translation->getLocale() === $locale;
                })->first();

                $translation->setName($form->get("name_$locale")->getData());
            }

            $entityManager->flush();

            $this->addFlash('success', 'Tag updated successfully!');

            return $this->redirectToRoute('app_tag_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/tag/edit.html.twig', [
            'tag' => [
                'id' => $tag->getId(),
                'name_en' => $tag->getTranslations()->filter(function(TagTranslation $translation) {
                    return $translation->getLocale() === 'en';
                })->first()->getName(),
                'name_hr' => $tag->getTranslations()->filter(function(TagTranslation $translation) {
                    return $translation->getLocale() === 'hr';
                })->first()->getName(),
            ],
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="app_tag_delete", methods={"POST"})
     */
    public function delete(Request $request, Tag $tag, TagRepository $tagRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $tag->getId(), $request->request->get('_token'))) {
            $tagRepository->remove($tag, true);

        }

        $this->addFlash('success', 'User deleted successfully!');

        return $this->redirectToRoute('app_tag_index', [], Response::HTTP_SEE_OTHER);
    }
}
