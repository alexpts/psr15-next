<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use PTS\Events\EventsInterface;

trait EmitterTrait
{

	protected ?EventsInterface $events = null;

	public function setEvents(EventsInterface $events): void
	{
		$this->events = $events;
	}

	protected function emit(string $name, array $arguments): void
	{
		$this->events && $this->events->emit($name, $arguments);
	}
}
