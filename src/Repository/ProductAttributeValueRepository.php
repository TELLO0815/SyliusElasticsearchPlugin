<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusElasticsearchPlugin\Repository;

use Sylius\Component\Product\Repository\ProductAttributeValueRepositoryInterface as BaseAttributeValueRepositoryInterface;
use Sylius\Component\Attribute\Model\AttributeInterface;

final class ProductAttributeValueRepository implements ProductAttributeValueRepositoryInterface
{
    /** @var BaseAttributeValueRepositoryInterface */
    private $baseAttributeValueRepository;

    public function __construct(BaseAttributeValueRepositoryInterface $baseAttributeValueRepository)
    {
        $this->baseAttributeValueRepository = $baseAttributeValueRepository;
    }

    public function getUniqueAttributeValues(AttributeInterface $productAttribute): array
    {
        $queryBuilder = $this->baseAttributeValueRepository->createQueryBuilder('o');

        return $queryBuilder
            ->where('o.attribute = :attribute')
//            ->groupBy('o.' . $productAttribute->getStorageType())
            ->setParameter(':attribute', $productAttribute)
            ->getQuery()
            ->getResult()
        ;
    }
}
