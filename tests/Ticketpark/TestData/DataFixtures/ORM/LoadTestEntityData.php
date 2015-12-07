<?php

namespace Ticketpark\Tests\TestData\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Ticketpark\Doctrine\DataFixtures\Autoloader\Autoloader;

class LoadTestEntityData extends Autoloader
{
    // This is just a dummy method to enable testing
    // of the abstract autoloader class
    public function load(ObjectManager $manager){}
}