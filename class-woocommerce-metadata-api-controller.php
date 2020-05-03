<?php
class WC_REST_WooCommerce_Metadata_API_By_WooPOS_Controller extends WC_REST_CRUD_Controller{
	protected $namespace = 'wc/v3';
	protected $rest_base = 'metadata';
	
	public $post_fields = array( 'post_name', 'post_title', 'post_content' );

	public function register_routes(){
		register_rest_route( $this->namespace,  '/' . $this->rest_base, array(
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array( $this, 'update_meta' ),
				'permission_callback' => array( $this, 'check_permission_to_edit_posts' ),
				'args' => $this->get_params()
			)
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/delete', array(
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array( $this, 'delete_meta' ),
				'permission_callback' => array( $this, 'check_permission_to_edit_posts' ),
				'args' => $this->get_params()
			)
		) );
		register_rest_route( $this->namespace,  '/' . $this->rest_base . '/list', array(
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array( $this, 'list_meta' ),
				'permission_callback' => array( $this, 'check_permission_to_edit_posts' ),
				'args' => $this->get_params()
			)
		) );
	}
	
	public function check_permission_to_edit_posts(){
		return current_user_can( 'edit_posts' );
	}
	
	public function get_params(){
		$params = array(
			'posts' => array(
				'required'			=> true,
				'description'		=> __( 'Array of posts to change.', 'woopos_metadata' ),
				'type'				=> 'array',
				'items'				=> array(
					'description'		=> __( 'Post object', 'woopos_metadata' ),
					'type' 				=> 'object',
					'properties'		=> array(
						'id' => array(
							'required'			=> true,
							'description' 		=> __( 'Post ID.', 'woopos_metadata' ),
							'type'				=> 'integer'
						),
						'data' => array(
							'required'			=> true,
							'description'		=> __( 'Array of meta and taxonomy fields to change.', 'woopos_metadata' ),
							'type'				=> 'array',
							'items'				=> array(
								'description'		=> __( 'Array of meta and taxonomy fields to change.', 'woopos_metadata' ),
								'type' 				=> 'object',
								'properties' 		=> array(
									'key' => array(
										'description'		=> __( 'Field or taxonomy name.', 'woopos_metadata' ),
										'type'				=> 'string',
										'sanitize_callback'	=> 'sanitize_text_field'
									),
									'value' => array(
										'description'		=> __( 'Value.', 'woopos_metadata' ),
										'default'			=> '',
										'sanitize_callback'	=> 'sanitize_text_field'
									),
									'type' => array(
										'description'	=> __( 'Key type. Possible values are "meta" and "taxonomy".', 'woopos_metadata' ),
										'type'			=> 'string',
										'enum'			=> array( 'post', 'meta', 'taxonomy' )
									)
								)								
							)
						)
					)
				)
			)
		);
		return $params;
	}
	
	public function update_meta($request){
		$response = $this->iterate_through_data($request, 'update');
		return $response;
	}
	public function delete_meta($request){
		$response = $this->iterate_through_data($request, 'delete');
		return $response;
	}
	public function list_meta($request){
		$response = $this->iterate_through_data($request, 'list_data');
		return $response;
	}
	
	public function iterate_through_data($request, $action){		
		$response = array('posts' => array());
		foreach($request['posts'] as $i => $post){
			try{
				$post_id = $post['id'];
				if( get_post_status($post_id) === false ){
					throw new Exception("Post with id '{$post_id}' does not exist.");
				}
				
				$response_data = array();
				foreach($post['data'] as $j => $meta){
					try{
						if( !isset($meta['key']) || empty($meta['key']) ){
							throw new Exception('Meta key is required.');
						}
						$key = $meta['key'];
						$value = $meta['value'];
						
						if( isset($meta['type']) ){
							$type = $meta['type'];
						}elseif( taxonomy_exists($key) ){
							$type = 'taxonomy';
						}elseif( $post = get_post($post_id) && isset($post->$key) ){
							$type = 'post';
						}else{
							$type = 'meta';
						}
						
						$result = $this->$action($post_id, $type, $key, $value);
						
						$response_data[] = $result;
					}catch(Exception $e){
						$response_data[] = array_merge($meta, array(
							'result' => 'error',
							'message' => $e->getMessage()
						));
					}
				}
				$response['posts'][] = array(
					'id' => $post_id,
					'data' => $response_data
				);
			}catch(Exception $e){
				$response['posts'][] = array(
					'id' => $post_id,
					'result' => 'error',
					'message' => $e->getMessage()
				);
			}
		}
		return $response;
	}
	
	public function update($post_id, $type, $key, $value){
		switch($type){
			case 'post':
				$post = array(
					'ID' => $post_id,
					$key => $value
				);
				$update_post = wp_update_post( $post, true );
				if( is_wp_error($update_post) ){
					throw new Exception( $update_post->get_error_message() );
				}
				break;
			
			case 'meta':
				if( $value != get_post_meta( $post_id, $key, true ) ){
					$update_post_meta = update_post_meta( $post_id, $key, $value );
					if( $update_post_meta === false ){
						throw new Exception('Meta update failed.');
					}
				}
				break;
			
			case 'taxonomy':
				$post_type = get_post_type($post_id);
				if( is_object_in_taxonomy($post_type, $key) === false ){
					throw new Exception("Taxonomy '{$key}' is not applicable to '{$post_type}' post type.");
				}
				if( empty($value) ){
					throw new Exception('Value is required to add taxonomy term.');
				}
				$add_terms = wp_set_object_terms( $post_id, $value, $key, true );
				if( is_wp_error($add_terms) ){
					throw new Exception( $add_terms->get_error_message() );
				}
				break;
		}
		return array(
			'type' => $type,
			'key' => $key,
			'value' => $value,
			'status' => 'success'
		);
	}
	
	public function delete($post_id, $type, $key, $value){
		switch($type){
			case 'post':
				$post = array(
					'ID' => $post_id,
					$key => ''
				);
				$update_post = wp_update_post( $post, true );
				if( is_wp_error($update_post) ){
					throw new Exception( $update_post->get_error_message() );
				}
				break;
			
			case 'meta':
				$delete_post_meta = delete_post_meta($post_id, $key);
				if( $update_post_meta === false ){
					throw new Exception('Meta delete failed.');
				}
				break;
			
			case 'taxonomy':
				$post_type = get_post_type($post_id);
				if( is_object_in_taxonomy($post_type, $key) === false ){
					throw new Exception("Taxonomy '{$key}' is not applicable to '{$post_type}' post type.");
				}
				if( empty($value) ){
					throw new Exception('Value is required to delete taxonomy term.');
				}
				$remove_term = wp_remove_object_terms( $post_id, $value, $key );
				if( is_wp_error($remove_term) ){
					throw new Exception( $add_terms->get_error_message() );
				}elseif( $remove_term === false ){
					throw new Exception( 'Term delete failed.' );
				}
				break;
		}
		return array(
			'type' => $type,
			'key' => $key,
			'value' => $value,
			'status' => 'success'
		);
	}
	
	public function list_data($post_id, $type, $key, $value){
		switch($type){
			case 'post':
				$post = get_post($post_id);
				if( !isset($post->$key) ){
					throw new Exception("Post table don't have '{$key}' field.");
				}
				$value = $post->$key;
				break;
			
			case 'meta':
				$value = get_post_meta($post_id, $key, true);
				break;
			
			case 'taxonomy':
				$post_type = get_post_type($post_id);
				if( is_object_in_taxonomy($post_type, $key) === false ){
					throw new Exception("Taxonomy '{$key}' is not applicable to '{$post_type}' post type.");
				}
				$terms = wp_get_object_terms( $post_id, $key, array( 'fields' => 'slugs' ) );
				$value = implode('|', $terms);
				break;
		}
		return array(
			'type' => $type,
			'key' => $key,
			'value' => $value
		);
	}
}