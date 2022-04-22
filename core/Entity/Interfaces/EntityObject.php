<?php

namespace Core\Entity;

use Core\DataTypes\Potential;
use Core\Utilities\Comparable;

/**
 * Interface EntityObject
 *
 * @package Core\Entity
 */
interface EntityObject extends Potential, Comparable
{

    /**
     * @return mixed
     */
    public function createEntity();

    /**
     * @return mixed
     */
    public function getEntity();

    /**
     * @return mixed
     */
    public function updateEntity();

    /**
     * @return mixed
     */
    public function deleteEntity();

    /**
     * @return mixed
     */
    public function get_as_json();
}
