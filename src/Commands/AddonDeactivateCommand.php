<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Commands;

use Blackcube\Dboard\Services\AddonActivation;
use Blackcube\Dcore\Services\AddonService;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

/**
 * Console command deactivating an add-on after a confirmation: its migrations are reverted,
 * its tables and their data deleted, its permissions and OAuth2 clients removed.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
#[AsCommand(
    name: 'dboard:addonDeactivate',
    description: 'Deactivate an add-on: its tables and their data are deleted, its permissions removed',
)]
class AddonDeactivateCommand extends Command
{
    public function __construct(
        private readonly AddonService $addonService,
        private readonly AddonActivation $addonActivation,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'Add-on id (the one shown by dboard:addonList)');
    }

    protected function execute(InputInterface $input, OutputInterface $consoleOutput): int
    {
        $io = new SymfonyStyle($input, $consoleOutput);
        $id = (string) $input->getArgument('id');

        $status = ExitCode::UNSPECIFIED_ERROR;
        if ($this->addonService->isActive($id) === false) {
            $io->error('No active add-on for this id.');
        } elseif ($io->confirm('"' . $this->addonService->getName($id) . '" will be deactivated: its tables and their data are deleted, its permissions and OAuth2 clients removed. Continue?', false) === false) {
            $io->writeln('Nothing done.');
            $status = ExitCode::OK;
        } else {
            try {
                $this->addonActivation->deactivate($id);
                $io->success('Add-on "' . $id . '" deactivated.');
                $status = ExitCode::OK;
            } catch (RuntimeException $e) {
                $io->error($e->getMessage());
            }
        }

        return $status;
    }
}
