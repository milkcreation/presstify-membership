<?php
/*
 * @Overridable
 */
namespace tiFy\Plugins\Membership;

use tiFy\Plugins\Membership\User;

class Login extends \tiFy\Core\User\Login\Factory
{
    /**
     * CONTROLEURS
     */
    /**
     * Récupération de l'url de redirection du formulaire d'authentification
     *
     * @param string $redirect_url Url de redirection personnalisée
     * @param \WP_User $user Utilisateur courant
     *
     * @return string
     */
    public function get_login_form_redirect($redirect_url = '', $user)
    {
        return Membership::getBaseUri();
    }

    /**
     * Vérification des droits d'authentification d'un utilisateur
     *
     * @param \WP_User $user
     * @param string $username Identifiant de l'utilisateur passé en argument de la requête d'authentification
     * @param string $password Mot de passe en clair passé en argument de la requête d'authentification
     *
     * @return \WP_Error|\WP_User
     */
    public function authenticate($user, $username, $password)
    {
        $user = parent::authenticate($user, $username, $password);

        // Bypass
        if (is_wp_error($user)) :
            return $user;
        endif;
        if (!$user instanceof \WP_User) :
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