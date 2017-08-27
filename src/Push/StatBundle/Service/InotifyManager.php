<?php
/**
 * Created by PhpStorm.
 * User: pzhuchkov
 * Date: 25.08.17
 * Time: 21:17
 */

namespace Push\StatBundle\Service;

use Mmoreram\RSQueueBundle\Services\Producer;
use Monolog\Logger;

/**
 * Class InotifyManager
 *
 * @package Push\StatBundle\Service
 */
class InotifyManager
{
    const Q = 'messages';
    /**
     * @var Logger
     */
    protected $l;

    /**
     * @var Producer
     */
    protected $p;

    /**
     * InotifyManager constructor.
     *
     * @param Producer $producer queue producer
     * @param Logger   $logger   logger
     */
    public function __construct(
        Producer $producer,
        Logger $logger
    ) {
        $this->l = $logger;
        $this->p = $producer;
    }

    /**
     * listen
     *
     * @param string $directory watch directory
     *
     * @return void
     */
    public function listen($directory)
    {
        $directory = rtrim($directory, '/');
        if (!file_exists($directory)) {
            throw new \RuntimeException(sprintf('Directory "%s" not found'));
        }

        $inoInst = inotify_init();

        stream_set_blocking($inoInst, 0);

        $watch_id = inotify_add_watch($inoInst, $directory, IN_CREATE | IN_DELETE);

        while (true) {

            $events = inotify_read($inoInst);

            if ($events[0]['wd'] === $watch_id) {
                $message = [
                    'path' => $directory . '/' . $events[0]['name'],
                    'mask' => $events[0]['mask'],
                ];
                $this->p->produce(
                    "messages",
                    json_encode($message)
                );

                $this->l->debug(
                    'INOTIFY',
                    [
                        'message' => $message,
                        'resource' => \PHP_Timer::resourceUsage(),
                    ]
                );
            }

        }

        inotify_rm_watch($inoInst, $watch_id);

        fclose($inoInst);
    }
}