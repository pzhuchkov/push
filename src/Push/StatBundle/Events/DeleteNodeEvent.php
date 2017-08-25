<?php
/**
 * Created by PhpStorm.
 * User: pzhuchkov
 * Date: 25.08.17
 * Time: 21:21
 */

namespace Push\StatBundle\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class DeleteNodeEvent
 *
 * @package Push\StatBundle\Events
 */
class DeleteNodeEvent extends Event
{
    const EVENT = 'push.stat.delete.node';

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