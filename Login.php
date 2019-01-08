<?php
/*
 * @Overridable
 */
namespace tiFy\Plugins\Membership;

use tiFy\Plugins\Membership\User;

class Login extends \tiFy\Components\Login\Factory
{
    /**
     * Vérification des droits d'authentification d'un utilisateur
     *
     * @param \WP_User|WP_Error $user
     * @param string $username
     * @param string $password
     *
     * @return \WP_User|WP_Error
     */
    public function authenticate($user, $username, $password)
    {
        // Appel du parent
        $user = parent::authenticate($user, $username, $password);

        // Bypass
        if (is_wp_error($user)) :
            return $user;
        endif;
        if (empty($user->ID)) :
            return $user;
        endif;
        if (empty(array_intersect($this->getRoles(), $user->roles))) :
            return $user;
        endif;

        $statusAttrs = User::getUserStatusAttrs($user->ID);

        if (empty($statusAttrs['authenticate'])) :
            return new \WP_Error('user_status_not_allowed',
                __('Désolé, votre statut utilisateur ne vous permet pas de vous connecter pour le moment.', 'tify'));
        endif;

        return $user;
    }
}