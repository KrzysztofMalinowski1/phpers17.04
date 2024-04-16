<?php

declare(strict_types=1);

namespace App\Tests\functional\Helpers;

use Symfony\Component\Console\Tester\CommandTester;

abstract class DatabaseTest extends BaseApplicationTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildDatabase();
    }

    protected function tearDown(): void
    {
        $this->dropDatabase();
        parent::tearDown();
    }

    protected function rebuildDatabase(): void
    {
        $this->dropDatabase();

        $command = $this->application->find('doctrine:schema:create');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-q' => true]);

        $command = $this->application->find('doctrine:schema:update');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--force' => true]);

        $command = $this->application->find('doctrine:fixtures:load');
        $commandTester = new CommandTester($command);
        $commandTester->execute([], ['interactive' => false]);
    }

    protected function dropDatabase(): void
    {
        $command = $this->application->find('doctrine:schema:drop');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--force' => true, '-q' => true]);
    }
}
