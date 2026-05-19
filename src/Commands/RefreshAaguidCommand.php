<?php

declare(strict_types=1);

/**
 * RefreshAaguidCommand.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Commands;

use Blackcube\Dboard\Services\PasskeyService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

#[AsCommand(
    name: 'dboard:refreshAaguid',
    description: 'Refresh passkey devices from AAGUID registry',
)]
final class RefreshAaguidCommand extends Command
{
    public function __construct(
        private readonly PasskeyService $passkeyService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Refreshing AAGUID registry');

        $this->passkeyService->cleanIcons();
        $io->text('Icons cleaned');

        $this->passkeyService->importAaguid();
        $io->success('AAGUID registry refreshed');

        return ExitCode::OK;
    }
}
