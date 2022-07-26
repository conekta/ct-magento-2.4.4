<?php

namespace Conekta\Payments\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Sales\Model\ResourceModel\Order;

class MetadataOrder implements ArrayInterface
{
    /**
     * @param Order $orderResource
     */
    public function __construct(
        protected Order $orderResource
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
        $orderAttributes = array_keys($this->orderResource->getConnection()->describeTable('quote'));
        $optionsMetadata = [];

        foreach ($orderAttributes as $attr) {
            if ($attr == 'entity_id') {
                continue;
            }
            $label = ucwords(str_replace('_', ' ', $attr));
            $optionsMetadata[$attr] = $label;
        }

        return $optionsMetadata;
    }
}
