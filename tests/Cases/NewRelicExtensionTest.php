<?php

declare(strict_types = 1);

namespace ContributteTests\NewRelic\Cases;

use Contributte\NewRelic\Callbacks\OnErrorCallback;
use Contributte\NewRelic\Callbacks\OnRequestCallback;
use Contributte\NewRelic\DI\NewRelicExtension;
use ContributteTests\NewRelic\Mocks\ApplicationExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @TestCase
 */
final class NewRelicExtensionTest extends TestCase
{

	public function testExtension(): void
	{
		$loader = new ContainerLoader(TEMP_DIR, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addConfig([
				'newrelic' => [
					'enabled' => true,
					'appName' => 'YourApplicationName',
					'license' => 'yourLicenseCode',
					'actionKey' => 'action',
					'logLevel' => [
						'critical',
						'exception',
						'error',
					],
					'rum' => [
						'enabled' => 'true',
					],
					'transactionTracer' => [
						'enabled' => true,
						'detail' => 1,
						'recordSql' => 'obfuscated',
						'slowSql' => true,
						'threshold' => 'apdex_f',
						'stackTraceThreshold' => 500,
						'explainThreshold' => 500,
					],
					'errorCollector' => [
						'enabled' => true,
						'recordDatabaseErrors' => true,
					],
					'parameters' => [
						'capture' => false,
						'ignored' => [],
					],
					'custom' => [
						'parameters' => [
							'paramName' => 'paramValue',
						],
						'tracers' => [],
					],
				],
			]);
			$compiler->addExtension('application', new ApplicationExtension());
			$compiler->addExtension('newrelic', new NewRelicExtension());
		}, [getmypid(), 1]);

		/** @var Container $container */
		$container = new $class();

		Assert::count(1, $container->findByType(OnRequestCallback::class));
		Assert::count(1, $container->findByType(OnErrorCallback::class));
	}

}

(new NewRelicExtensionTest())->run();
