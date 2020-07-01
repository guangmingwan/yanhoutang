<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-email-designer
 * @version   1.1.42
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Filter;

class NumberFilter
{
    /**
     * Number Format
     *
     * Format
     *
     * @param string $input
     * @param int    $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     * @return string
     */
    public function number_format($input, $decimals = 0, $decPoint = '.', $thousandsSep = ',')
    {
        return number_format((float)$input, $decimals, $decPoint, $thousandsSep);
    }
}
