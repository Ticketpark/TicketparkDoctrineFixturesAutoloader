# TicketparkFixturesAutoloadBundle

This Symfony2 bundle ads functionalities to simplify loading Doctrine fixtures.

## Todos
* Add unit tests
* Improve documentation

## Installation

Add TicketparkImageBundle in your composer.json:

```js
{
    "require": {
        "ticketpark/fixtures-autoload-bundle": "dev-master"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update ticketpark/fixtures-autoload-bundle
```

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Ticketpark\FixturesAutoloadBundle\TicketparkFixturesAutoloadBundle(),
    );
}
```
## Usage

``` php
<?php

namespace Acme\Bundle\SomeBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Ticketpark\FixturesAutoloadBundle\Autoloader\Autoloader;

class LoadCountryData extends AutoLoader
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $data = array(
            array(
                '_reference'    => '_CH',
                'shortCode'     => 'CH',
                'name'          => 'Switzerland'
            ),
            array(
                '_reference'    => '_AT',
                'shortCode'     => 'AT',
                'name'          => 'Austria'
            ),
        );

        $this->autoload($data, $manager);
    }
}
```


## License
This bundle is under the MIT license. See the complete license in the bundle:

    Resources/meta/LICENSE
