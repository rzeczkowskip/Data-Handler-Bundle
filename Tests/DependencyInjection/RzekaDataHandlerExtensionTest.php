<?php
namespace Rzeka\DataHandlerBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Rzeka\DataHandlerBundle\DependencyInjection\RzekaDataHandlerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ScriberCoreExtensionTest extends TestCase
{
    public function testConfigurationLoading(): void
    {
        $filesToLoad = [
            'services.xml'
        ];

        $filesToLoadCallbacks = array_map(function ($file) {
            return static::callback(function ($v) use ($file) { return $this->callbackEndsWith($v, $file); });
        }, $filesToLoad);

        $container = $this->createMock(ContainerBuilder::class);

        $container
            ->expects(static::atLeastOnce())
            ->method('fileExists')
            ->with(...$filesToLoadCallbacks);

        $extension = new RzekaDataHandlerExtension();
        $extension->load([], $container);
    }

    private function callbackEndsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);
        if ($length === 0) {
            return false;
        }

        return (substr($haystack, -$length) === $needle);
    }
}
