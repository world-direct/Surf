<?php

namespace TYPO3\Surf\Command;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\FailedDeployment;
use TYPO3\Surf\Domain\Model\SimpleWorkflow;
use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Integration\FactoryAwareInterface;
use TYPO3\Surf\Integration\FactoryAwareTrait;

/**
 * Surf describe command
 */
class DescribeCommand extends Command implements FactoryAwareInterface
{
    use FactoryAwareTrait;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var array
     */
    protected $description = [];

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('describe')
             ->setDescription('Describes the flow for the given name')
             ->addArgument(
                 'deploymentName',
                 InputArgument::REQUIRED,
                 'The deployment name'
             )
             ->addOption(
                 'configurationPath',
                 null,
                 InputOption::VALUE_OPTIONAL,
                 'Path for deployment configuration files'
             );
    }

    /**
     * Prints configuration for the
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $configurationPath = $input->getOption('configurationPath');
        $deploymentName = $input->getArgument('deploymentName');
        $deployment = $this->factory->getDeployment($deploymentName, $configurationPath);
        $workflow = $deployment->getWorkflow();

        if (! $deployment instanceof FailedDeployment) {
            $this->description['deployment']['name'] = $deployment->getName();
            $this->description['workflow']['name'] = $workflow->getName();

            if ($workflow instanceof SimpleWorkflow) {
                $this->description['workflow']['rollback_enabled'] = $workflow->isEnableRollback();
            }

            $this->addNodesToDescription($deployment->getNodes());

            $this->addApplicationsToDescription($deployment->getApplications(), $deployment->getWorkflow());

            $output->write(Yaml::dump($this->description, 10, 2));
        }
    }

    /**
     * @param array $nodes
     */
    protected function addNodesToDescription($nodes)
    {
        foreach ($nodes as $node) {
            $this->description['nodes'][$node->getName()]['hostname'] = $node->getHostname();
        }
    }

    /**
     * Get configuration for each defined application
     *
     * @param array $applications
     * @param Workflow $workflow
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    protected function addApplicationsToDescription(array $applications, Workflow $workflow)
    {
        /** @var Application $application */
        foreach ($applications as $application) {
            $this->description['applications'][$application->getName()] = [
                'type' => get_class($application),
                'deployment_path' => $application->getDeploymentPath(),
                'options' => $application->getOptions(),
                'nodes' => $application->getNodes(),
            ];

            if ($workflow instanceof SimpleWorkflow) {
                $this->description['applications'][$application->getName()]['workflow'] = $this->getApplicationWorkflow(
                    $application->getName(),
                    $workflow->getStages(),
                    $workflow->getTasks()
                );
            }
        }
    }

    /**
     * Prints stages and contained tasks for given application
     *
     * @param string $applicationName
     * @param array $stages
     * @param array $tasks
     * @return array
     */
    protected function getApplicationWorkflow($applicationName, array $stages, array $tasks)
    {
        $workflow = array_fill_keys(array_values($stages), []);

        foreach ($stages as $stage) {
            foreach (['before', 'tasks', 'after'] as $stageStep) {
                $tasksInStage = array_merge(
                    (array)$tasks['stage'][$applicationName][$stage][$stageStep],
                    (array)$tasks['stage']['_'][$stage][$stageStep]
                );
                foreach ($tasksInStage as $order => $taskInStage) {
                    $workflow[$stage][$stageStep] = array_merge(
                        $this->getBeforeAfterTasks($tasks, $applicationName, $taskInStage, 'before'),
                        (array) $taskInStage,
                        $this->getBeforeAfterTasks($tasks, $applicationName, $taskInStage, 'after')
                    );
                }
            }
        }
        return $workflow;
    }

    /**
     * Print all tasks before or after a task
     *
     * @param array $tasks
     * @param string $applicationName
     * @param string $task
     * @param string $step
     * @return array
     */
    private function getBeforeAfterTasks(array $tasks, $applicationName, $task, $step)
    {
        $beforeAfterTasks = [];
        $applicationNames = [$applicationName, '_'];
        foreach ($applicationNames as $applicationName) {
            if (isset($tasks[$step][$applicationName][$task])) {
                foreach ($tasks[$step][$applicationName][$task] as $beforeAfterTask) {
                    $beforeAfterTasks[] = $beforeAfterTask;
                }
            }
        }
        return $beforeAfterTasks;
    }
}
