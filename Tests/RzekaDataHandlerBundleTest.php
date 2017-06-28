<?php
namespace Rzeka\DataHandlerBundle\Tests;

use PHPUnit\Framework\TestCase;
use Rzeka\DataHandlerBundle\RzekaDataHandlerBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ScriberCoreBundleTest extends TestCase
{
    public function testInstanceOfBundle()
    {
        $bundle = new RzekaDataHandlerBundle();

        static::assertInstanceOf(Bundle::class, $bundle);
    }
}
