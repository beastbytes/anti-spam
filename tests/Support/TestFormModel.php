<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\AntiSpam\Tests\Support;

use Yiisoft\Form\FormModel;

class TestFormModel extends FormModel
{
    public string $email = '';
    public string $family_name = '';
    public string $givenName = '';
}
