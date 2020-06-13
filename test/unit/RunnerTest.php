<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Runner;
use PTS\NextRouter\StoreLayers;
use PTS\Psr7\Response\JsonResponse;

class RunnerTest extends TestCase
{
	protected Runner $runner;

	protected function setUp(): void
	{
		parent::setUp();

		$this->runner = new Runner;
	}

	public function testGetLayers(): void
	{
		$store = new StoreLayers;
		$store->get('/', fn() => new JsonResponse(['ok' => 1]));
		$store->get('/', fn() => new JsonResponse(['ok' => 2]));

		$this->runner->setLayers($store->getLayers());
		$layers = $this->runner->getLayers();

		static::assertCount(2, $layers);
		static::assertSame($store->getLayers(), $layers);
	}

}