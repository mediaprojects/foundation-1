<?php namespace Orchestra\Foundation\Routing;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Orchestra\Support\Facades\Site;
use Orchestra\Foundation\Processor\PasswordBroker as PasswordBrokerProcessor;

class PasswordBrokerController extends AdminController
{
    /**
     * Construct Forgot Password Controller with some pre-define
     * configuration
     *
     * @param \Orchestra\Foundation\Processor\PasswordBroker   $processor
     */
    public function __construct(PasswordBrokerProcessor $processor)
    {
        $this->processor = $processor;

        parent::__construct();
    }

    /**
     * Setup controller filters.
     *
     * @return void
     */
    protected function setupFilters()
    {
        $this->beforeFilter('orchestra.guest');
        $this->beforeFilter('orchestra.csrf', array('only' => array('create')));
    }

    /**
     * Show Forgot Password Page where user can enter their current e-mail
     * address.
     *
     * GET (:orchestra)/forgot
     *
     * @return Response
     */
    public function index()
    {
        Site::set('title', trans('orchestra/foundation::title.forgot-password'));

        return View::make('orchestra/foundation::forgot.index');
    }

    /**
     * Validate requested e-mail address for password reset, we should first
     * send a URL where user need to visit before the system can actually
     * change the password on their behave.
     *
     * POST (:orchestra)/forgot
     *
     * @return Response
     */
    public function create()
    {
        return $this->processor->create($this, Input::all());
    }

    /**
     * Once user actually visit the reset my password page, we now should be
     * able to make the operation to create a new password.
     *
     * GET (:orchestra)/forgot/reset/(:hash)
     *
     * @param  string   $token
     * @return Response
     */
    public function show($token)
    {
        Site::set('title', trans('orchestra/foundation::title.reset-password'));

        return View::make('orchestra/foundation::forgot.reset')->with('token', $token);
    }

    /**
     * Create a new password for the user.
     *
     * POST (:orchestra)/forgot/reset
     *
     * @return Response
     */
    public function reset()
    {
        $input = Input::only('email', 'password', 'password_confirmation', 'token');

        return $this->processor->reset($this, $input);
    }

    /**
     * Response when request password failed on validation.
     *
     * @param  mixed   $validation
     * @return Response
     */
    public function requestValidationFailed($validation)
    {
        // If any of the validation is not properly formatted, we need
        // to tell it the the user. This might not be important but a
        // good practice to make sure all form use the same e-mail
        // address validation
        return $this->redirectWithErrors(handles('orchestra::forgot'), $validation);
    }

    /**
     * Response when request reset password failed.
     *
     * @param  string  $response
     * @return Response
     */
    public function createFailed($response)
    {
        $message = trans($response);

        return $this->redirectWithMessage(handles('orchestra::forgot'), $message, 'error');
    }

    /**
     * Response when request reset password succeed.
     *
     * @param  string  $response
     * @return Response
     */
    public function createSucceed($response)
    {
        $message = trans($response);

        return $this->redirectWithMessage(handles('orchestra::forgot'), $message);
    }

    /**
     * Response when reset password failed.
     *
     * @param  string  $response
     * @return Response
     */
    public function resetFailed($response)
    {
        $message = trans($response);
        $token   = Input::get('token');

        return $this->redirectWithMessage(handles("orchestra::forgot/reset/{$token}"), $message, 'error');
    }

    /**
     * Response when reset password succeed.
     *
     * @param  string  $response
     * @return Response
     */
    public function resetSucceed()
    {
        $message = trans('orchestra/foundation::response.account.password.update');

        return $this->redirectWithMessage(handles('orchestra::/'), $message);
    }
}
