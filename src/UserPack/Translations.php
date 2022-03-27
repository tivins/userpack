<?php

namespace Tivins\UserPack;

use Tivins\I18n\TranslationModule;

class Translations extends TranslationModule
{
    /**
     * @inheritDoc
     */
    protected function getAll(): array
    {
        return [
            'User name'           => [
                'fr' => 'Nom d\'utilisateur',
                'en' => 'User name',
                'it' => 'Nome utente',
            ],
            'Sign in'             => [
                'fr' => 'S\'identifier',
                'en' => 'Sign in',
                'it' => 'Identificarsi',
            ],
            'Password'            => [
                'fr' => 'Mot de passe',
                'en' => 'Password',
                'it' => 'Password',
            ],
            'Confirm password'    => [
                'fr' => 'Confirmation du mot de passe',
                'en' => 'Confirm password',
            ],
            'Forgot password?'    => [
                'fr' => 'mot de passe oublié ?',
                'en' => 'Forgot password',
            ],
            'Invalid credentials' => [
                'fr' => 'Identifiants erronés',
                'en' => 'Invalid credentials',
            ],
            'security_token_error' => [
                'fr' => 'Erreur de sécurité du formulaire. merci de ré-essayer.',
                'en' => 'Form security error. Please retry.',
            ]
        ];
    }
}