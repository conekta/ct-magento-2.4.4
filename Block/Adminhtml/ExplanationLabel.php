<?php

namespace Conekta\Payments\Block\Adminhtml;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

/**
 * Class ExplanationLabel
 */
class ExplanationLabel extends AbstractElement
{
    /**
     * ExplanationLabel construct
     *
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * Get element html
     *
     * @return string
     */
    public function getElementHtml(): string
    {
        return 'Select a maximum of 12 attributes in total from the following attribute lists';
    }
}
