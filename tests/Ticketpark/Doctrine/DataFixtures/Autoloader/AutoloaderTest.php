<?php

namespace Ticketpark\Tests\Doctrine\DataFixtures\Autoloader;

use Ticketpark\Tests\Doctrine\DataFixtures\Autoloader;
use Ticketpark\Tests\TestData\DataFixtures\ORM\LoadTestEntityData;

class AutoloaderTest extends \PHPUnit_Framework_TestCase
{
    public function testAutoloading()
    {
        $data = array(
            array(
                'name'       => 'Foo',
                'roles'      => array('foo'),
                'currencies' => array('CHF', 'EUR'),
                'friends'    => array('Anna', 'Ben'),
                'pets'       => array('Dog', 'Cat', 'Fish')
            ),
            array(
                'name' => 'Bar',
            ),
        );

        $setterMethods = array(
            'currencies' => 'addCurrency',
            'friends'    => 'customNewFriendSetter'
        );
        $treatAsSingles = array('pets');

        $autoloader = new LoadTestEntityData();
        $autoloader->setEntityClass('Ticketpark\Tests\TestData\Entity\TestEntity');
        $autoloader->autoload($data, $this->getObjectManagerMock(2, true), $setterMethods, $treatAsSingles);
    }

    public function testWithoutAnyData()
    {
        $autoloader = new LoadTestEntityData();
        $autoloader->setEntityClass('Ticketpark\Tests\TestData\Entity\TestEntity');
        $autoloader->autoload(array(), $this->getObjectManagerMock(0, false));
    }

    public function testGuessEntityClass()
    {
        $autoloader = new LoadTestEntityData();
        $autoloader->autoload(array(), $this->getObjectManagerMock(0, false));
    }

    /**
     * @expectedException \Exception
     */
    public function testWithInexistentEntityClass()
    {
        $autoloader = new LoadTestEntityData();
        $autoloader->setEntityClass('Foo\Bar');
        $autoloader->autoload(array(), $this->getObjectManagerMock(0, false));
    }

    public function getObjectManagerMock($numberOfPersists, $willFlush)
    {
        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $objectManager->expects($this->exactly($numberOfPersists))
            ->method('persist');

        $expected = $this->never();
        if ($willFlush) {$expected = $this->once();}
        $objectManager->expects($expected)
            ->method('flush');

        return $objectManager;
    }
}