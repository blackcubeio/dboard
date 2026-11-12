<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Commands;

use Blackcube\Dcore\Services\AddonService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

/**
 * Console command listing the add-ons: the declared ones with their state, then the orphan
 * rows of the registry.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
#[AsCommand(
    name: 'dboard:addonList',
    description: 'List the add-ons and their state',
)]
class AddonListCommand extends Command
{
    public function __construct(
        private readonly AddonService $addonService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $consoleOutput): int
    {
        $io = new SymfonyStyle($input, $consoleOutput);

        $addonRows = [];
        foreach ($this->addonService->getDeclared() as $id => $class) {
            if ($this->addonService->isDeclared($id) === true) {
                $status = 'inactive';
                if ($this->addonService->isActive($id) === true) {
                    $status = $this->addonService->hasPendingMigrations($id) === true ? 'update available' : 'active';
                }
                $addonRows[] = [
                    $id,
                    $class::getName(),
                    $this->addonService->getInstalledVersion($id) ?? '-',
                    $status,
                ];
            }
        }
        foreach ($this->addonService->getOrphans() as $id) {
            $addonRows[] = [
                $id,
                $id,
                '-',
                'package missing',
            ];
        }

        $io->table([
            'id',
            'name',
            'version',
            'status',
        ], $addonRows);

        return ExitCode::OK;
    }
}
