<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Review;
use App\Enum\PartnerEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    protected ManagerRegistry $managerRegistry;
    protected ObjectManager $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
        $this->managerRegistry = $registry;
        $this->entityManager = $registry->getManager();
    }


    public function insertReviews(\Generator $reviews): void
    {
        foreach ($reviews as $item) {

            if($item) {
                $date = new \DateTime($item['created_at']);
                $entity = $this->entityManager->getRepository(Review::class)->findOneBy(
                    ['product' => $item['product'], 'authorName' => $item['author_name'], 'createdAt' => $date]
                );

                if ($entity === null) {
                    $this->entityManager->beginTransaction();

                    try{
                        $review = new Review();
                        $review->setProduct($item['product']);
                        $review->setAuthorName($item['author_name']);
                        $review->setRate($item['rate']);
                        $review->setComment($item['comment']);
                        $review->setMarketId($item['market_id']);
                        $review->setCreatedAt($date);

                        $this->entityManager->persist($review);
                        $this->entityManager->flush();
                        $this->entityManager->commit();
                    } catch (\Throwable $exception) {
                        if (!$this->entityManager->isOpen()) {
                            $this->managerRegistry->resetManager();
                        }
                        $this->entityManager->rollback();
                        //print_r($exception->getMessage());
                        continue;
                    }
                }
            }

        }
    }


    //    /**
    //     * @return Review[] Returns an array of Review objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Review
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
