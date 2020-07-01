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
 * @package   mirasvit/module-event
 * @version   1.2.33
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\EventData;

use Magento\Framework\DataObject;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\EventData\Condition\ErrorCondition;
use Mirasvit\Event\Ui\Event\Source\ErrorLevel;

class ErrorData extends DataObject implements EventDataInterface
{
    const IDENTIFIER = 'error';

    /**
     * @var ErrorLevel
     */
    private $errorLevelSource;

    /**
     * ErrorData constructor.
     * @param ErrorLevel $errorLevelSource
     * @param array $data
     */
    public function __construct(
        ErrorLevel $errorLevelSource,
        array $data = []
    ) {
        parent::__construct($data);

        $this->errorLevelSource = $errorLevelSource;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return 'error';
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getLabel()
    {
        return __('Error');
    }

    /**
     * @return string
     */
    public function getConditionClass()
    {
        return ErrorCondition::class;
    }

    /**
     * @return array|\Mirasvit\Event\Api\Data\AttributeInterface[]
     */
    public function getAttributes()
    {
        return [
            'level'     => [
                'label'   => __('Level'),
                'type'    => self::ATTRIBUTE_TYPE_ENUM,
                'options' => $this->errorLevelSource->getOptions(),
            ],
            'message'   => [
                'label' => __('Message'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
            'backtrace' => [
                'label' => __('Backtrace'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
            'request_uri' => [
                'label' => __('Request URI'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
        ];
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->getData('level');
    }

    /**
     * @return mixed
     */
    public function getLevelLabel()
    {
        return $this->errorLevelSource->getOptions()[$this->getLevel()];
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->getData('message');
    }

    /**
     * @return mixed
     */
    public function getBacktrace()
    {
        return $this->getData('backtrace');
    }

    /**
     * @return mixed
     */
    public function getRequestUri()
    {
        return $this->getData('request_uri');
    }
}
