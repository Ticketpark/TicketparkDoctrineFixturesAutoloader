# TicketparkDoctrineFixturesAutoloader

[![Build Status](https://travis-ci.org/Ticketpark/TicketparkDoctrineFixturesAutoloader.svg?branch=master)](https://travis-ci.org/Ticketpark/TicketparkDoctrineFixturesAutoloader)

This library simplifies loading Doctrine fixtures.

## Installation

Add TicketparkDoctrineFixturesAutoloader to your composer.json:

```js
{
    "require": {
        "ticketpark/doctrine-fixtures-autoloader"
    }
}
```

## Usage

``` php
<?php

namespace Acme\Bundle\SomeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ticketpark\Doctrine\DataFixtures\Autoloader\Autoloader;

class LoadCountryData extends AutoLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $data = array(
            array(
                '_reference'    => 'CH',
                'shortCode'     => 'CH',
                'name'          => 'Switzerland'
            ),
            array(
                '_reference'    => 'AT',
                'shortCode'     => 'AT',
                'name'          => 'Austria'
            ),
        );

        $this->autoload($data, $manager);
    }
}
```

In a second fixture class, references will be available based on the entity name and the optional `_reference` value:

``` php
<?php

namespace Acme\Bundle\SomeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ticketpark\Doctrine\DataFixtures\Autoloader\Autoloader;

class LoadUserData extends AutoLoader implements FixtureInterface, DependentFixtureInterface
{
    public function getDependencies()
    {
        return array(
            'Acme\Bundle\SomeBundle\DataFixtures\ORM\LoadCountryData'
        );
    }
    
    public function load(ObjectManager $manager)
    {
        $data = array(
            array(
                // The string `country_CH` references the element
                // created in the 'Country' entity with 'CH' as its
                // _reference value.
                'country' => $this->getReference('country_CH'),
                'name'    => 'Tom Swissman'
            )
        );

        $this->autoload($data, $manager);
    }
}
```


### Overwriting setter method names
In some cases you might want to override the setter methods. for instance because your method is named `addCurrency` instead of `addCurrencie` as the autoloader by default would asume. In this case, simply use the additional `setterMethods` parameter:

``` php
<?php

namespace Acme\Bundle\SomeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ticketpark\Doctrine\DataFixtures\Autoloader\Autoloader;

class LoadCurrencyData extends AutoLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $data = array(
            array(
                'currencies' => array(
                    'USD', 'EUR', 'CHF'
                )
            ),
        );

        $setterMethods = array(
            'currencies' => 'addCurrency'
        );

        $this->autoload($data, $manager, $setterMethods);
    }
}
```

### Treat arrays as singles
Another option to treat an array like a single element and inject the full array into a setter is by using the `$treatAsSingle` method parameter.

``` php
<?php

namespace Acme\Bundle\SomeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ticketpark\Doctrine\DataFixtures\Autoloader\Autoloader;

class LoadCurrencyData extends AutoLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $data = array(
            array(
                'currencies' => array(
                    'USD', 'EUR', 'CHF'
                )
            ),
        );

        // this will cause a call to setCurrencies() with the full currencies array
        $treatAsSingles = array('currencies');
        $this->autoload($data, $manager, array(), $treatAsSingles);
    }
}
```

### Provide class name
By default, the library tries to guess your entity namespace based on standard data fixture naming conventions. However, you can also define the namespace of your entity manually:

``` php
<?php

namespace Acme\Bundle\SomeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ticketpark\Doctrine\DataFixtures\Autoloader\Autoloader;

class LoadCountryData extends AutoLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $data = array(
            array(
                'shortCode'     => 'CH',
                'name'          => 'Switzerland'
            ),
        );
        $this->setEntityClass('My\Custom\Namespace\Country');
        $this->autoload($data, $manager);
    }
}
```

## License
This bundle is under the MIT license. See the complete license in the bundle:

    Resources/meta/LICENSE
