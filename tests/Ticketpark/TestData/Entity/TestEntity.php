<?php

namespace Ticketpark\Tests\TestData\Entity;

class TestEntity
{
    public function setName($name){}

    public function addRole($role){}

    public function addCurrency($currency){}

    public function customNewFriendSetter($friend){}

    public function setPets(array $pets){}

    public function __call($method, $params){}
}