<?php

namespace Elegant\Admin\Form\Field;

use Elegant\Admin\Form\NestedForm;
use Elegant\Admin\Widgets\Form as WidgetForm;

class Table extends HasMany
{
    /**
     * Table constructor.
     *
     * @param string $column
     * @param array  $arguments
     */
    public function __construct($column, $arguments = [])
    {
        $this->column = $column;

        if (count($arguments) == 1) {
            $this->label = $this->formatLabel();
            $this->builder = $arguments[0];
        }

        if (count($arguments) >= 2) {
            list($this->label, $this->builder) = $arguments;
        }

        if (array_key_exists('callForm', $arguments)) {
            $this->callForm = $arguments['callForm'];
        }
        if (array_key_exists('callRow', $arguments)) {
            $this->callRow = $arguments['callRow'];
        }
        if (array_key_exists('callColumn', $arguments)) {
            $this->callColumn = $arguments['callColumn'];
        }
    }

    /**
     * @return array
     */
    protected function buildRelatedForms()
    {
//        if (is_null($this->form)) {
//            return [];
//        }

        $forms = [];

        if ($values = old($this->column)) {
            foreach ($values as $key => $data) {
                if ($data[NestedForm::REMOVE_FLAG_NAME] == 1) {
                    continue;
                }

                $forms[$key] = $this->buildNestedForm($this->column, $this->builder, $key)->fill($data);
            }
        } else {
            foreach ($this->value ?? [] as $key => $data) {
                if (isset($data['pivot'])) {
                    $data = array_merge($data, $data['pivot']);
                }
                $forms[$key] = $this->buildNestedForm($this->column, $this->builder, $key)->fill($data);
            }
        }

        return $forms;
    }

    public function prepare($input)
    {
        $form = $this->buildNestedForm($this->column, $this->builder);

        $prepare = $form->prepare($input);

        return collect($prepare)->reject(function ($item) {
            return $item[NestedForm::REMOVE_FLAG_NAME] == 1;
        })->map(function ($item) {
            unset($item[NestedForm::REMOVE_FLAG_NAME]);

            return $item;
        })->toArray();
    }

    protected function getKeyName()
    {
        if (is_null($this->form)) {
            return;
        }

        return 'id';
    }

    protected function buildNestedForm($column, \Closure $builder, $key = null)
    {
        $form = new NestedForm($column);

        if ($this->form instanceof WidgetForm) {
            $form->setWidgetForm($this->form);
        } else {
            $form->setForm($this->form);
        }

        $form->setKey($key);

        call_user_func($builder, $form);

        $form->hidden(NestedForm::REMOVE_FLAG_NAME)->default(0)->addElementClass(NestedForm::REMOVE_FLAG_CLASS);

        return $form;
    }
}
