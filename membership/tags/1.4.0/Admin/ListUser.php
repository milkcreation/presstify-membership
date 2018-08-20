<?php
namespace tiFy\Plugins\Membership\Admin;

class ListUser extends \tiFy\Core\Templates\Admin\Model\ListUser\ListUser
{		
	/* = PARAMETRAGE = */
	/** == Définition des messages de notification == **/
	public function set_notices()
	{
		return array(
			'activated'		=> __( 'L\'utilisateur a été activé.', 'tify' ),
			'deactivated'	=> __( 'L\'utilisateur a été désactivé.', 'tify' )	
		);
	}
	
	/** == Définition des vues filtrées == **/
	public function set_views()
	{
		global $wpdb;
		
		return array(
			array(
				'label'				=> __( 'Tous', 'tify' ),
				'count'				=> $this->count_items( array( 'role__in' => $this->set_roles() ) ),
				'remove_query_args'	=> true
			),
			array(
				'label'				=> array( 'singular' => __( 'Actif', 'tify' ), 'plural' => __( 'Actifs', 'tify' ) ),
				'count'				=> $this->count_items( 
					array(
						'role__in'		=> $this->set_roles(),
						'meta_query'	=> array(
							array(
								'key' 		=> $wpdb->get_blog_prefix( ) .'tify_membership_status',
								'value'		=> 'activated'	
							)	
						)		
					) 
				),
				'hide_empty'		=> true,
				'add_query_args'	=> array( 'member_status' => 'activated' )
			),
			array(
				'label'				=> array( 'singular' => __( 'Enregistré', 'tify' ), 'plural' => __( 'Enregistrés', 'tify' ) ),
				'count'				=> $this->count_items( 
					array(
						'role__in'		=> $this->set_roles(),
						'meta_query'	=> array(
							array(
								'key' 		=> $wpdb->get_blog_prefix() .'tify_membership_status',
								'value'		=> 'registered'
							)
						)		
					) 
				),
				'hide_empty'		=> true,
				'add_query_args'	=> array( 'member_status' => 'registered' )
			),
			array(
				'label'				=> array( 'singular' => __( 'Confirmé', 'tify' ), 'plural' => __( 'Confirmés', 'tify' ) ),
				'count'				=> $this->count_items( 
					array(
						'role__in'		=> $this->set_roles(),
						'meta_query'	=> array(
							array(
								'key' 		=> $wpdb->get_blog_prefix() .'tify_membership_status',
								'compare'	=> 'NOT EXISTS'
							)
						)		
					) 
				),
				'hide_empty'		=> true,
				'add_query_args'	=> array( 'member_status' => 'confirmed' )
			),
			array(
				'label'				=>  array( 'singular' => __( 'Désactivé', 'tify' ), 'plural' => __( 'Désactivés', 'tify' ) ),
				'count'				=> $this->count_items( 
					array(
						'role__in'		=> $this->set_roles(),
						'meta_query'	=> array(
							'relation'		=> 'OR',
							array(
								'key' 		=> $wpdb->get_blog_prefix() .'tify_membership_status',
								'value'		=> 'disabled'
							),
							array(
								'key' 		=> $wpdb->get_blog_prefix() .'tify_membership_status',
								'compare'	=> 'NOT EXISTS'
							)
						)		
					) 
				),
				'hide_empty'		=> true,
				'add_query_args'	=> array( 'member_status' => 'disabled' )
			)
		);
	}
	
	/** == Récupération de la liste des rôles concernés par la vue == **/
	public function set_bulk_actions()
	{
		return array( 
			'delete' 		=> __( 'Supprimer', 'tify' ),
			'activate' 		=> __( 'Activer', 'tify' ),
			'deactivate' 	=> __( 'Désactiver', 'tify' ),	
		);
	}
	
	/** == Récupération de la liste des rôles concernés par la vue == **/
	public function set_row_actions()
	{
		return array( 
			'edit',
			'activate' 		=> array(
				'label'			=> __( 'Activer', 'tify' ),
				'title'			=> __( 'Activer l\'utilisateur', 'tify' ),
				'link_attrs'	=> array( 'style' => 'color:#006505;' ),
				'nonce'			=> $this->get_item_nonce_action( 'activate' )
			),
			'deactivate' 	=> array(
				'label'			=> __( 'Désactiver', 'tify' ),
				'title'			=> __( 'Désactiver l\'utilisateur', 'tify' ),
				'link_attrs'	=> array( 'style' => 'color:#D98500;' ),	
				'nonce'			=> $this->get_item_nonce_action( 'deactivate' )
			),
			'delete'
		);
	}
	
	/** == Définition de l'ajout automatique des actions de l'élément pour la colonne principale == **/
	public function set_handle_row_actions()
	{
		return false;
	}
	
	/** == Récupération de la liste des rôles concernés par la vue == **/
	public function set_roles()
	{
		return \tiFy\Plugins\Membership\Membership::getRoleNames();
	}
		
	/* = TRAITEMENT = */
	/** == Traitement de l'argument de status du membre == **/
	public function parse_query_arg_member_status( &$query_args, $value )
	{
		global $wpdb;
		
		switch( $value ) :
			case 'activated' :
				$query_args['meta_query']	= array(
					array(
						'key' 		=> $wpdb->get_blog_prefix() .'tify_membership_status',
						'value'		=> 'activated'
					)	
				);		
				break;
			case 'registered' :
				$query_args['meta_query']	= array(
					array(
						'key' 		=> $wpdb->get_blog_prefix() .'tify_membership_status',
						'value'		=> 'registered'	
					)	
				);		
				break;
			case 'confirmed' :
				$query_args['meta_query']	= array(
					array(
						'key' 		=> $wpdb->get_blog_prefix() .'tify_membership_status',
						'value'		=> 'confirmed'	
					)	
				);		
				break;
			case 'disabled' :
				$query_args['meta_query']	= array(
					'relation'		=> 'OR',
					array(
						'key' 		=> $wpdb->get_blog_prefix() .'tify_membership_status',
						'value'		=> 'disabled'
					),
					array(
						'key' 		=> $wpdb->get_blog_prefix() .'tify_membership_status',
						'compare'	=> 'NOT EXISTS'
					)	
				);		
				break;
		endswitch;
	}
	
	/** == Filtrage avancé 
	protected function extra_tablenav( $which ) 
	{
	?>
		<div class="alignleft actions">
		<?php if ( 'top' == $which ) : ?>
			<?php 
				tify_membership_role_dropdown( 
					array(
						'show_option_all'	=> __( 'Tous les rôles', 'tify' ),
						'selected' 			=> ! empty( $_REQUEST['role'] ) ? $_REQUEST['role'] : 0
					)
				); 
				submit_button( __( 'Filtrer', 'tify' ), 'button', 'filter_action', false, array( 'id' => 'role-query-submit' ) );?>
		<?php endif;?>
		</div>
	<?php
	} == **/ 
	
	/* = AFFICHAGE = */
	/** == == **/
	public function single_row( $item ) 
	{
		switch( get_user_option( 'tify_membership_status', $item->ID ) ) :
			default :
			case 'disabled' :
				$class = 'highlighted-error';
				break;
			case 'activated':
				$class = '';
				break;
			case 'registered' :
				$class = 'highlighted-warning';
				break;
			case 'confirmed':
				$class = 'highlighted-info';
				break;
		endswitch;
	?>
		<tr class="<?php echo $class;?>">
	<?php 
		$this->single_row_columns( $item );
	?>
		</tr>	
	<?php
	}
	
	/** == Contenu personnalisé : Login == **/
	public function column_user_login( $item )
	{
		$avatar = get_avatar( $item->ID, 32 );
		
		$row_actions = ( get_user_option( 'tify_membership_status', $item->ID ) === 'activated' ) ? array( 'edit', 'deactivate', 'delete' ) : array( 'edit', 'activate', 'delete' );  
		
		if ( current_user_can( 'edit_user',  $item->ID ) && $this->EditBaseUri ) :
			return sprintf( '%1$s<strong>%2$s</strong>%3$s', $avatar, $this->get_item_edit_link( $item, array(), $item->user_login ), $this->get_row_actions( $item, $row_actions ) );
		else :
			return sprintf( '%1$s<strong>%2$s</strong>', $avatar, $item->user_login );
		endif;
	}
}