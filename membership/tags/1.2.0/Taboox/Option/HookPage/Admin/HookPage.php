<?php

namespace tiFy\Plugins\Membership\Taboox\Option\HookPage\Admin;

use tiFy\Plugins\Membership\Options;

class HookPage extends \tiFy\Core\Taboox\Admin
{
    /* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
    public function admin_init()
    {
        register_setting( $this->page, 'page_for_tify_membership' );
    }

    /* = FORMULAIRE DE SAISIE = */
    public function form()
    {
    ?>
<table class="form-table">
    <tbody>
        <tr>
            <th><?php _e( 'Choix de la page d\'accroche', 'tify' );?></th>
            <td>
            <?php 
            wp_dropdown_pages(
                array(
                    'name'                  => 'page_for_tify_membership',
                    'class'                 => 'widefat',
                    'post_type'             => 'page',
                    'selected'              => (int) Options::HookID(),
                    'show_option_none'      => __( 'Aucune page choisie', 'tify' ),
                    'sort_column'           => 'menu_order' 
                )
            );            
            ?>
            </td>
        </tr>
    </tbody>
</table>
    <?php    
    }
}