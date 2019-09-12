<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusElasticsearchPlugin\Form\Type\ChoiceMapper;

use BitBag\SyliusElasticsearchPlugin\Context\TaxonContextInterface;
use BitBag\SyliusElasticsearchPlugin\Repository\ProductAttributeValueRepositoryInterface;
use BitBag\SyliusElasticsearchPlugin\Formatter\StringFormatterInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;

final class ProductAttributesMapper implements ProductAttributesMapperInterface
{
    /** @var ProductAttributeValueRepositoryInterface */
    private $productAttributeValueRepository;

    /** @var LocaleContextInterface */
    private $localeContext;

    /** @var StringFormatterInterface */
    private $stringFormatter;

    /** @var TaxonContextInterface */
    private $taxonContext;

    public function __construct(
        ProductAttributeValueRepositoryInterface $productAttributeValueRepository,
        LocaleContextInterface $localeContext,
        StringFormatterInterface $stringFormatter,
        TaxonContextInterface $taxonContext
    ) {
        $this->productAttributeValueRepository = $productAttributeValueRepository;
        $this->localeContext = $localeContext;
        $this->stringFormatter = $stringFormatter;
        $this->taxonContext = $taxonContext;
    }

    public function mapToChoices(ProductAttributeInterface $productAttribute): array
    {
        $configuration = $productAttribute->getConfiguration();

        if (isset($configuration['choices']) && is_array($configuration['choices'])) {
            $choices = [];
            foreach ($configuration['choices'] as $singleValue => $val) {
                $choice = $this->stringFormatter->formatToLowercaseWithoutSpaces($singleValue);
                $label = $configuration['choices'][$singleValue][$this->localeContext->getLocaleCode()];
                $choices[$label] = $choice;
            }

            return $choices;
        }

        $currentTaxon = $this->taxonContext->getTaxon()->getId();
        $attributeValues = $this->productAttributeValueRepository->getUniqueAttributeValues($productAttribute);


        $number = 0;
        foreach ($attributeValues as $attributeValue) {

            if ($attributeValue->getSubject()->getMainTaxon()->getId() != $currentTaxon){

                unset($attributeValues[$number]);

            }

            $number++;
        }

        $choices = [];
        array_walk($attributeValues, function (ProductAttributeValueInterface $productAttributeValue) use (&$choices): void {
            $product = $productAttributeValue->getProduct();

            if (!$product->isEnabled()) {
                unset($product);

                return;
            }

                $value = $productAttributeValue->getValue();
                $configuration = $productAttributeValue->getAttribute()->getConfiguration();
                $mainTaxon = $productAttributeValue->getSubject()->getMainTaxon()->getId();

                if (is_array($value)
                    && isset($configuration['choices'])
                    && is_array($configuration['choices'])
                ) {
                    foreach ($value as $singleValue) {
                        $choice = $this->stringFormatter->formatToLowercaseWithoutSpaces($singleValue);
                        $label = $configuration['choices'][$singleValue][$this->localeContext->getLocaleCode()];
                        $taxon = $mainTaxon;
                        $choices[$label.'-taxon'.$taxon] = $choice;
                    }
                } else {
                    $choice = is_string($value) ? $this->stringFormatter->formatToLowercaseWithoutSpaces($value) : $value;
                    $taxon = $mainTaxon;
                    $choices[$value.'-taxon'.$taxon] = $choice;
                }

        });
        unset($attributeValues);

        return $choices;
    }
}
