<?php
/*
 * @Overridable
 */
namespace tiFy\Plugins\Membership;

class Capabilities extends \tiFy\App\Factory
{
    /**
     * CONSTRUCTEUR 
     */
    public function __construct()
    {
        parent::__construct();
        
        // Déclencheurs
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
        add_filter( 'editable_roles', array( $this, 'editable_roles' ) );
        add_filter( 'views_users', array( $this, 'views_users' ) );
        add_filter( 'users_list_table_query_args', array( $this, 'users_list_table_query_args' ) );
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation de l'interface d'administration
     */
    final public function admin_init()
    {                
        // Création des roles et des habilitations
        foreach( (array) Membership::getRoles() as $role => $args ) :
            // Création du rôle
            if( ! $_role =  get_role( $role ) )
                $_role = add_role( $role, $args['display_name'] );

            // Création des habilitations
            foreach( (array)  $args['capabilities'] as $cap => $grant ) :
                if( ! isset( $_role->capabilities[$cap] ) ||  ( $_role->capabilities[$cap] != $grant ) ) :
                    $_role->add_cap( $cap, $grant );
                endif;
            endforeach;
        endforeach;
    }

    /**
     * Définition des habilitations
     */
    public function map_meta_cap( $caps, $cap, $user_id, $args )
    {
        switch ( $cap ) :
            case 'tify_membership_allowed_user' :
                $callback = preg_replace( '/^tify_membership_/', '', $cap );                
                $caps = call_user_func( array( $this, $callback ), $caps, $cap, $user_id, $args );
                break;
        endswitch;    

        return $caps;
    }
    
    /**
     *  Modification des rôles éditables dans la page liste utilisateurs
     *  
     *  @param array $roles Rôles éditables
     */
    final public function editable_roles( $roles )
    {
        foreach( (array) Membership::getRoles() as $role => $args ) :
            if( ! $args['wp_ui'] && ( get_current_screen()->id == 'users' ) ) :
                unset( $roles[$role] );
            endif;
        endforeach;
            
        return $roles;    
    }

    /**
     * Filtrage des vues dans la page liste utilisateurs
     * @param array $views Vues existantes
     */
    final public function views_users( $views )
    {
        global $role;
        $users = count_users();
        $users_to_subtract = 0;
        foreach( (array) Membership::getRoles() as $_role => $args ) :
            if( ! $args['wp_ui'] ) :
                $users_to_subtract += $users['avail_roles'][$_role];
                unset( $views[$_role] );
            endif;
        endforeach;
        $total_users = $users['total_users'] - $users_to_subtract;
        
        $is_site_users = 'site-users-network' === get_current_screen()->id;
        if ( $is_site_users ) :
            $site_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
            $url = 'site-users.php?id=' . $site_id;
        else :
            $url = 'users.php';
        endif;
        $class = empty($role) ? ' class="current"' : '';
        $views['all'] = "<a href='$url'$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_users, 'users' ), number_format_i18n( $total_users ) ) . '</a>';
        return $views;
    }

    /**
     * Filtrage des utilisateurs dans la page liste utilisateurs
     * @param array $args Arguments de la requête
     */
    final public function users_list_table_query_args( $query_args )
    {
        // Création des roles et des habilitations
        foreach( (array) Membership::getRoles() as $role => $args ) :
            if( ! $args['wp_ui'] ) :
                $query_args['role__not_in'] = array( $role );
            endif;
        endforeach;
        
        return $query_args;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Utilisateur habilité
     */
    public function allowed_user( $caps, $cap, $user_id, $args )
    {
        if( ! is_user_logged_in() ) :
            $caps = array( 'do_not_allow' );
        else :
            $userdata = get_userdata( $user_id );
            foreach( (array) Membership::getRoleNames() as $role ) :
                if( in_array( $role, $userdata->roles ) ) :
                    return array( 'exist' );
                endif;
            endforeach;
            $caps = array( 'do_not_allow' );
        endif;
        
        return $caps;
    }
}