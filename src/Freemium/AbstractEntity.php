<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use Doctrine\Common\Inflector\Inflector;
use RuntimeException;

class AbstractEntity
{
    public function __call($name, $args)
    {
        $type = substr($name, 0, 3);
        $property = Inflector::tableize(substr($name, 3));

        if (property_exists($this, $property)) {
            if ($type === 'get') {
                return $this->$property;
            } elseif ($type === 'set') {
                return $this->$property = $args[0];
            }
        } else {
            throw new RuntimeException(sprintf('Property %s::%s does not exit', get_class($this), $name));
        }
    }

    public function setProperties(array $properties)
    {
        foreach ($properties as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }

    public function getProperties()
    {
        return get_object_vars($this);
    }

    public function bindProperties(array $properties)
    {
        foreach ($properties as $name => $value) {
            $setter = 'set'.Inflector::classify($name);
            if (is_callable(array($this, $setter))) {
                $this->$setter($value);
            }
        }
    }
}
