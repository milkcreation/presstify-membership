<?php
/*
Plugin Name: Membership
Plugin URI: http://presstify.com/plugins/membership
Description: Gestion d'espaces Membres
Version: 1.0.0
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

namespace tiFy\Plugins\Membership;

use tiFy\Core\User\Login\Login;

class Membership extends \tiFy\Environment\Plugin
{
    /**
     * Rôles et habilitations
     * @var array
     */
    private static $Roles = [];

    /**
     * Url d'accès
     * @var string
     */
    private static $BaseUri = null;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {    
        parent::__construct();

        // Définition des rôles et habilitations
        self::setRoles();
        
        // Chargement des contrôleurs
        new Admin;
        new User;
        
        // Habilitations
        self::tFyAppShareContainer(
            'tiFy\Plugins\Membership\Capabilities',
            self::tFyAppLoadOverride('tiFy\Plugins\Membership\Capabilities')
        );

        /// Interface d'authentification
        self::tFyAppShareContainer(
            'tiFy\Plugins\Membership\Login',
            Login::register(
                'tiFyPluginMembership-Login',
                [
                    'roles' => array_keys(self::getRoles()),
                    'cb'    => self::tFyAppGetOverride('tiFy\Plugins\Membership\Login')
                ]
            )
        );

        // Formulaires (inscription && modification de compte)
        self::tFyAppShareContainer(
            'tiFy\Plugins\Membership\Forms',
            self::tFyAppLoadOverride('tiFy\Plugins\Membership\Forms')
        );
        
        // Affichage général
        $template = self::tFyAppConfig('template') ? : self::getOverride('tiFy\Plugins\Membership\Template');
        self::tFyAppShareContainer(
            'tiFy\Plugins\Membership\Template',
            new $template
        );
        
        // Email
        self::tFyAppShareContainer(
            'tiFy\Plugins\Membership\Mail',
            self::tFyApploadOverride('tiFy\Plugins\Membership\Mail')
        );

        /// Fonctions d'aide 
        require_once self::tFyAppDirname() .'/Helpers.php';
    }
      
    /**
     * CONTROLEURS
     */
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
