<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\AntiSpam\Form;

use Yiisoft\Form\Field\Base\InputField;
use Yiisoft\Html\Html;
use Yiisoft\View\WebView;

use function md5;

final class HashInput extends InputField
{
    public function __construct(protected WebView $view)
    {
    }

    protected function generateInput(): string
    {
        $this->registerScript();

        $input = Html::textInput(
            $this->getInputName(),
            (string) $this->getFormAttributeValue(),
            $this->getInputAttributes()
        )
             ->render()
        ;

        $hidden = $this
            ->addInputContainerAttributes(['style' => 'display:none;'])
            ->hideLabel(false)
        ;
        $hiddenInput = Html::textInput(
            md5($hidden->getInputName())
        )
            ->render()
        ;

        return $input . $hiddenInput;
    }

    private function registerScript(): void
    {
        $inputId = $this->getInputId();
        $hiddenInputId = md5($this->getInputId());

        $js = <<<JS
        document.getElementById('$inputId').onblur = function() {
          document.getElementById('$hiddenInputId').value = hex_md5(document.getElementById('$inputId').value);
        };
        JS;

        $this
            ->view
            ->registerJs($js)
        ;
    }
}
