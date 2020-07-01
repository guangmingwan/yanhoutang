<?php

namespace Potato\Pdf\Model\Source\System;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PageOrientation
 */
class PageOrientation implements OptionSourceInterface
{
    const PORTRAIT_VALUE   = 'Portrait';
    const LANDSCAPE_VALUE  = 'Landscape';

    /**
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::PORTRAIT_VALUE => __("Portrait"),
            self::LANDSCAPE_VALUE => __("Landscape")
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
