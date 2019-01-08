<?php
namespace tiFy\Plugins\Membership\Admin;
 
class Options extends \tiFy\Core\Templates\Admin\Model\TabooxOption\TabooxOption
{		
	/* = DECLARATION DES PARAMETRES = */
	/** == Définition des sections de formulaire d'édition == **/
	public function set_sections()
	{
	   return array(
			'tiFyPluginsMembershipHookPage' => array(
				'title'		=> __( 'Page d\'accroche', 'tify' ),
				'cb'		=> 'tiFy\Plugins\Membership\Taboox\Option\HookPage\Admin\HookPage',
				'order'		=> 0
			) 
		);
	}
}