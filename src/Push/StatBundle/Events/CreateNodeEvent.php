<?php
/**
 * Created by PhpStorm.
 * User: pzhuchkov
 * Date: 25.08.17
 * Time: 21:21
 */

namespace Push\StatBundle\Events;

/**
 * Class CreateNodeEvent
 *
 * @package Push\StatBundle\Events
 */
class CreateNodeEvent
{
    const EVENT = 'push.stat.create.node';

    /**
     * @var string
     */
    protected $name;

    /**
     * DeleteNodeEvent constructor.
     *
     * @param string $name name
     *
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * getName
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}