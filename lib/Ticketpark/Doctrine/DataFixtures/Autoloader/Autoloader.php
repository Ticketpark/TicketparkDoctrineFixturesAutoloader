<?php

namespace Ticketpark\Doctrine\DataFixtures\Autoloader;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

abstract class Autoloader extends Fixture
{
    /**
     * @var string
     *
     * References will be prefixed with this string.
     * If null, the reference prefix will automatically determined by the classname of the fixture loader class.
     * Example: Called from 'Acme\Bundle\SomeBundle\DataFixtures\ORM\LoadEventData' the prefix will become 'event_'
     */
    protected $referencePrefix = null;

    /**
     * @var string
     *
     * The namespace of the class the data will be loaded with.
     * If null, the reference prefix will automatically be guessed by the classname of the fixture loader class.
     * Example: Called from 'Acme\Bundle\SomeBundle\DataFixtures\ORM\LoadEventData' the entityClass will
     * become 'Acme\Bundle\SomeBundle\Entity\Event'
     */
    protected $entityClass = null;

    /**
     * Loads fixtures from an array
     *
     * @param array $data
     * Contains the data to be added to database in key => value format.
     * Expects setter/adder for key to be existent. Non-existent keys will be considered as having NULL values.
     * Add the optional `_reference` key in order to create a reference for use in further fixtures.
     * The `_reference` string will be prefixed with $this->getReferencePrefix() and serve as reference label.
     *
     * <code>
     * $data = array(
     *      array(
     *          '_reference' => 'foo',
     *          'name'       => 'My Name',
     *          'prices'     => array(25, 30, 55),
     *      ),
     *      array(
     *          '_reference' => 'foo',
     *          'name'       => 'My Name',
     *          'prices'     => array(25, 30, 55),
     *      ),
     * );
     * </code>
     *
     * @param ObjectManager $manager
     * @param array $setterMethods
     * @param array $treatAsSingle
     */
    public function autoload(array $data, ObjectManager $manager, array $setterMethods = null, array $treatAsSingle = array())
    {
        $entityClass = $this->getEntityClass();
        $referencePrefix = $this->getReferencePrefix();

        if (!class_exists($entityClass)) {
            if ($this->entityClass){
                throw new \Exception(sprintf(
                    'The class "%s" does not exist. Maybe you misspelled it in your setEntityClass() call.',
                    $entityClass
                ));
            } else {
                throw new \Exception(sprintf(
                    'The class "%s" does not exist or could not be guessed correctly. You might have to define the the entity class with setEntityClass().',
                    $entityClass
                ));
            }
        }

        $needsFlush = false;
        foreach($data as $properties){

            $entity = new $entityClass;
            foreach ($properties as $propertyName => $propertyValues) {

                if ($propertyName == '_reference') {
                    continue;
                }

                if (is_array($propertyValues) && !in_array($propertyName, $treatAsSingle)) {
                    // Example: turns value 'prices' into 'addPrice'
                    $setterMethod = 'add'.ucfirst(substr($propertyName, 0, -1));

                } else {
                    // Example: turns value 'event' into 'setEvent'
                    $setterMethod = 'set'.ucfirst($propertyName);
                    $propertyValues = array($propertyValues);
                }

                // Override the guessed setter method name with a custom name if provided
                if (is_array($setterMethods) && array_key_exists($propertyName, $setterMethods)) {
                    $setterMethod = $setterMethods[$propertyName];
                }

                if (!is_callable(array($entity, $setterMethod))) {
                    throw new \Exception('Inexistent method: '.$entityClass.'->'.$setterMethod.'()');
                }

                foreach($propertyValues as $value){
                    $entity->$setterMethod($value);
                }
            }

            if (isset($properties['_reference'])) {
                $this->addReference($referencePrefix.$properties['_reference'], $entity);
            }

            $manager->persist($entity);
            $needsFlush = true;
        }

        if ($needsFlush) {
            $manager->flush();
        }

    }

    /**
     * Set reference prefix
     *
     * @param $referencePrefix
     * @return $this
     */
    public function setReferencePrefix($referencePrefix)
    {
        $this->referencePrefix = $referencePrefix;

        return $this;
    }

    /**
     * Get preference prefix
     *
     * @return string
     */
    public function getReferencePrefix()
    {
        if ($this->referencePrefix === null) {

            $prefix = $this->getEntityName();
            $prefix = strtolower($prefix);

            return  $prefix.'_';
        }

        return $this->referencePrefix;
    }

    /**
     * Set entity class
     *
     * @param $entityClass
     * @return $this
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * Get entity class
     *
     * @return string
     */
    protected function getEntityClass()
    {
        // Guess the entity class if it is not explicitly set
        if ($this->entityClass === null) {
            $reflection = new \ReflectionClass(get_called_class());
            $entityClass = str_replace('DataFixtures\ORM', 'Entity', $reflection->getNamespaceName()) . '\\' . $this->getEntityName();

            return $entityClass;
        }

        return $this->entityClass;
    }

    /**
     * Returns entity name
     *
     * @return string
     */
    protected function getEntityName()
    {
        $reflection = new \ReflectionClass(get_called_class());

        $entityName = $reflection->getShortName();
        $entityName = preg_replace('/^Load/', '', $entityName);
        $entityName = preg_replace('/Data$/', '', $entityName);

        return $entityName;
    }
}
