<?php

namespace Conekta\Payments\Model\Source;

use Magento\Payment\Model\Source\Cctype as MCctype;

class Cctype extends MCctype
{
    /**
     * @return array
     */
    public function getAllowedTypes(): array
    {
        return [
            'VI', 'MC', 'AE'
        ];
    }
}
