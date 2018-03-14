<?php
namespace tiFy\Plugins\Membership;

class Options
{	
	/* = ARGUMENTS = */
	// ID de la page d'accroche
	private static $HookID 			= null;
	
	// Url de la page d'accroche
	private static $HookPermalink	= null;
	
	/* = CONTRÔLEURS = */
	/** == ID de la page d'accroche == **/
	public static function HookID()
	{
	    if( is_null( self::$HookID ) )
			self::$HookID = (int) get_option( 'page_for_tify_membership', Membership::tFyAppConfig( 'hook_id' ) );
		
		return self::$HookID;
	}
	
	/** == Permaliens de la page d'accroche == **/
	public static function HookPermalink()
	{
		if( is_null( self::$HookPermalink ) )
			self::$HookPermalink = \get_permalink( self::HookID() );
		
		return self::$HookPermalink;
	}
}