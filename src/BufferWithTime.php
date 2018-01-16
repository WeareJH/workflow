<?php

namespace Jh\Workflow;

use Rx\AsyncSchedulerInterface;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\Operator\OperatorInterface;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BufferWithTime implements OperatorInterface
{
    /**
     * @var int
     */
    private $milliSeconds;

    /**
     * @var AsyncSchedulerInterface
     */
    private $scheduler;

    /**
     * @var array
     */
    private $buffer = [];

    private $completed = false;

    public function __construct(int $milliSeconds, AsyncSchedulerInterface $scheduler)
    {
        $this->milliSeconds = $milliSeconds;
        $this->scheduler = $scheduler;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $action = function () use ($observer, &$action) {

            if ($this->completed) {
                $observer->onCompleted();
                return;
            }

            $observer->onNext($this->buffer);
            $this->buffer = [];
            $this->scheduler->schedule($action, $this->milliSeconds);
        };

        $this->scheduler->schedule($action, $this->milliSeconds);
        return $observable->subscribe(
            function ($x) {
                $this->buffer[] = $x;
            },
            [$observer, 'onError'],
            function () {
                $this->completed = true;
            }
        );
    }
}
