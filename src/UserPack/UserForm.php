<?php

namespace Tivins\UserPack;

use Tivins\Core\StringUtil;

class UserForm
{
    private string $errors = '';

    public function loginCheck(UserModule $userModule, string $redirectionURI = '/')
    {
        $this->errors = '';
        if (($_POST['formId'] ?? '') != $this->getLoginFormId()) {
            return false;
        }
        $user = $userModule->getByCredentials($_POST['name'], $_POST['password']);
        if ($user) {
            UserSession::setID($user->id);
            header('Location: ' . $redirectionURI);
            exit;
        }
        $this->errors = 'Invalid credentials';
        return true;
    }

    public function getLoginFormId(): string
    {
        return str_replace('\\', '.', __class__) . '.login';
    }

    public function login(array $options = [], ?string $errors = null): string
    {
        $html = '<form method="post" action="/" class="login-form">';
        $html .= '<input type="hidden" name="formId" value="' . StringUtil::html($this->getLoginFormId()) . '">';
        if ($this->errors) {
            $html .= '<div class="field error">' . StringUtil::html($this->errors) . '</div>';
        }
        $html .= '<div class="field"><label for="login-form-name">Name</label><input id="login-form-name" type="text" required name="name"/></div>';
        $html .= '<div class="field"><label for="login-form-password">Password</label><input id="login-form-password" required type="password" name="password"/></div>';
        $html .= '<div class="field"><button type="submit">Log In</button></div>';
        $html .= '</form>';
        return $html;
    }
}