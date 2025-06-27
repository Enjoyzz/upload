<?php

declare(strict_types=1);

namespace Enjoys\Upload\Event;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Base abstract class for all upload-related events
 *
 * Provides common functionality for event propagation control
 * according to PSR-14 (Event Dispatcher) standard.
 *
 * All concrete upload events should extend this class to maintain
 * consistent behavior across the upload event system.
 */
abstract class AbstractUploadEvent implements StoppableEventInterface
{
    /**
     * @var bool Flag indicating whether event propagation is stopped
     */
    private bool $propagationStopped = false;

    /**
     * Checks whether event propagation is stopped
     *
     * @return bool True if event propagation is stopped, false otherwise
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Stops the event propagation
     *
     * When called, prevents the event from being passed to additional listeners.
     * This is useful when a listener has handled the event and wants to prevent
     * other listeners from processing it further.
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }
}
