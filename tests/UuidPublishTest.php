<?php

declare(strict_types=1);

namespace Tests;

use CodeIgniter\Test\Filters\CITestStreamFilter;
use Tests\Support\CLITestCase;

/**
 * @internal
 */
final class UuidPublishTest extends CLITestCase
{
    private string $configFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configFile = APPPATH . 'Config/Uuid.php';

        // Clean up any previously published files
        if (file_exists($this->configFile)) {
            unlink($this->configFile);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up published files
        if (file_exists($this->configFile)) {
            unlink($this->configFile);
        }
    }

    public function testRun(): void
    {
        CITestStreamFilter::registration();
        CITestStreamFilter::addOutputFilter();

        command('uuid:publish');

        $output = $this->parseOutput(CITestStreamFilter::$buffer);

        CITestStreamFilter::removeOutputFilter();

        $this->assertStringContainsString('Config Published!', $output);
        $this->assertStringContainsString('app/Config/Uuid.php', $output);
    }
}
