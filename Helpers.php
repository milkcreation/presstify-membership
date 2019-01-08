<?php
use tiFy\Plugins\Membership\Membership;

/* = Vérifie si la page courante affiche un template de membership = */
function is_tify_membership( $template = null )
{
	$Template = Membership::getController( 'template' );
	
	return $Template::IsTemplate( $template );
}

/* = Affichage d'un template de membership = */
function tify_membership_content( $template = null, $echo = true )
{
	$Template = Membership::getController( 'template' );
	
	$output = $Template::content( $template );
	if( $echo )
	    echo $output;
	
	return $output;
}