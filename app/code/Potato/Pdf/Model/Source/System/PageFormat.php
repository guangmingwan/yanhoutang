<?php

namespace Potato\Pdf\Model\Source\System;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PageFormat
 */
class PageFormat implements OptionSourceInterface
{
    const A4_VALUE   = 'A4';
    const LETTER_VALUE  = 'Letter';

    /**
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::A4_VALUE => __("A4"),
            self::LETTER_VALUE => __("Letter")
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
