<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterUuid\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Publisher\Publisher;
use Throwable;

class UuidPublish extends BaseCommand
{
    protected $group       = 'UUID';
    protected $name        = 'uuid:publish';
    protected $description = 'Publish UUID config file.';

    public function run(array $params)
    {
        $source = service('autoloader')->getNamespace('Michalsn\\CodeIgniterUuid')[0];

        $publisher = new Publisher($source, APPPATH);

        try {
            $publisher->addPaths([
                'Config/Uuid.php',
            ])->merge(false);
        } catch (Throwable $e) {
            $this->showError($e);

            return;
        }

        // Update published config file
        foreach ($publisher->getPublished() as $file) {
            $publisher->replace(
                $file,
                [
                    'namespace Michalsn\\CodeIgniterUuid\\Config' => 'namespace Config',
                    'use CodeIgniter\\Config\\BaseConfig'         => 'use Michalsn\\CodeIgniterUuid\\Config\\Uuid as BaseUuid',
                    'class Uuid extends BaseConfig'               => 'class Uuid extends BaseUuid',
                ],
            );
        }

        CLI::write(CLI::color('  Config Published! ', 'green') . 'You can customize the configuration by editing the "app/Config/Uuid.php" file.');
        CLI::newLine();
    }
}
