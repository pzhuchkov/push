<?php

namespace Push\StatBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PushStatWatchCommand
 *
 * @package Push\StatBundle\Command
 */
class PushStatWatchCommand extends ContainerAwareCommand
{
    /**
     * getInotifyManager
     *
     * @return object|\Push\StatBundle\Service\InotifyManager
     */
    protected function getInotifyManager()
    {
        return $this->getContainer()->get('push.stat.service.inotify_manager');
    }

    /**
     * configure
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('push:stat:watch')
            ->setDescription('Команда следит за изменениеями в директории, и закидывает задание в редис')
            ->addArgument(
                'directory',
                InputArgument::OPTIONAL,
                'Директория за которой нужно следить'
            );
    }

    /**
     * execute
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        if (!file_exists($directory)) {
            throw new \RuntimeException(sprintf('Directory "%s" not found', $directory));
        }

        $output->writeln(
            sprintf('<info>Start watch "%s"</info>', $directory)
        );

        \PHP_Timer::start();

        $this->getInotifyManager()->listen($directory);

        $output->writeln(sprintf('<info>%</info>', \PHP_Timer::resourceUsage()));
    }

}
