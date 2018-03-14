<?php
namespace tiFy\Plugins\Membership;

class User extends \tiFy\App\Factory
{
	/* = PARAMETRES = */
	// Liste des status
	private static $Statuses	= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		// Initialisation des statuts
		self::$Statuses = array(
			// Enregistré					
			'registered'	=> array(
				'label'			=> array( 
					'singular' 		=> __( 'Enregistré', 'tify' ), 
					'plural' 		=> __( 'Enregistrés', 'tify' ),
					'description'	=> __( 'Le compte utilisateur a été créé mais celui ci n\'est pas encore actif', 'tify' )
				),
				'authenticate'	=> true
			),
			// Activé	
			'activated'		=> array( 
				'label' 		=> array(
					'singular' 		=> __( 'Activé', 'tify' ), 
					'plural' 		=> __( 'Activés', 'tify' ),
					'description'	=> __( 'Le compte utilisateur a été créé et activé', 'tify' )
				),
				'authenticate'	=> true
			),	
			// Désactivé						
			'disabled'		=> array( 
				'label'			=> array(
					'singular' 		=> __( 'Désactivé', 'tify' ), 
					'plural' 		=> __( 'Désactivés', 'tify' ),
					'description'	=> __( 'Le compte utilisateur a été créé mais celui ci a été désactivé', 'tify' )
				),
				'authenticate'	=> false
			),
			// Confirmé - deprecié	
			'confirmed'		=> array( 
				'label'			=> array(
					'singular' 		=> __( 'Confirmé', 'tify' ), 
					'plural' 		=> __( 'Confirmés', 'tify' )
				),
				'authenticate'	=> true
			),
		);
	}	
	
	/* = CONTRÔLEURS = */
	/** == Vérifie si un utilisateur est actif == **/
	final public static function isActive( $user_id = 0 )
	{
		if( ! $user_id )
			$user_id = get_current_user_id();
		if( ! $user_id )
			return false;
		
		if( user_can( $user_id, 'tify_membership_allowed_user' ) ) :
			return ( self::getUserStatus( $user_id ) === 'activated' );
		else :
			return false;
		endif;
	}
	
	/** == Déclaration d'un nouveau statut == **/
	final public static function registerStatus( $id, $attrs )
	{
		$statuses = self::getStatuses();
		
		if( isset( $statuses[$id] ) ) :
			$defaults = $statuses[$id];
		else :
			$defaults = array(
				'label'			=> $id,
				'authenticate'	=> false
			);
		endif;
		
		self::$Statuses[$id] = wp_parse_args( $args, $defaults );		
	}	
	
	/** == Liste des statuts utilisateur == **/
	final public static function getStatuses()
	{		
		return self::$Statuses;
	}
		
	/** == Liste des attributs d'un statut utilisateur == **/
	final public static function getStatusAttrs( $id )
	{
		$statuses = self::getStatuses();
		
		if( isset( $statuses[$id] ) )
			return $statuses[$id];
	}
	
	/** == Récupération d'un attribut de statut utilisateur == **/
	final public static function getStatusAttr( $id, $attr, $default = '' )
	{
		if( ! $args = self::getStatusAttrs( $id ) )
			return $default;
		
		if( isset( $args[$attr] ) )
			return $args[$attr];
		
		return $default;	
	}
	
	/** == Récupération d'un intitulé de statut utilisateur == **/
	final public static function getStatusLabel( $id, $type = 'singular' )
	{
		if( ! $label = self::getStatusAttr( $id, 'label', false ) )
			return $id;
		
		if( isset( $label[$type] ) ) :
			return $label[$type];
		elseif( isset( $label['singular'] ) ) :
			return $label['singular'];
		elseif( is_string( $label ) ) :
			return $label;
		else :
			return $id;
		endif;
	}
			
	/** == Récupération du statut de l'utilisateur == **/
	final public static function getUserStatus( $user_id = 0, $translate = false )
	{
		$user_status = get_user_option( 'tify_membership_status', $user_id );
		$statuses = self::getStatuses();
		
		// Bypass
		if( ! isset( $statuses[$user_status] ) )
			return;
		
		if( ! $translate ) :
			return $user_status;
		else :
			return static::getStatusLabel( $user_status );
		endif;
	}
	
	/** == Récupération des attributs de status d'un utilisateur == **/
	final public static function getUserStatusAttrs( $user_id = 0, $translate = false )
	{
		$user_status = get_user_option( 'tify_membership_status', $user_id );
		if( ! $user_status )
		    $user_status = self::getDefaultStatus();
		
		$statuses = self::getStatuses();
		
		// Bypass
		if( ! isset( $statuses[$user_status] ) )
			return;
		
		return $statuses[$user_status];
	}
	
	/** == Récupération de la clé d'activation d'un utilisateur == **/
	final public static function getActivationKey( $user_id )
	{		
		if( ! $activation_key = get_user_option( 'tify_membership_key', $user_id ) ) :
			$activation_key = wp_generate_password( 32, false, false );
			update_user_option( $user_id, 'tify_membership_key', $activation_key );
		endif;
		
		return $activation_key;
	}
	
	/** == Récupération de la clé de désinsciption == **/
	final public static function getUnsubscribeToken( $user_id )
	{				
		if( ! $unsubscribe_token = get_user_option( 'tify_membership_unsub', $user_id ) ) :
			$unsubscribe_token = wp_generate_password( 48, false, false );
			update_user_option( $user_id, 'tify_membership_unsub', $unsubscribe_token );
		endif;
		
		return $unsubscribe_token;
	}
	
	/* = SURCHAGE = */	
	/** == Récupération du statut utilisateur par défaut == **/
	final public static function getDefaultStatus()
	{
		return 'registered';
	}	
}