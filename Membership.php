<?php
/*
Plugin Name: Membership
Plugin URI: http://presstify.com/plugins/membership
Description: Gestion d'espaces Membres
Version: 1.2
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

namespace tiFy\Plugins\Membership;

use tiFy\Components;
use tiFy\Core\Cron\Cron;
use tiFy\Components\Login\Login;

class Membership extends \tiFy\Environment\Plugin
{
    /**
     * Rôles et habilitations
     */
    private static $Roles            = array();
    
    /**
     * Url d'accès
     */
    private static $BaseUri            = null;
    
    /**
     * Contrôleurs
     */
    private static $Controller        = array();
    
    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {    
        parent::__construct();

        // Tâche planifiée nettoyage des clés d'activation
        $this->tFyAppActionAdd('tify_cron_register');

        // Définition des rôles et habilitations
        self::setRoles();
        
        // Chargement des contrôleurs
        new Admin;    
        new User;
        
        /// Habilitations
        $capabilities = self::getOverride( '\tiFy\Plugins\Membership\Capabilities' );
        static::$Controller['capabilities'] = new $capabilities;
        
        /// Formulaire d'authentification
        Components::register('Login');
        $this->tFyAppActionAdd('tify_login_register');

        /// Formulaire d'inscription + modification de compte
        static::$Controller['forms'] = self::loadOverride( '\tiFy\Plugins\Membership\Forms' );
        
        /// Affichage général
        if( $template = self::tFyAppConfig('template') ) :
        else :
            $template = self::getOverride( 'tiFy\Plugins\Membership\Template' );
        endif;
        static::$Controller['template'] = new $template;
        
        /// Email
        static::$Controller['mail'] = self::loadOverride( 'tiFy\Plugins\Membership\Mail' );
        
        /// Fonctions d'aide 
        require_once self::tFyAppDirname() .'/Helpers.php';
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Déclaration du formulaire d'authentification
     *
     * @return \tiFy\Components\Login\Factory
     */
    public function tify_login_register()
    {
        $attrs = self::tFyAppConfig('login');
        $attrs['cb'] = self::getOverride('\tiFy\Plugins\Membership\Login');
        $attrs['roles'] = array_keys(self::tFyAppConfig('roles'));
        $attrs['redirect_url'] = self::getBaseUri();

        static::$Controller['login'] = Login::register('_tiFyPluginMembership-Login', $attrs);
    }

    /**
     * Déclaration de la tâche planifiée de nettoyage de clé d'activation expirée
     *
     * @return void
     */
    public function tify_cron_register()
    {
        Cron::register(
            '_tiFyMembershipActivationKeyClean',
            [
                // Intitulé de la tâche planifiée
                'title'         => __('Nettoyage des clés d\'activation expirées', 'Theme'),
                // Execution du traitement de la tâche planifiée
                'handle'        => [$this, 'clean_key'],
                // Attributs de journalisation des données
                'log'           => false
            ]
        );
    }

    /**
     * Tâche planifiée de nettoyage des clés d'activation
     * @todo
     *
     * @return void
     */
    public function clean_key()
    {
        global $wpdb;

        $prefix = $wpdb->prefix;

        $user_query = new \WP_User_Query(
            [
                'role' => 'tify_membership',
                'meta_query' => [
                    [
                        'key'   => "{$prefix}tify_membership_key_expiry",
                        'value' => time(),
                        'compare' => '<'
                    ]
                ],
                'fields' => 'ID'
            ]
        );
        if ($user_ids = $user_query->get_results()) :
            foreach($user_ids as $user_id) :
                delete_user_option($user_id, 'tify_membership_key');
                delete_user_option($user_id, 'tify_membership_key_expiry');
            endforeach;
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Récupération des controleurs
     */
    public static function getController( $controller )
    {
        if( isset( static::$Controller[$controller] ) )
            return static::$Controller[$controller];
    }
    
    /**
     * Récupération des rôles et de leurs attributs
     */
    private static function setRoles()
    {
        if (! $roles = self::tFyAppConfig('roles'))
            return;
        foreach ($roles as &$role) :
            $role = wp_parse_args( 
                $role, 
                array(
                    'capabilities'          => array(),   
                    'show_admin_bar_front'  => true,
                    'wp_ui'                 => true
                )
            );
        endforeach;

        self::$Roles = $roles;
    }
    
    /**
     * Récupération des rôles et de leurs attributs
     */
    public static function getRoles()
    {
        if( ! empty( self::$Roles ) ) :
            return self::$Roles;
        else :
            return array();
        endif;
    }
    
    /**
     * Récupération de la liste des rôles
     */
    public static function getRoleNames()
    {
        return array_keys( self::$Roles );
    }
    
    /**
     * Récupération de la liste des rôles
     */
    public static function getBaseUri()
    {
        if( ! empty( self::$BaseUri ) ) :
            return self::$BaseUri;
        else :
            return self::$BaseUri = ( $base_uri = Options::HookPermalink() ) ? $base_uri : home_url();
        endif;
    }
}
