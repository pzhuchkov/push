<?php

namespace Push\StatBundle\Command;

use Mmoreram\RSQueueBundle\Command\ConsumerCommand;
use Push\StatBundle\Service\InotifyManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PushStatUpdateCommand
 *
 * @package Push\StatBundle\Command
 */
class PushStatUpdateCommand extends ConsumerCommand
{
    /**
     * getLogger
     *
     * @return object|\Symfony\Bridge\Monolog\Logger
     */
    protected function getLogger()
    {
        return $this->getContainer()->get('logger');
    }

    /**
     * getWordManager
     *
     * @return object|\Push\StatBundle\Service\WordManager
     */
    protected function getWordManager()
    {
        return $this->getContainer()->get('push.stat.service.word_manager');
    }

    /**
     * configure
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('push:stat:update')
            ->setDescription('Команда получает задание из редиса, и обновляет статистику')
            ->addOption(
                'bulk',
                'b',
                InputOption::VALUE_OPTIONAL,
                'Кол-во вставок в базе',
                5000
            );

        parent::configure();
    }

    /**
     * Execute code.
     *
     * Each time new payload is consumed from queue, consume() method is called.
     * When iterations get the limit, process literaly dies
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @throws InvalidAliasException If any alias is not defined
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->define();

        $consumer = $this->getContainer()->get('rs_queue.consumer');
        $iterations = (int)$input->getOption('iterations');
        $timeout = (int)$input->getOption('timeout');
        $sleep = (int)$input->getOption('sleep');
        $iterationsDone = 0;
        $queuesAlias = array_keys($this->methods);

        if ($this->shuffleQueues()) {

            shuffle($queuesAlias);
        }

        while ($response = $consumer->consume($queuesAlias, $timeout)) {

            list($queueAlias, $payload) = $response;
            $method = $this->methods[$queueAlias];

            /**
             * All custom methods must have these parameters
             *
             * InputInterface  $input   An InputInterface instance
             * OutputInterface $output  An OutputInterface instance
             * Mixed           $payload Payload
             */
            $this->$method($input, $output, $payload);

            if (($iterations > 0) && (++$iterationsDone >= $iterations)) {

                break;
            }

            sleep($sleep);
        }
    }

    /**
     * Relates queue name with appropiated method
     */
    public function define()
    {
        $this->addQueue(InotifyManager::Q, 'consumeMessage');
    }

    /**
     * If many queues are defined, as Redis respects order of queues, you can shuffle them
     * just overwritting method shuffleQueues() and returning true
     *
     * @return boolean Shuffle before passing to Gearman
     */
    public function shuffleQueues()
    {
        return true;
    }

    /**
     * Consume method with retrieved queue value
     *
     * @param InputInterface  $input   An InputInterface instance
     * @param OutputInterface $output  An OutputInterface instance
     * @param Mixed           $payload Data retrieved and unserialized from queue
     */
    protected function consumeMessage(InputInterface $input, OutputInterface $output, $payload)
    {
        $this->getWordManager()->setBulk($input->getOption('bulk'));

        $payload = json_decode($payload, true);

        $this->getLogger()->debug(
            'PUSH_STAT_UPDATE',
            [
                'payload' => $payload,
                'resource' => \PHP_Timer::resourceUsage(),
            ]
        );

        switch ($payload['mask']) {
            case IN_CREATE:
                $this->getWordManager()->add($payload['path']);
                break;
            case IN_DELETE:
                $this->getWordManager()->remove($payload['path']);
                break;
            default:
                throw new \RuntimeException(
                    sprintf(
                        'Mask "%s" not defined',
                        $payload['mask']
                    )
                );
        }

        $this->getLogger()->debug(
            'PUSH_STAT_UPDATE',
            [
                'finish_parsing' => $payload,
                'resource' => \PHP_Timer::resourceUsage(),
            ]
        );

        $this->getContainer()->get('cache.app')->clear();
    }
}
