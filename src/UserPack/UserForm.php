<?php

namespace Tivins\UserPack;

use Tivins\Core\Http\HTTP;
use Tivins\Core\Msg;
use Tivins\Core\StringUtil;
use Tivins\I18n\I18n;

class UserForm
{
    private string $actionURI = '/';

    private ?I18n $i18n = null;
    private Msg  $msgLogin;
    private Msg  $msgRegister;

    public function __construct()
    {
        $this->msgLogin    = new Msg('user_login');
        $this->msgRegister = new Msg('user_register');
    }

    /**
     * @param I18n|null $i18n Null to deactivate.
     * @return $this
     */
    public function setTranslationModule(?I18n $i18n): static
    {
        $this->i18n = $i18n;
        return $this;
    }

    private function translate(string $key): string
    {
        return $this->i18n ? $this->i18n->get($key, $key) : $key;
    }

    public function loginCheck(UserModule $userModule, string $redirectionURI = '/')
    {
        if (($_POST['formId'] ?? '') != $this->getLoginFormId()) {
            return false;
        }
        $user = $userModule->getByCredentials($_POST['name'], $_POST['password']);
        if ($user) {
            UserSession::setID($user->id);
            $this->msgLogin->push('Success', Msg::Success);
        }
        else {
            /*
             * Force to slow the process.
             */
            sleep(2);
            $this->msgLogin->push($this->i18n->get('Invalid credentials'), Msg::Error);
        }
        HTTP::redirect($redirectionURI);
    }

    private function getLoginFormId(): string
    {
        return base64_encode(static::class . '.' . __function__);
    }

    public function setActionURI(string $uri): static
    {
        $this->actionURI = $uri;
        return $this;
    }

    public function login(array $options = []): string
    {
        $html = '<form method="post" action="' . $this->actionURI . '" class="login-form">';
        $html .= $this->msgLogin->get();
        $html .= '<input type="hidden" name="formId" value="' . StringUtil::html($this->getLoginFormId()) . '">';
        $html .= '<div class="field"><label for="login-form-name">'.StringUtil::html($this->translate('User name')).'</label><input id="login-form-name" type="text" required name="name"/></div>';
        $html .= '<div class="field">
                  <div class="d-flex">
                    <label for="login-form-password" class="flex-grow">'.StringUtil::html($this->translate('Password')).'</label>
                    <a href="/user/password" class="fs-80">'.StringUtil::html($this->translate('Forgot password?')).'</a>
                  </div>
                  <input id="login-form-password" required type="password" name="password"/>
              </div>';
        $html .= '<div class="field"><button type="submit">'.StringUtil::html($this->translate('Sign in')).'</button></div>';
        $html .= '</form>';
        return $html;
    }

    public function register(array $options = []): string
    {
        $catchaQuestion = $this->generateCaptcha();
        $html           = '<form method="post" action="' . $this->actionURI . '" class="register-form">';
        $html           .= $this->msgRegister->get();
        $html           .= '<input type="hidden" name="formId" value="' . StringUtil::html($this->getRegisterFormId()) . '">';
        $html           .= '<div class="field"><label for="login-form-name">Name</label><input id="login-form-name" type="text" required name="name"/></div>';
        $html           .= '<div class="field"><label for="login-form-email">Email</label><input id="login-form-email" type="email" required name="email"/></div>';
        $html           .= '<div class="field"><label for="login-form-password">Password</label><input id="login-form-password" required type="password" name="password"/></div>';
        $html           .= '<div class="field"><label for="login-form-password-confirm">Confirm password</label><input id="login-form-password-confirm" required type="password" name="password-confirm"/></div>';
        $html           .= '<div class="field"><label for="login-form-captcha">Anti-robot test : ' . StringUtil::html($catchaQuestion) . '</label><input id="login-form-captcha" type="text" required name="captcha"/></div>';
        $html           .= '<div class="field"><button type="submit">Sign up</button></div>';
        $html           .= '</form>';
        return $html;
    }

    /**
     * @return string The question to ask.
     */
    private function generateCaptcha(): string
    {
        $a                            = rand(1, 9);
        $b                            = rand(1, 9);
        $response                     = $a + $b;
        $_SESSION['userpack_captcha'] = $response;
        return "$a + $b =";
    }

    private function getRegisterFormId(): string
    {
        return base64_encode(static::class . '.' . __function__);
    }

    public function registerCheck(UserModule $userModule, string $redirectionURI = '/')
    {
        if (($_POST['formId'] ?? '') != $this->getRegisterFormId()) {
            return false;
        }

        if (empty($_SESSION['userpack_captcha']) || ($_POST['captcha'] ?? '*') != $_SESSION['userpack_captcha']) {
            $this->msgRegister->push('Captcha invalid', Msg::Error);
            HTTP::redirect($redirectionURI);
        }

        if ($_POST['password'] !== $_POST['password-confirm']) {
            $this->msgRegister->push('Passwords were different', Msg::Error);
            HTTP::redirect($redirectionURI);
        }

        $uid = $userModule->createUser($_POST['name'], $_POST['email'], $_POST['password']);
        if ($uid) {
            $this->msgRegister->push('User created', Msg::Success);
        }
        else {
            $this->msgRegister->push('User cannot be created', Msg::Error);
        }
        HTTP::redirect($redirectionURI);
    }
}
