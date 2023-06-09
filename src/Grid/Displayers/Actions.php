<?php

namespace Elegant\Admin\Grid\Displayers;

use Elegant\Admin\Admin;

class Actions extends AbstractDisplayer
{
    /**
     * @var array
     */
    protected $appends = [];

    /**
     * @var array
     */
    protected $prepends = [];

    /**
     * Default actions.
     *
     * @var array
     */
    protected $actions = ['view', 'edit', 'destroy'];

    /**
     * @var string
     */
    protected $resource;

    /**
     * Disable all actions.
     *
     * @var bool
     */
    protected $disableAll = false;

    /**
     * diy translate.
     *
     * @var array
     */
    protected $trans = [];

    /**
     * Append a action.
     *
     * @param $action
     *
     * @return $this
     */
    public function append($action)
    {
        array_push($this->appends, $action);

        return $this;
    }

    /**
     * Prepend a action.
     *
     * @param $action
     *
     * @return $this
     */
    public function prepend($action)
    {
        array_unshift($this->prepends, $action);

        return $this;
    }

    /**
     * Get route key name of current row.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        return $this->row->{$this->row->getRouteKeyName()};
    }

    /**
     * Disable edit.
     *
     * @return $this.
     */
    public function disableEdit(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->actions, 'edit');
        } elseif (!in_array('edit', $this->actions)) {
            array_push($this->actions, 'edit');
        }

        return $this;
    }

    /**
     * Disable view action.
     *
     * @return $this
     */
    public function disableView(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->actions, 'view');
        } elseif (!in_array('view', $this->actions)) {
            array_push($this->actions, 'view');
        }

        return $this;
    }

    /**
     * Disable destroy.
     *
     * @return $this.
     */
    public function disableDestroy(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->actions, 'destroy');
        } elseif (!in_array('destroy', $this->actions)) {
            array_push($this->actions, 'destroy');
        }

        return $this;
    }

    /**
     * Disable all actions.
     *
     * @return $this
     */
    public function disableAll()
    {
        $this->disableAll = true;

        return $this;
    }

    /**
     * Set resource of current resource.
     *
     * @param $resource
     *
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get resource of current resource.
     *
     * @return string
     */
    public function getResource()
    {
        return $this->resource ?: parent::getResource();
    }

    /**
     * {@inheritdoc}
     */
    public function display($callback = null)
    {
        if ($callback instanceof \Closure) {
            $callback->call($this, $this);
        }

        if ($this->disableAll) {
            return '';
        }

        $actions = $this->prepends;

        foreach ($this->actions as $action) {
            $method = 'render'.ucfirst($action);
            array_push($actions, $this->{$method}());
        }

        $actions = array_merge($actions, $this->appends);

        return implode('', $actions);
    }

    /**
     * Render view action.
     *
     * @return string
     */
    protected function renderView()
    {
        return <<<EOT
<a href="{$this->getResource()}/{$this->getRouteKey()}" class="{$this->grid->getGridRowName()}-view">
    <i class="fa fa-eye"></i>
</a>
EOT;
    }

    /**
     * Render edit action.
     *
     * @return string
     */
    protected function renderEdit()
    {
        return <<<EOT
<a href="{$this->getResource()}/{$this->getRouteKey()}/edit" class="{$this->grid->getGridRowName()}-edit">
    <i class="fa fa-edit"></i>
</a>
EOT;
    }

    /**
     * Render delete action.
     *
     * @return string
     */
    protected function renderDestroy()
    {
        $this->setupDeleteScript();

        return <<<EOT
<a href="javascript:void(0);" data-id="{$this->getKey()}" class="{$this->grid->getGridRowName()}-destroy">
    <i class="fa fa-trash"></i>
</a>
EOT;
    }

    protected function setupDeleteScript()
    {
        $trans = [
            'destroy_confirm' => admin_trans('destroy_confirm'),
            'confirm'        => admin_trans('confirm'),
            'cancel'         => admin_trans('cancel'),
        ];

        $trans = array_merge($trans, $this->trans);

        $script = <<<SCRIPT

$('.{$this->grid->getGridRowName()}-destroy').unbind('click').click(function() {

    var id = $(this).data('id');

    swal({
        title: "{$trans['destroy_confirm']}",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "{$trans['confirm']}",
        showLoaderOnConfirm: true,
        cancelButtonText: "{$trans['cancel']}",
        preConfirm: function() {
            return new Promise(function(resolve) {
                $.ajax({
                    method: 'post',
                    url: '{$this->getResource()}/' + id,
                    data: {
                        _method:'delete',
                        _token:LA.token,
                    },
                    success: function (data) {
                        $.pjax.reload('#pjax-container');

                        resolve(data);
                    }
                });
            });
        }
    }).then(function(result) {
        var data = result.value;
        if (typeof data === 'object') {
            if (data.status) {
                swal(data.message, '', 'success');
            } else {
                swal(data.message, '', 'error');
            }
        }
    });
});

SCRIPT;

        Admin::script($script);
    }

    /**
     * diy delete translate.
     *
     * @param $tans
     */
    public function setTrans($tans)
    {
        $this->trans = $tans;
    }
}
