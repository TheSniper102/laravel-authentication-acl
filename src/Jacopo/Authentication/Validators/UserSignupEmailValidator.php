<?php  namespace Jacopo\Authentication\Validators; 
/**
 * Class UserSignupEmailValidator
 *
 * @author jacopo beschi jacopo@jacopobeschi.com
 */
use Illuminate\Support\MessageBag;
use Jacopo\Library\Validators\OverrideConnectionValidator;
use Jacopo\Authentication\Exceptions\UserNotFoundException;
use App, Session, Input, Config;

class UserSignupEmailValidator extends OverrideConnectionValidator
{
    public function validateEmail($attribute, $value, $parameters)
    {
        $repository = App::make('user_repository');
        try
        {
            $user = $repository->findByLogin($value);
        }
        catch(UserNotFoundException $e)
        {
            return true;
        }

        if($user->activated)
        {
            return false;
        }

        // if email confirmation is disabled we dont send email again
        if(! Config::get('authentication::email_confirmation') ) return false;

        // send email
        $this->resendConfirmationEmail($value);
        // set session message
        Session::flash('message', "We sent you again the mail confirmation. Please check your inbox.");
        return false;
    }

    /**
     * @param $value
     */
    protected function resendConfirmationEmail($value)
    {
        $data = Input::all();
        $data['password'] = 'Cannot decipher password, please use password recovery after if it\'s needed.';

        App::make('register_service')->sendRegistrationMailToClient($data);
    }
} 