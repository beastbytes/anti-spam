<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\AntiSpam\Assets;

use Yiisoft\Assets\AssetBundle;

final class HashAssetBundle extends AssetBundle
{
    public array $js = ['md5-min.js'];
    public ?string $sourcePath = __DIR__ . '/assets';
}
