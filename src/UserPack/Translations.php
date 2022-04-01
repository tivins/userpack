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
            'UserName'           => [
                'en' => 'User name',
                'fr' => 'Nom d\'utilisateur',
                'it' => 'Nome utente',
                'es' => 'Nombre de usuario',
                'de' => 'Name des nutzers',
                'nl' => 'Naam gebruiker',
            ],
            'Sign in'             => [
                'en' => 'Sign in',
                'fr' => 'S\'identifier',
                'it' => 'Identificarsi',
            ],
            'Password'            => [
                'en' => 'Password',
                'fr' => 'Mot de passe',
                'it' => 'Password',
            ],
            'CurrentPassword'            => [
                'en' => 'Current password',
                'fr' => 'Mot de passe actuel',
            ],
            'ConfirmPassword'    => [
                'en' => 'Confirm password',
                'fr' => 'Confirmation du mot de passe',
            ],
            'ForgotPassword'    => [
                'en' => 'Forgot password?',
                'fr' => 'mot de passe oublié ?',
            ],
            'Invalid credentials' => [
                'en' => 'Invalid credentials',
                'fr' => 'Identifiants erronés',
            ],
            'security_token_error' => [
                'en' => 'Form security error. Please retry.',
                'fr' => 'Erreur de sécurité du formulaire. merci de ré-essayer.',
            ]
        ];
    }
}