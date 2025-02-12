<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\Surf\Task\VarnishBanTask;

class VarnishBanTaskTest extends BaseTaskTest
{
    /**
     * @var VarnishBanTask
     */
    protected $task;

    /**
     * @test
     */
    public function executeWithDefaultOptions(): void
    {
        $this->task->execute($this->node, $this->application, $this->deployment, []);
        $this->assertCommandExecuted("/\/usr\/bin\/varnishadm -S \/etc\/varnish\/secret -T 127.0.0.1:6082 ban.url '.*'/");
    }

    /**
     * @test
     */
    public function executeOverridingDefaultOptions(): void
    {
        $options = [
            'varnishadm' => 'varnishadm',
            'secretFile' => 'secretFile',
            'banUrl' => 'banUrl',
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("/varnishadm -S secretFile -T 127.0.0.1:6082 ban.url 'banUrl'/");
    }

    /**
     * @test
     */
    public function simulateWithDefaultOptions(): void
    {
        $this->task->simulate($this->node, $this->application, $this->deployment, []);
        $this->assertCommandExecuted("/\/usr\/bin\/varnishadm -S \/etc\/varnish\/secret -T 127.0.0.1:6082 status/");
    }

    /**
     * @test
     */
    public function simulateOverridingDefaultOptions(): void
    {
        $options = [
            'varnishadm' => 'varnishadm',
            'secretFile' => 'secretFile',
            'banUrl' => 'banUrl',
        ];
        $this->task->simulate($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('/varnishadm -S secretFile -T 127.0.0.1:6082 status/');
    }

    /**
     * @return VarnishBanTask
     */
    protected function createTask(): VarnishBanTask
    {
        return new VarnishBanTask();
    }
}
