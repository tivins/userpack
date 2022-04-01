<?php

namespace Tivins\UserPack;

use Tivins\Core\Code\Exception;
use Tivins\Core\HTML\Form;
use Tivins\Core\HTML\FormSecurity;
use Tivins\Core\Http\HTTP;
use Tivins\Core\Msg;
use Tivins\Core\StringUtil as Str;
use Tivins\I18n\I18n;

class UserForm
{
    private string $actionURI = '/';

    private I18n $i18n;
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

    /**
     * @param array $options Options to configure the form.
     *      * `forgotURL` (string, default: ''): Add "forgot password" link if defined.
     *      * `formClass` (string, default 'login-form'): The CSS class(es) for the form.
     *
     * @return string
     * @throws Exception
     */
    public function login(array $options = []): string
    {
        $options += [
            'forgotURL' => '',
            'formClass' => 'login-form',
        ];

        $html = '<form method="post" action="' . Str::html($this->actionURI) . '" class="' . Str::html($options['formClass']) . '">';
        $html .= $this->msgLogin->get();
        $html .= $this->getHiddenFields($this->getLoginFormId());

        $html .= '<div class="field">'
            . '<label for="login-form-name">' . Str::html($this->translate('User name')) . '</label>'
            . '<input id="login-form-name" type="text" required name="name"/>'
            . '</div>';

        $html .= '<div class="field">'
            . '<div class="d-flex">'
            . '<label for="login-form-password" class="flex-grow">' . Str::html($this->translate('Password')) . '</label>'
            . ($options['forgotURL'] ? '<a href="' . Str::html($options['forgotURL']) . '" class="fs-80">' . Str::html($this->translate('ForgotPassword')) . '</a>' : '')
            . '</div>'
            . '<input id="login-form-password" required type="password" name="password"/>'
            . '</div>';

        $html .= '<div class="field">'
            . '<button type="submit">' . Str::html($this->translate('Sign in')) . '</button>'
            . '</div>';

        $html .= '</form>';
        return $html;
    }

    private function getLoginFormId(): string
    {
        return base64_encode(static::class . '.' . __function__);
    }

    private function translate(string $key): string
    {
        return $this->i18n->get($key, $key);
    }

    /**
     * @throws Exception
     */
    public function loginCheck(UserModule $userModule, string $redirectionURI = '/')
    {
        if (($_POST['formId'] ?? '') != $this->getLoginFormId()) {
            return false;
        }

        if (!FormSecurity::checkPostedToken($this->getLoginFormId(), $_POST['token'] ?? '')) {
            $this->msgLogin->push($this->translate('security_token_error'), Msg::Error);
            HTTP::redirect($redirectionURI);
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
            $this->msgLogin->push($this->translate('Invalid credentials'), Msg::Error);
        }
        HTTP::redirect($redirectionURI);
    }

    public function setActionURI(string $uri): static
    {
        $this->actionURI = $uri;
        return $this;
    }


    /**
     * @throws Exception
     */
    public function register(array $options = []): string
    {
        $catchaQuestion = $this->generateCaptcha();

        $html = '<form method="post" action="' . $this->actionURI . '" class="register-form">';
        $html .= $this->msgRegister->get();
        $html .= $this->getHiddenFields($this->getRegisterFormId());

        $html .= '<div class="field">'
            . '<label for="register-form-name">' . Str::html($this->translate('User name')) . '</label>'
            . '<input id="register-form-name" type="text" required name="name" value="' . Str::html($_POST['name'] ?? '') . '"/>'
            . '</div>';
        $html .= '<div class="field">'
            . '<label for="register-form-email">' . Str::html($this->translate('Email')) . '</label>'
            . '<input id="register-form-email" type="email" required name="email" value="' . Str::html($_POST['email'] ?? '') . '"/>'
            . '</div>';
        $html .= '<div class="field">'
            . '<label for="register-form-password">' . Str::html($this->translate('Password')) . '</label>'
            . '<input id="register-form-password" required type="password" name="password"/>'
            . '</div>';
        $html .= '<div class="field">'
            . '<label for="register-form-password-confirm">' . Str::html($this->translate('ConfirmPassword')) . '</label>'
            . '<input id="register-form-password-confirm" required type="password" name="password-confirm"/>'
            . '</div>';
        $html .= '<div class="field">'
            . '<label for="register-form-captcha">Anti-robot test : ' . Str::html($catchaQuestion) . '</label>'
            . '<input id="register-form-captcha" type="text" required name="captcha"/>'
            . '</div>';
        $html .= '<div class="field"><button type="submit">Sign up</button></div>';
        $html .= '</form>';
        return $html;
    }

    /**
     * @return string The question to ask.
     */
    private function generateCaptcha(): string
    {
        $numbers                      = [rand(1, 9), rand(1, 9)];
        $_SESSION['userpack_captcha'] = array_sum($numbers);
        return implode(' + ', $numbers) . ' = ';
    }

    private function getRegisterFormId(): string
    {
        return base64_encode(static::class . '.' . __function__);
    }

    /**
     * @throws Exception
     */
    public function registerCheck(UserModule $userModule): bool
    {
        /*
         * The form was not triggered. Nothing to do.
         */
        if (($_POST['formId'] ?? '') != $this->getRegisterFormId()) {
            return false;
        }

        if (!FormSecurity::checkPostedToken($this->getRegisterFormId(), $_POST['token'] ?? '')) {
            $this->msgLogin->push($this->translate('security_token_error'), Msg::Error);
            return false;
        }

        $success = true;

        if (!Str::isEmail($_POST['email'] ?? '')) {
            $this->msgRegister->push('Email invalid', Msg::Error);
            $success = false;
        }
        if (mb_strlen($_POST['name'] ?? '') < 5) {
            $this->msgRegister->push('User name too short (should be bigger than 4 letters long)', Msg::Error);
            $success = false;
        }

        if (empty($_SESSION['userpack_captcha']) || ($_POST['captcha'] ?? '*') != $_SESSION['userpack_captcha']) {
            $this->msgRegister->push('Captcha invalid', Msg::Error);
            $success = false;
        }

        if (!Str::isStrongPassword($_POST['password'] ?? '')) {
            $this->msgRegister->push('Password too weak. Use lower, upper case, number and punctuation.', Msg::Error);
            $success = false;
        }

        if ($_POST['password'] !== $_POST['password-confirm']) {
            $this->msgRegister->push('Passwords were different', Msg::Error);
            $success = false;
        }

        if ($success && $userModule->exists($_POST['name'] ?? '', $_POST['email'] ?? '')) {
            $this->msgRegister->push('User name or email cannot be used.', Msg::Error);
            $success = false;
        }

        if ($success) {
            $uid = $userModule->createUser($_POST['name'], $_POST['email'], $_POST['password']);
            if (!$uid) {
                $this->msgRegister->push('User cannot be created', Msg::Error);
                $success = false;
            }
        }

        return $success;
    }

    private function getEditFormId(): string
    {
        return base64_encode(static::class . '.' . __function__);
    }

    /**
     * @throws Exception
     */
    private function getHiddenFields(string $formId): string
    {
        return Form::hidden('formId', $formId) . Form::hidden('token', FormSecurity::getPublicToken($formId));
    }

    /**
     * @throws Exception
     */
    public function edit(object $user, array $options = []): string
    {
        $html = '<form method="post" action="' . $this->actionURI . '" class="edit-form">';
        $html .= $this->msgRegister->get();
        $html .= $this->getHiddenFields($this->getEditFormId());

        $html .= '<div class="field">'
            . '<label for="edit-form-name">' . Str::html($this->translate('UserName')) . '</label>'
            . '<input id="edit-form-name" type="text" name="name" value="' . Str::html($user->name) . '"/>'
            . '</div>';
        $html .= '<div class="field">'
            . '<label for="edit-form-email">' . Str::html($this->translate('Email')) . '</label>'
            . '<input id="edit-form-email" type="email" required name="email" value="' . Str::html($user->email) . '"/>'
            . '</div>';
        $html .= '<div class="field">'
            . '<label for="edit-form-password">' . Str::html($this->translate('CurrentPassword')) . '</label>'
            . '<input id="edit-form-password" required type="password" name="password"/>'
            . '</div>';
        $html .= '<div class="field"><button type="submit">Update</button></div>';
        $html .= '</form>';
        return $html;
    }

    /**
     * @throws Exception
     */
    public function editCheck(UserModule $userModule, $user): bool
    {
        /*
         * The form was not triggered. Nothing to do.
         */
        if (($_POST['formId'] ?? '') != $this->getEditFormId()) {
            return false;
        }

        if (!FormSecurity::checkPostedToken($this->getEditFormId(), $_POST['token'] ?? '')) {
            $this->msgLogin->push($this->translate('security_token_error'), Msg::Error);
            return false;
        }

        $success = true;

        if (empty($_POST['name'])) {
            $this->msgRegister->push('invalid user name', Msg::Error);
            $success = false;
        }
        if (!Str::isEmail($_POST['email'] ?? '')) {
            $this->msgRegister->push('Email invalid', Msg::Error);
            $success = false;
        }
        if (!password_verify($_POST['password'], $user->password)) {
            $this->msgRegister->push('Passwords were different', Msg::Error);
            $success = false;
        }
        if ($success && $userModule->exists($_POST['name'], $_POST['email'], $user->id)) {
            $this->msgRegister->push('Email cannot be used.', Msg::Error);
            $success = false;
        }

        if ($success && !$userModule->update($user->id, $_POST['name'], $_POST['email'])) {
            $this->msgRegister->push('User cannot be updated.', Msg::Error);
            $success = false;
        }
        if ($success) {
            $this->msgRegister->push('User updated !', Msg::Error);
        }
        return $success;
    }
}
