<?php

namespace Potato\Pdf\Model\Source\System;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PrintMethod
 */
class PrintMethod implements OptionSourceInterface
{
    const USE_SERVICE   = 'service';
    const USE_SERVER_APP = 'server';

    /**
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::USE_SERVER_APP => __("Self-hosted server applications"),
            self::USE_SERVICE => __("PotatoCommerce PDF Service")
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->getOptionArray();
        $result = [];
        foreach ($options as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }
        return $result;
    }
}
