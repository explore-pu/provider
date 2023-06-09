<?php

namespace Elegant\Admin\Form\Field;

use Elegant\Admin\Form\Field;
use Illuminate\Support\Arr;

class SwitchField extends Field
{
    protected static $css = [
        '/vendor/elegant-admin/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css',
    ];

    protected static $js = [
        '/vendor/elegant-admin/bootstrap-switch/dist/js/bootstrap-switch.min.js',
    ];

    protected $states = [
        'on'  => ['value' => 1, 'text' => 'ON', 'color' => 'primary'],
        'off' => ['value' => 0, 'text' => 'OFF', 'color' => 'default'],
    ];

    protected $size = 'small';

    /**
     * @var bool
     */
    protected $plugin = true;

    /**
     * @var string
     */
    protected $changeAfter = '';

    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    public function states($states = [])
    {
        foreach (Arr::dot($states) as $key => $state) {
            Arr::set($this->states, $key, $state);
        }

        return $this;
    }

    public function disablePlugin($plugin = false)
    {
        $this->plugin = $plugin;

        return $this;
    }

    public function prepare($value)
    {
        if (isset($this->states[$value])) {
            return $this->states[$value]['value'];
        }

        return $value;
    }

    public function changeAfter($script = '')
    {
        $this->changeAfter = $script;

        return $this;
    }

    public function render()
    {
        if (!$this->shouldRender()) {
            return '';
        }

        foreach ($this->states as $state => $option) {
            if ($this->value() == $option['value']) {
                $this->value = $state;
                break;
            }
        }

        if ($this->plugin) {
            $this->script = <<<EOT
$('{$this->getElementClassSelector()}.la_checkbox').bootstrapSwitch({
    size:'{$this->size}',
    onText: '{$this->states['on']['text']}',
    offText: '{$this->states['off']['text']}',
    onColor: '{$this->states['on']['color']}',
    offColor: '{$this->states['off']['color']}',
    handleWidth: "35",
    labelWidth: "2",
    onSwitchChange: function(event, state) {
        $(event.target).closest('.bootstrap-switch').next().val(state ? 'on' : 'off').change();
        {$this->changeAfter}
    }
});
EOT;
        } else {

            $this->script = <<<EOT
$('{$this->getElementClassSelector()}.la_checkbox').parents('td').css({padding: "14px 8px"});
$('{$this->getElementClassSelector()}.la_checkbox').click(function () {
    $(this).next().val(this.checked ? 'on' : 'off').change();
    {$this->changeAfter}
});
EOT;
        }

        return parent::render();
    }
}
