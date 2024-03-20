<?php

namespace App\Repository;

use App\Entity\Tag;
use App\Entity\TagTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 *
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function getPaginationQuery(string $locale): Query
    {
        return $this->createQueryBuilder('t')
            ->select('t, tr')
            ->innerJoin('t.translations', 'tr')
            ->where('tr.locale = :locale')
            ->setParameter('locale', $locale)
            ->getQuery();
    }

    public function getTagData(Tag $tag): array
    {
        return [
            'id' => $tag->getId(),
            'name_en' => $tag->getTranslations()->filter(function(TagTranslation $translation) {
                return $translation->getLocale() === 'en';
            })->first()->getName(),
            'name_hr' => $tag->getTranslations()->filter(function(TagTranslation $translation) {
                return $translation->getLocale() === 'hr';
            })->first()->getName(),
        ];
    }

    public function add(Tag $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Tag $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Tag[] Returns an array of Tag objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Tag
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
