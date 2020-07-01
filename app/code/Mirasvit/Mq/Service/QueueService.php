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
 * @package   mirasvit/module-message-queue
 * @version   1.0.10
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Mq\Service;

use Mirasvit\Mq\Api\Data\EnvelopeInterface;
use Mirasvit\Mq\Api\Repository\ConsumerRepositoryInterface;
use Mirasvit\Mq\Api\Service\QueueServiceInterface;
use Mirasvit\Mq\Repository\QueueRepository;

class QueueService implements QueueServiceInterface
{
    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @var ConsumerRepositoryInterface
     */
    private $consumerRepository;

    /**
     * @var EnvelopeEncoderService
     */
    private $encoderService;

    /**
     * QueueService constructor.
     * @param QueueRepository $queueRepository
     * @param ConsumerRepositoryInterface $consumerRepository
     * @param EnvelopeEncoderService $encoderService
     */
    public function __construct(
        QueueRepository $queueRepository,
        ConsumerRepositoryInterface $consumerRepository,
        EnvelopeEncoderService $encoderService
    ) {
        $this->queueRepository = $queueRepository;
        $this->consumerRepository = $consumerRepository;
        $this->encoderService = $encoderService;
    }

    /**
     * {@inheritdoc}
     */
    public function process($number)
    {
        $queue = $this->queueRepository->getProvider();
        while ($envelope = $queue->peek()) {
            /** @var EnvelopeInterface $envelope */
            $this->callback($envelope);

            $queue->acknowledge($envelope);

            if ($number-- == 0) {
                break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe($maxExecutionTime = 60)
    {
        $queue = $this->queueRepository->getProvider();

        $queue->subscribe([$this, 'callback'], $maxExecutionTime);
    }

    /**
     * @param EnvelopeInterface $envelope
     * @return void
     */
    public function callback(EnvelopeInterface $envelope)
    {
        $data = $this->encoderService->decode($envelope->getBody());
        $consumers = $this->consumerRepository->getByQueueName($envelope->getQueueName());

        foreach ($consumers as $consumer) {
            call_user_func($consumer->getCallback(), $data);
        }
    }
}
