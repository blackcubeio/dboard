<?php

declare(strict_types=1);

/**
 * PasskeyService.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Dboard\Models\PasskeyDevice;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;

final class PasskeyService
{
    private const IMG_ALIAS = '@dboard/Assets/Passkeys/Img';
    private const JSON_ALIAS = '@dboard/ExternalData/combined_aaguid.json';
    private const DATA_PREFIX = 'data:image/svg+xml;base64,';

    public function __construct(
        private LoggerInterface $logger,
        private Aliases $aliases,
    ) {}

    public function importAaguid(): void
    {
        $jsonPath = $this->aliases->get(self::JSON_ALIAS);
        $data = json_decode(file_get_contents($jsonPath), true);
        $imgDir = $this->aliases->get(self::IMG_ALIAS);

        if (!is_dir($imgDir)) {
            mkdir($imgDir, 0755, true);
        }

        foreach ($data as $aaguid => $entry) {
            $iconLight = false;
            $iconDark = false;

            if (!empty($entry['icon_light']) && str_starts_with($entry['icon_light'], self::DATA_PREFIX)) {
                $svg = base64_decode(substr($entry['icon_light'], strlen(self::DATA_PREFIX)));
                if ($svg !== false) {
                    file_put_contents($imgDir . '/' . $aaguid . '_light.svg', $svg);
                    $iconLight = true;
                }
            }

            if (!empty($entry['icon_dark']) && str_starts_with($entry['icon_dark'], self::DATA_PREFIX)) {
                $svg = base64_decode(substr($entry['icon_dark'], strlen(self::DATA_PREFIX)));
                if ($svg !== false) {
                    file_put_contents($imgDir . '/' . $aaguid . '_dark.svg', $svg);
                    $iconDark = true;
                }
            }

            $device = PasskeyDevice::query()->andWhere(['aaguid' => $aaguid])->one();
            if ($device === null) {
                $device = new PasskeyDevice();
                $device->setAaguid($aaguid);
            }
            $device->setName($entry['name']);
            $device->setIconLight($iconLight);
            $device->setIconDark($iconDark);
            $device->save();
        }

        $this->logger->info('Imported ' . count($data) . ' passkey devices from AAGUID registry');
    }

    public function cleanIcons(): void
    {
        $imgDir = $this->aliases->get(self::IMG_ALIAS);
        if (!is_dir($imgDir)) {
            return;
        }
        foreach (glob($imgDir . '/*.svg') as $file) {
            unlink($file);
        }
    }
}
