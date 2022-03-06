<?php

namespace Tivins\UserPack;

use Tivins\Core\StringUtil;

class UserForm
{

    public function login(array $options = [], ?string $errors = null)
    {
        $html = '<form method="post" action="/" class="login-form">';
        $html .= '<h1 id="logo" class="text-center d-block" style="margin:5rem 0">Logger</h1>';
        $html .= '<h3>Authentication</h3>';
        if ($errors) {
            $html .= '<div class="field error">' . StringUtil::html($errors) . '</div>';
        }
        $html .= '<div class="field"><label for="login-form-name">Name</label><input id="login-form-name" type="text" required name="name"/></div>';
        $html .= '<div class="field"><label for="login-form-password">Password</label><input id="login-form-password" required type="password" name="password"/></div>';
        $html .= '<div class="field"><button type="submit">Log In</button></div>';
        $html .= '</form>';
        return $html;
    }
}