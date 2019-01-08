<?php 
namespace tiFy\Plugins\Membership;

use tiFy\Core\Templates\Templates;
use tiFy\Plugins\Membership\Membership;

class Admin extends \tiFy\App\Factory
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $tFyAppActions				= array(
		'tify_templates_register'	
	);
	
	/* = DECLENCHEURS = */
	/** == Initialisation globale == **/
	public function tify_templates_register()
	{		
		// Affichage d'une info-bulle dans l'entrée de menu principal pour les nouveaux membres enregistrés
		global $wpdb;
	
		$user_query = new \WP_User_Query( 
			array( 
				'role__in' 		=> Membership::getRoleNames(), 
				'meta_key'     	=> $wpdb->get_blog_prefix() .'tify_membership_status',
				'meta_value'   	=> 'registered'
			) 
		);

		if( $count =  $user_query->get_total() ) :
			$View = Templates::getAdmin('tiFyMembershipMenu');
		    $menu_name = $View->getLabel( 'menu_name' );
			$admin_menu = $View->getAttr( 'admin_menu' );
			$admin_menu['menu_title'] = sprintf( '%1$s&nbsp;<span class="awaiting-mod count-%2$d\"><span class="awaiting-count">%2$d</span></span>', $menu_name, $count );
			$View->setAttr( 'admin_menu',  $admin_menu );
		endif;
	}
}