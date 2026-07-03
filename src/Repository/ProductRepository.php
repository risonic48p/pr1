<?php

namespace App\Repository;

use App\Entity\Product;
use App\Enum\PartnerEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    protected ManagerRegistry $managerRegistry;
    protected ObjectManager $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
        $this->managerRegistry = $registry;
        $this->entityManager = $registry->getManager();
    }


    public function insertProducts(\Generator $products): void
    {
        foreach ($products as $item) {
            if($item) {
                $product = new Product();
                $product->setTitle($item['title']);
                $product->setUrl($item['url']);
                $product->setPartner(PartnerEnum::from($item['partner']));

                $this->entityManager->beginTransaction();
                try{
                    $this->entityManager->persist($product);
                    $this->entityManager->flush();
                    $this->entityManager->commit();
                } catch (\Throwable $exception) {
                    if (!$this->entityManager->isOpen()) {
                        $this->entityManager->rollback();
                        $this->managerRegistry->resetManager();
                        //print_r($exception->getMessage());
                    }

                    continue;
                }
            }
        }
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
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

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
