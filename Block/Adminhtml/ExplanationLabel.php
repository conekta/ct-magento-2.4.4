<?php

namespace Conekta\Payments\Block\Adminhtml;

use Magento\Framework\Data\Form\Element\{AbstractElement, CollectionFactory, Factory};
use Magento\Framework\Escaper;

/**
 * Class ExplanationLabel
 * @package Conekta\Payments\Block\Adminhtml
 */
class ExplanationLabel extends AbstractElement
{
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * @return string
     */
    public function getElementHtml(): string
    {
        return 'Select a maximum of 12 attributes in total from the following attribute lists';
    }
}
