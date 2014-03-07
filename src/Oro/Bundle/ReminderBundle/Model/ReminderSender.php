<?php

namespace Oro\Bundle\ReminderBundle\Model;

use Oro\Bundle\ReminderBundle\Entity\Reminder;

/**
 * Sends reminders using delegate send processors.
 */
class ReminderSender implements ReminderSenderInterface
{
    /**
     * @var SendProcessorInterface[]
     */
    protected $processors;

    /**
     * @param SendProcessorInterface[] $processors
     */
    public function __construct(array $processors)
    {
        $this->processors = $processors;
    }

    public function send(Reminder $reminder)
    {
        foreach ($this->processors as $processor) {
            if ($processor->supports($reminder)) {
                $processor->process($reminder);
            }
        }
    }
}
