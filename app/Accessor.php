<?php

/**
 * Class for accessing items in slim container.
 *
 * @author Leo Rudin
 */

namespace App;

class Accessor
{
    /**
     * Contains slim container for accessing dependencies.
     *
     * @var \Slim\Container
     */
    protected $container;

    /**
     * Creates instance and assigns slim container.
     *
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Magic function for accessing slim container with a preferred
     * property.
     *
     * @param  $property
     * @return mixed
     */
    public function __get($property)
    {
        if ($this->container->{$property}) {
            return $this->container->{$property};
        }
    }
}