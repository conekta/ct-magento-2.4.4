<?php

namespace Conekta\Payments\Model\System\Config\Source;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Option\ArrayInterface;

class MetadataProduct implements ArrayInterface
{
    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        protected SearchCriteriaBuilder $searchCriteriaBuilder,
        protected AttributeRepositoryInterface $attributeRepository
    ) {
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $result = [];
        foreach ($this->getOptions() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $attributeRepository = $this->attributeRepository->getList(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteria
        );

        $optionsMetadata = [];

        foreach ($attributeRepository->getItems() as $item) {
            if ($item->getAttributeCode() == 'media_gallery') {
                continue;
            }
            $optionsMetadata[$item->getAttributeCode()] = $item->getFrontendLabel();
        }

        return $optionsMetadata;
    }
}
