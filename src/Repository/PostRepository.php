<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\PostTranslation;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function getPaginationQuery(string $locale, ?string $searchTerm = null): Query
    {
        $query = $this->createQueryBuilder('p')
            ->select('p, pt, u, t, tt')
            ->innerJoin('p.translations', 'pt', 'WITH', 'pt.locale = :locale')
            ->innerJoin('p.author', 'u')
            ->leftJoin('p.tags', 't')
            ->leftJoin('t.translations', 'tt', 'WITH', 'tt.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('p.publishedAt', 'DESC');

        if ($searchTerm) {
            $query
                ->where('pt.title LIKE :searchTerm')
                ->setParameter('searchTerm', "%$searchTerm%");
        }

        return $query->getQuery();
    }

    public function getPostData(Post $post, string $locale): array
    {
        $tags = $this->getEntityManager()->createQueryBuilder()
            ->select('t, tr')
            ->from(Tag::class, 't')
            ->innerJoin('t.translations', 'tr')
            ->innerJoin('t.posts', 'p')
            ->where('p.id = :id')
            ->andWhere('tr.locale = :locale')
            ->setParameter('id', $post->getId())
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult();

        return [
            'id' => $post->getId(),
            'author' => $post->getAuthor()->getName(),
            'image_url' => $post->getImageUrl(),
            'published_at' => $post->getPublishedAt()->format('Y-m-d'),
            'title_en' => $post->getTranslations()->filter(function (PostTranslation $translation) {
                return $translation->getLocale() === 'en';
            })->first()->getTitle(),
            'title_hr' => $post->getTranslations()->filter(function (PostTranslation $translation) {
                return $translation->getLocale() === 'hr';
            })->first()->getTitle(),
            'slug_en' => $post->getTranslations()->filter(function (PostTranslation $translation) {
                return $translation->getLocale() === 'en';
            })->first()->getSlug(),
            'slug_hr' => $post->getTranslations()->filter(function (PostTranslation $translation) {
                return $translation->getLocale() === 'hr';
            })->first()->getSlug(),
            'content_en' => $post->getTranslations()->filter(function (PostTranslation $translation) {
                return $translation->getLocale() === 'en';
            })->first()->getContent(),
            'content_hr' => $post->getTranslations()->filter(function (PostTranslation $translation) {
                return $translation->getLocale() === 'hr';
            })->first()->getContent(),
            'tags' => array_map(function (Tag $tag) {
                return [
                    'id' => $tag->getId(),
                    'name' => $tag->getTranslations()->first()->getName(),
                ];
            }, $tags),
            'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $post->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    public function save(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Post[] Returns an array of Post objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Post
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
