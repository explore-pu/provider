<?php

namespace Elegant\Admin\Controllers;

use Elegant\Admin\Facades\Admin;
use Elegant\Admin\Form;
use Elegant\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @var string
     */
    protected $loginView = 'admin::login';

    /**
     * Show the login page.
     *
     * @return \Illuminate\Contracts\View\Factory|Redirect|\Illuminate\View\View
     */
    public function getLogin()
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }

        return view($this->loginView);
    }

    /**
     * Handle a login request.
     *
     * @param Request $request
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function postLogin(Request $request)
    {
        $this->loginValidator($request->all())->validate();

        $credentials = $request->only([$this->username(), 'password']);
        $remember = $request->get('remember', false);

        if ($this->guard()->attempt($credentials, $remember)) {
            return $this->sendLoginResponse($request);
        }

        return back()->withInput()->withErrors([
            $this->username() => $this->getFailedLoginMessage(),
        ]);
    }

    /**
     * Get a validator for an incoming login request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function loginValidator(array $data)
    {
        return Validator::make($data, [
            $this->username()   => 'required',
            'password'          => 'required',
        ]);
    }

    /**
     * User logout.
     *
     * @return Redirect
     */
    public function getLogout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect(config('admin.route.prefix'));
    }

    /**
     * User setting page.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function getSetting(Content $content)
    {
        $form = $this->settingForm();
        $form->tools(
            function (Form\Tools $tools) {
                $tools->disableList();
                $tools->disableDestroy();
                $tools->disableView();
            }
        );

        return $content
            ->title(admin_trans('user_setting'))
            ->body($form->edit(Admin::user()->id));
    }

    /**
     * Update user setting.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putSetting()
    {
        return $this->settingForm()->update(Admin::user()->id);
    }

    /**
     * Model-form for user setting.
     *
     * @return Form
     */
    protected function settingForm()
    {
        $class = config('admin.database.users_model');

        $form = new Form(new $class());

        $form->display('username', admin_trans('username'));
        $form->text('name', admin_trans('name'))->rules('required');
        $form->image('avatar', admin_trans('avatar'));
        $form->password('password', admin_trans('password'))->rules('confirmed|required');
        $form->password('password_confirmation', admin_trans('password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->setAction(admin_url('self_setting'));

        $form->ignore(['password_confirmation']);

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        $form->saved(function () {
            admin_toastr(admin_trans('update_succeeded'));

            return redirect(admin_url('self_setting'));
        });

        return $form;
    }

    /**
     * @return array|\Illuminate\Contracts\Translation\Translator|string|null
     */
    protected function getFailedLoginMessage()
    {
        return Lang::has('auth.failed')
            ? trans('auth.failed')
            : 'These credentials do not match our records.';
    }

    /**
     * Get the post login redirect path.
     *
     * @return string
     */
    protected function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : config('admin.route.prefix');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        admin_toastr(admin_trans('login_successful'));

        $request->session()->regenerate();

        $this->authenticated($request, $this->guard()->user());

        return redirect()->intended($this->redirectPath());
    }

    protected function authenticated(Request $request, $user)
    {
        if (config('admin.operation_log.enable') === true) {
            $logModel = config('admin.database.logs_model');
            $input = $request->input();
            $input['password'] = '******';

            try {
                $logModel::create([
                    'user_id' => $user->id,
                    'operate' => admin_restore_route($request->route()->action['as']),
                    'path'    => substr(admin_restore_path($request->path()), 0, 255),
                    'method'  => $request->method(),
                    'ip'      => $request->getClientIp(),
                    'input'   => json_encode($input),
                ]);
            } catch (\Exception $exception) {
                // pass
            }
        }
    }


    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username()
    {
        return 'username';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Admin::guard();
    }
}
