<?php

namespace Elegant\Admin\Controllers;

use Elegant\Admin\Grid;
use Illuminate\Support\Arr;

class LogController extends AdminController
{
    /**
     * @return array|\Illuminate\Contracts\Translation\Translator|string|null
     */
    protected function title()
    {
        return admin_trans('auth_logs');
    }

    /**
     * @return \Illuminate\Config\Repository|mixed|string
     */
    protected function model()
    {
        return config('admin.database.logs_model');
    }

    /**
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new $this->model());
        $grid->model()->orderByDesc('id');

        $grid->column('id', 'ID')->sortable();
        $grid->column('user.name', admin_trans('user'));
        $grid->column('operate', admin_trans('operate'))->display(function ($operation) {
            return admin_route_trans($operation);
        });
        $grid->column('method', admin_trans('method'))->display(function ($method) {
            $methodColors = ['GET' => 'green', 'POST' => 'yellow', 'PUT' => 'blue', 'DELETE' => 'red'];
            $color = Arr::get($methodColors, $method, 'grey');
            return '<span class="badge bg-' . $color . '">' . $method . '</span>';
        });
        $grid->column('path', admin_trans('path'))->label('info');
        $grid->column('ip', admin_trans('ip'))->label('primary');
        $grid->column('input', admin_trans('input'))->display(function () {
            return admin_trans('view');
        })->modal(admin_trans('view') . admin_trans('input'), function ($modal) {
            $input = json_decode($modal->input, true);
            $input = Arr::except($input, ['_pjax', '_token', '_method', '_previous_']);
            if (empty($input)) {
                return '<pre>{}</pre>';
            }

            return '<pre>'.json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).'</pre>';
        });

        $grid->column('created_at', admin_trans('created_at'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableEdit();
            $actions->disableView();
        });

        $grid->disableCreateButton();

        $grid->filter(function (Grid\Filter $filter) {
            $userModel = config('admin.database.users_model');
            $methods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH', 'LINK', 'UNLINK', 'COPY', 'HEAD', 'PURGE'];

            $filter->equal('user_id', admin_trans('user'))->select($userModel::pluck('name', 'id'));
            $filter->equal('method', admin_trans('method'))->select(array_combine($methods, $methods));
            $filter->like('path', admin_trans('path'));
            $filter->equal('ip', admin_trans('ip'));
        });

        return $grid;
    }
}
