<?php
namespace tiFy\Plugins\Membership\Admin;
 
class EditUser extends \tiFy\Core\Templates\Admin\Model\TabooxEditUser\TabooxEditUser
{	
    /**
     * PARAMETRAGE
     */
    /**
     * Récupération de la liste des rôles concernés par la vue
     * @see \tiFy\Core\Templates\Admin\Model\EditUser\EditUser::set_roles()
     */
	public function set_roles()
	{
		$roles = \tiFy\Plugins\Membership\Membership::getRoleNames();
		
		return $roles;
	}
	
	/**
	 * DECLENCHEURS
	 */
	/**
	 * Initialisation de l'interface d'administration
	 */
	public function admin_init()
	{
		parent::admin_init();				
		tify_option_user_register( 'tify_membership_status', true, 'wp_unslash' );
	}
	
	/**
	 * Mise en file des scripts
	 */
	public function admin_enqueue_scripts()
	{
		parent::admin_enqueue_scripts();		
		wp_enqueue_style( 'tify_control-switch' );
	}
	
	/**
	 * AFFICHAGE
	 */
	/**
	 * Affichage des actions secondaire de la boîte de soumission du formulaire
	 * @see \tiFy\Core\Templates\Admin\Model\EditUser\EditUser::minor_actions()
	 */
	public function minor_actions()
	{
		$user_status = ( ! empty( $this->item ) && ( $status = get_user_option( 'tify_membership_status', $this->item->ID ) ) )? $status : 'disabled';	
		
		$output  = "";
		$output .= "<br/>";
		$output .= "<label style=\"display:block;font-weight:600;font-size:14px;margin-bottom:5px;\">";
		$output .= __( 'Activer', 'tify' );
		$output .= "&nbsp;<em style=\"color:#999; font-size:11px;\">". sprintf( __( '(Actuel: %s)', 'tify' ), \tiFy\Plugins\Membership\User::getStatusLabel( $user_status ) ) ."</em>";
		$output .= "</label>";
		$output .= tify_control_switch(
			array(
				'name'			=> 	'tify_option_user[tify_membership_status]',
				'checked' 		=> 	$user_status === 'activated' ? 'activated' : ( $user_status  ? $user_status : 'disabled' ),
				'value_on'		=> 'activated',
				'value_off'		=> ( $user_status && ( $user_status !== 'activated' ) ) ?  $user_status : 'disabled',
				'echo'			=> false
			)
		);	
		
		$output .= $this->role_dropdown();
		
		echo $output;
	}
}