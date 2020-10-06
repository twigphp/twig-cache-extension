<?php

/*
 * This file is part of twig-cache-extension.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Twig\CacheExtension\Tests\CacheStrategy;

use Twig\CacheExtension\CacheStrategy\GenerationalCacheStrategy;

class GenerationalCacheStrategyTest extends \PHPUnit_Framework_TestCase
{
    private $keyGeneratorMock;
    private $cacheProviderMock;

    public function createCacheStrategy($lifetime = 0)
    {
        $this->keyGeneratorMock  = $this->createKeyGeneratorMock();
        $this->cacheProviderMock = $this->createCacheProviderMock();

        return new GenerationalCacheStrategy($this->cacheProviderMock, $this->keyGeneratorMock, $lifetime);
    }

    public function testGenerateKeyContainsAnnotation()
    {
        $strategy = $this->createCacheStrategy();
        $this->keyGeneratorMock->expects($this->any())
            ->method('generateKey')
            ->will($this->returnValue('foo'));

        $this->assertEquals('v42__GCS__foo', $strategy->generateKey('v42', 'value'));
    }

    /**
     * @expectedException \Twig\CacheExtension\Exception\InvalidCacheKeyException
     */
    public function testGenerationKeyThrowsExceptionWhenKeyGeneratorReturnsNull()
    {
        $strategy = $this->createCacheStrategy();

        $strategy->generateKey('v42', 'value');
    }

    /**
     * @dataProvider getLifeTimes
     */
    public function testSaveBlockUsesConfiguredLifetime($lifetime)
    {
        $strategy = $this->createCacheStrategy($lifetime);
        $this->cacheProviderMock->expects($this->any())
            ->method('save')
            ->with('key', 'value', $lifetime)
            ->will($this->returnValue('foo'));

        $strategy->saveBlock('key', 'value');
    }

    public function getLifetimes()
    {
        return array(
            array(0),
            array(60),
        );
    }

    public function createKeyGeneratorMock()
    {
        return $this->getMockBuilder('Twig\CacheExtension\CacheStrategy\KeyGeneratorInterface')->getMock();
    }

    public function createCacheProviderMock()
    {
        return $this->getMockBuilder('Twig\CacheExtension\CacheProviderInterface')->getMock();
    }
}
