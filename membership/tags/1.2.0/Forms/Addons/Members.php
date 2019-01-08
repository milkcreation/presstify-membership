<?php
namespace tiFy\Plugins\Membership\Forms\Addons;

use tiFy\Plugins\Membership\User;
use tiFy\Plugins\Membership\Membership;

class Members extends \tiFy\Core\Forms\Addons\User\User
{    
    /* = ARGUMENTS = */
    public $ID = 'tiFyPluginMembership';
    
    /* = DECLENCHEURS = */
    /** == == **/
    public function cb_handle_submit_request( $handle )
    {        
        parent::cb_handle_submit_request( $handle );
        
        if( ! $user = get_userdata( $this->getUserID() ) )
            return;
        
        // Enregistrement du status    
        if( ! get_user_option( 'tify_membership_status' ) ) :
            update_user_option( $this->getUserID(), 'tify_membership_status', User::getDefaultStatus() );
            update_user_option( $this->getUserID(), 'tify_membership_salt', wp_hash_password( wp_generate_password( 12, true, true ) ) );
        endif;
            
        // Envoi du mail de confirmation à l'enregistrement
        if( ! $this->isProfile() ) :
            if( $mail = Membership::getController('mail') )
                $mail->activation( $this->getUserID() );
        endif;
    }
}