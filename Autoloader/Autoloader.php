<?php

namespace Ticketpark\FixturesAutoloadBundle\Autoloader;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Autoloader
 */
abstract class Autoloader extends AbstractFixture
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
     * If null, the reference prefix will automatically determined by the classname of the fixture loader class.
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
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     * @param array $setterMethods
     */
    protected function autoload(array $data, ObjectManager $manager, array $setterMethods = null, array $treatAsSingle = array())
    {
        foreach($data as $item){

            $entityClass = $this->getEntityClass();
            $entity = new $entityClass;

            foreach ($item as $key => $values) {

                if ($key == '_reference') {
                    continue;
                }

                if (is_array($values) && !in_array($key, $treatAsSingle)) {
                    //Example: turns value 'prices' into 'addPrice'
                    $func = 'add'.ucfirst(substr($key, 0, -1));

                } else {
                    //Example: turns value 'event' into 'setEvent'
                    $func = 'set'.ucfirst($key);
                    $values = array($values);
                }

                // overwrite the setter method, if exists
                if (null !== $setterMethods) {
                    if (array_key_exists($key, $setterMethods)) {
                        $func = $setterMethods[$key];
                    }
                }

                if (!method_exists($entity, $func)) {
                    throw new \Exception('Inexistent method: '.$entityClass.'->'.$func.'()');
                }

                foreach($values as $value){
                    call_user_func(array($entity, $func), $value);
                }
            }

            if (isset($item['_reference'])) {
                $this->addReference($this->getReferencePrefix().$item['_reference'], $entity);
            }

            $manager->persist($entity);

            //Flush after each element due to possible event listeners
            $manager->flush();
        }
    }

    /**
     * Get reference prefix
     */
    public function getReferencePrefix()
    {
        if ($this->referencePrefix === null) {

            $prefix = $this->getEntityClassName();
            $prefix = strtolower($prefix);

            return  $prefix.'_';
        }

        return $this->referencePrefix;
    }

    /**
     * Get entity class to actually load data with
     */
    public function getEntityClass()
    {
        if ($this->entityClass === null) {

            $calledClass   = get_called_class();
            $entityClass = preg_replace('/'.preg_quote('\Load').'/', '', $calledClass);
            $entityClass = preg_replace('/Data$/', '', $entityClass);
            $entityClass = str_replace('DataFixtures\ORM', 'Entity\\', $entityClass);

            return $entityClass;
        }

        return $this->entityClass;
    }

    /**
     * Returns Entity class name
     *
     * @return string
     */
    protected function getEntityClassName()
    {
        $classname = $this->extractClassnameFromNamespace();
        $entityClassName = preg_replace('/^Load/', '', $classname);
        $entityClassName = preg_replace('/Data$/', '', $entityClassName);

        return $entityClassName;
    }

    /**
     * Extracts and returns classname from namespace string
     *
     * @return string
     */
    protected function extractClassnameFromNamespace()
    {
        $calledClass = get_called_class();

        $parts = explode('\\', trim($calledClass));
        $className = array_pop($parts);

        if ('' == $className) {
            return false;
        }

        return $className;
    }
}