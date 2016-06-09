<?php
namespace TYPO3\Flow\Cache\Tests\Unit\Backend;

/*
 * This file is part of the Neos.Cache package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Cache\EnvironmentConfiguration;
use TYPO3\Flow\Cache\Tests\BaseTestCase;

/**
 * Testcase for the Transient Memory Backend
 *
 */
class TransientMemoryBackendTest extends BaseTestCase
{
    /**
     * @expectedException \TYPO3\Flow\Cache\Exception
     * @test
     */
    public function setThrowsExceptionIfNoFrontEndHasBeenSet()
    {
        $backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend($this->getEnvironmentConfiguration());

        $data = 'Some data';
        $identifier = 'MyIdentifier';
        $backend->set($identifier, $data);
    }

    /**
     * @test
     */
    public function itIsPossibleToSetAndCheckExistenceInCache()
    {
        $cache = $this->getMock(\TYPO3\Flow\Cache\Frontend\FrontendInterface::class, array(), array(), '', false);
        $backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend($this->getEnvironmentConfiguration());
        $backend->setCache($cache);

        $data = 'Some data';
        $identifier = 'MyIdentifier';
        $backend->set($identifier, $data);
        $inCache = $backend->has($identifier);
        $this->assertTrue($inCache);
    }

    /**
     * @test
     */
    public function itIsPossibleToSetAndGetEntry()
    {
        $cache = $this->getMock(\TYPO3\Flow\Cache\Frontend\FrontendInterface::class, array(), array(), '', false);
        $backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend($this->getEnvironmentConfiguration());
        $backend->setCache($cache);

        $data = 'Some data';
        $identifier = 'MyIdentifier';
        $backend->set($identifier, $data);
        $fetchedData = $backend->get($identifier);
        $this->assertEquals($data, $fetchedData);
    }

    /**
     * @test
     */
    public function itIsPossibleToRemoveEntryFromCache()
    {
        $cache = $this->getMock(\TYPO3\Flow\Cache\Frontend\FrontendInterface::class, array(), array(), '', false);
        $backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend($this->getEnvironmentConfiguration());
        $backend->setCache($cache);

        $data = 'Some data';
        $identifier = 'MyIdentifier';
        $backend->set($identifier, $data);
        $backend->remove($identifier);
        $inCache = $backend->has($identifier);
        $this->assertFalse($inCache);
    }

    /**
     * @test
     */
    public function itIsPossibleToOverwriteAnEntryInTheCache()
    {
        $cache = $this->getMock(\TYPO3\Flow\Cache\Frontend\FrontendInterface::class, array(), array(), '', false);
        $backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend($this->getEnvironmentConfiguration());
        $backend->setCache($cache);

        $data = 'Some data';
        $identifier = 'MyIdentifier';
        $backend->set($identifier, $data);
        $otherData = 'some other data';
        $backend->set($identifier, $otherData);
        $fetchedData = $backend->get($identifier);
        $this->assertEquals($otherData, $fetchedData);
    }

    /**
     * @test
     */
    public function findIdentifiersByTagFindsCacheEntriesWithSpecifiedTag()
    {
        $cache = $this->getMock(\TYPO3\Flow\Cache\Frontend\FrontendInterface::class, array(), array(), '', false);
        $backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend($this->getEnvironmentConfiguration());
        $backend->setCache($cache);

        $data = 'Some data';
        $entryIdentifier = 'MyIdentifier';
        $backend->set($entryIdentifier, $data, array('UnitTestTag%tag1', 'UnitTestTag%tag2'));

        $retrieved = $backend->findIdentifiersByTag('UnitTestTag%tag1');
        $this->assertEquals($entryIdentifier, $retrieved[0]);

        $retrieved = $backend->findIdentifiersByTag('UnitTestTag%tag2');
        $this->assertEquals($entryIdentifier, $retrieved[0]);
    }

    /**
     * @test
     */
    public function hasReturnsFalseIfTheEntryDoesntExist()
    {
        $cache = $this->getMock(\TYPO3\Flow\Cache\Frontend\FrontendInterface::class, array(), array(), '', false);
        $backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend($this->getEnvironmentConfiguration());
        $backend->setCache($cache);

        $identifier = 'NonExistingIdentifier';
        $inCache = $backend->has($identifier);
        $this->assertFalse($inCache);
    }

    /**
     * @test
     */
    public function removeReturnsFalseIfTheEntryDoesntExist()
    {
        $cache = $this->getMock(\TYPO3\Flow\Cache\Frontend\FrontendInterface::class, array(), array(), '', false);
        $backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend($this->getEnvironmentConfiguration());
        $backend->setCache($cache);

        $identifier = 'NonExistingIdentifier';
        $inCache = $backend->remove($identifier);
        $this->assertFalse($inCache);
    }

    /**
     * @test
     */
    public function flushByTagRemovesCacheEntriesWithSpecifiedTag()
    {
        $cache = $this->getMock(\TYPO3\Flow\Cache\Frontend\FrontendInterface::class, array(), array(), '', false);
        $backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend($this->getEnvironmentConfiguration());
        $backend->setCache($cache);

        $data = 'some data' . microtime();
        $backend->set('TransientMemoryBackendTest1', $data, array('UnitTestTag%test', 'UnitTestTag%boring'));
        $backend->set('TransientMemoryBackendTest2', $data, array('UnitTestTag%test', 'UnitTestTag%special'));
        $backend->set('TransientMemoryBackendTest3', $data, array('UnitTestTag%test'));

        $backend->flushByTag('UnitTestTag%special');

        $this->assertTrue($backend->has('TransientMemoryBackendTest1'), 'TransientMemoryBackendTest1');
        $this->assertFalse($backend->has('TransientMemoryBackendTest2'), 'TransientMemoryBackendTest2');
        $this->assertTrue($backend->has('TransientMemoryBackendTest3'), 'TransientMemoryBackendTest3');
    }

    /**
     * @test
     */
    public function flushRemovesAllCacheEntries()
    {
        $cache = $this->getMock(\TYPO3\Flow\Cache\Frontend\FrontendInterface::class, array(), array(), '', false);
        $backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend($this->getEnvironmentConfiguration());
        $backend->setCache($cache);

        $data = 'some data' . microtime();
        $backend->set('TransientMemoryBackendTest1', $data);
        $backend->set('TransientMemoryBackendTest2', $data);
        $backend->set('TransientMemoryBackendTest3', $data);

        $backend->flush();

        $this->assertFalse($backend->has('TransientMemoryBackendTest1'), 'TransientMemoryBackendTest1');
        $this->assertFalse($backend->has('TransientMemoryBackendTest2'), 'TransientMemoryBackendTest2');
        $this->assertFalse($backend->has('TransientMemoryBackendTest3'), 'TransientMemoryBackendTest3');
    }

    /**
     * @return EnvironmentConfiguration|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getEnvironmentConfiguration()
    {
        return $this->getMock(EnvironmentConfiguration::class, null, [
            __DIR__,
            'Testing',
            'vfs://Foo/',
            255
        ], '');
    }
}
