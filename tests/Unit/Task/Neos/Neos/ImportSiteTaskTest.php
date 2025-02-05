<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\Neos\Neos;

use TYPO3\Surf\Application\Neos\Neos;
use TYPO3\Surf\Task\Neos\Neos\ImportSiteTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class ImportSiteTaskTest extends BaseTaskTest
{
    /**
     * @var ImportSiteTask
     */
    protected $task;

    /**
     * @var Neos
     */
    protected $application;

    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new Neos('TestApplication');
    }

    /**
     * @return ImportSiteTask
     */
    protected function createTask(): ImportSiteTask
    {
        return new ImportSiteTask();
    }

    /**
     * @test
     */
    public function useCorrectCommandPackageKeyForNeosWithFlowVersion4(): void
    {
        $this->useCorrectCommandPackageKey();
    }

    /**
     * @test
     */
    public function useCorrectCommandPackageKeyForNeosWithFlowVersion3(): void
    {
        $this->useCorrectCommandPackageKey('3.0', 'typo3.neos');
    }

    /**
     * @param string $version
     * @param string $commandPackageKey
     */
    public function useCorrectCommandPackageKey($version = '4.0', $commandPackageKey = 'neos.neos'): void
    {
        $this->application->setVersion($version);
        $options = [
            'sitePackageKey' => 'Test.Site'
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("./flow $commandPackageKey:site:import '--package-key' 'Test.Site'");
    }
}
