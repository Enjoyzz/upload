<?php

declare(strict_types=1);


namespace Enjoys\Upload\Event;


use Psr\EventDispatcher\StoppableEventInterface;

abstract class AbstractUploadEvent implements StoppableEventInterface
{
    private bool $propagationStopped = false;

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }
}
