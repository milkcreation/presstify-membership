<?php
namespace tiFy\Plugins\Membership;

class Actions extends \tiFy\App\Factory
{	
	/* = Page d'accueil = */
	public static function Home()
	{
		if( is_user_logged_in() && ! current_user_can( 'tify_membership_allowed_user' ) ) :
			return Template::setNotice( __( 'L\'accès à cet espace est strictement réservé aux membres.', 'tify' ), 'warning' );
		endif;
	}
	
	/* = Formulaire d'inscription = */
	public static function SubscribeForm()
	{
		if( is_user_logged_in() ) :
			return Template::setNotice( __( 'Désolé, impossible de vous inscrire, vous êtes déjà connecté.', 'tify' ), 'error' );
		endif;
	}
	
	/* = Formulaire de modification des informations utilisateur = */
	public static function UserAccount()
	{
		if( ! is_user_logged_in() ) :
			return Template::body_login_form();
		elseif( ! current_user_can( 'tify_membership_allowed_user' ) ) :
			return Template::setNotice( __( 'La modification des paramètres de compte est réservée uniquement aux membres.', 'tify' ), 'warning' );
		endif;
	}
	
	/* = Expédition d'un email d'activation de compte = */
	public static function ActivationEmail()
	{	
		if( ! is_user_logged_in() )
			return Template::setNotice( __( 'Vous devez être connecté pour solliciter l\'envoi d\'un email d\'activation.', 'tify' ), 'error' );
		if( ! current_user_can( 'tify_membership_allowed_user' ) )
			return Template::setNotice( __( 'Seuls les utilisateurs habilités à ce service peuvent solliciter l\'envoi d\'un email d\'activation.', 'tify' ), 'error' );
		if( User::isActive() )
			return Template::setNotice( __( 'Ce compte a déjà été activé.', 'tify' ), 'warning' );
			
		Mail::Activation( get_current_user_id() );
	}
	
	/* = Activation de compte = */
	public static function Activate()
	{		
		if( empty( $_REQUEST['token'] ) )
			return Template::setNotice( __( 'Désolé, impossible d\'activer ce compte, les informations fournies sont insuffisantes', 'tify' ), 'error' );
		
		$token = $_REQUEST['token'];
		global $wpdb;
		
		$user_query = new \WP_User_Query( 
			array( 
				'role__in'		=> Membership::getRoleNames(),
				'number' 		=> 1,
				'meta_key'     	=> $wpdb->get_blog_prefix() .'tify_membership_key',
				'meta_value'   	=> $token
			) 
		);
				
		if( ! $user_query->get_total() ) :
			return Template::setNotice( __( 'Désolé, aucun compte ne correspond à ces informations, ou le compte a déjà été activé', 'tify' ), 'error' );
		endif;
		
		$u = current(  $user_query->get_results() );

		if( is_user_logged_in() && ( get_current_user_id() !== $u->ID ) ) :
			return Template::setNotice( __( 'Vous êtes actuellement authentifié avec un compte différent de celui que vous cherchez à activer; veuillez d\'abord vous déconnecter.', 'tify' ), 'error' );
		elseif( ( get_user_option( 'tify_membership_status', $u->ID ) === 'registered' ) && ! update_user_option( $u->ID, 'tify_membership_status', 'activated' ) ) :
			return Template::setNotice( __( 'Un problème est survenu pendant l\'activation de votre compte, veuillez prendre contact avec l\'administrateur du site.', 'tify' ), 'error' );			
		endif;
	}
	
	/* = Désinscription = */
	public static function Unsubscribe()
	{		
		if( empty( $_REQUEST['token'] ) || empty( $_REQUEST['email'] ) ) :
			return Template::setNotice( __( 'Désolé, impossible d\'annuler l\'inscription, les informations fournies sont insuffisantes', 'tify' ), 'error' );
		endif;
		
		$token = $_REQUEST['token'];
		global $wpdb;
		
		$user_query = new \WP_User_Query( 
			array( 
				'search'         	=> $_REQUEST['email'],
				'search_columns' 	=> array( 'user_email' ),				
				'role__in'			=> Membership::getRoleNames(),
				'number' 			=> 1,
				'meta_key'     		=> $wpdb->get_blog_prefix() .'tify_membership_unsub',
				'meta_value'   		=> $token
			) 
		);
		
		$u = current(  $user_query->get_results() );
		
		if( ! $user_query->get_total() ) :
			return Template::setNotice( __( 'Désolé, aucun compte ne correspond à ces informations, ou l\'inscription a déjà été annulée.', 'tify' ), 'error' );
		endif;
		
		require_once( ABSPATH .'wp-admin/includes/user.php' );
		\wp_delete_user( $u->ID );
	}
}