<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformCronBundle\Command;

use Cron\Cron;
use Cron\Executor\Executor;
use Cron\Resolver\ArrayResolver;
use EzSystems\EzPlatformCronBundle\Registry\CronJobsRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CronRunCommand extends Command
{
    /** @var \EzSystems\EzPlatformCronBundle\Registry\CronJobsRegistry */
    private $cronJobsRegistry;

    public function __construct(CronJobsRegistry $cronJobsRegistry, ?string $name = null)
    {
        parent::__construct($name);

        $this->cronJobsRegistry = $cronJobsRegistry;
    }

    protected function configure(): void
    {
        $this
            ->setDefinition([
                new InputOption('category', null, InputOption::VALUE_REQUIRED, 'Job category to run', 'default'),
            ])
            ->setName('ezplatform:cron:run')
            ->setDescription('Perform one-time cron tasks run.')
            ->setHelp(
                <<<EOT
It's not meant to be run manually, yet it's OK to do so as it still might be useful for development purpose.

Check documentation how to setup it be called automatically.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $category = $input->getOption('category');
        $cronJobs = $this->cronJobsRegistry->getCategoryCronJobs($category);

        $resolver = new ArrayResolver($cronJobs);

        $cron = new Cron();
        $cron->setExecutor(new Executor());
        $cron->setResolver($resolver);

        $cron->run();

        return 0;
    }
}
