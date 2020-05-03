<?php
class WC_REST_WooCommerce_Media_API_By_WooPOS_Controller extends WC_REST_CRUD_Controller{
	protected $namespace = 'wc/v3';
	protected $rest_base = 'media';


	public function register_routes(){
		register_rest_route( $this->namespace,  '/' . $this->rest_base, array(
			array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'upload_image' ),
			),
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'list_images' )
			)
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base. '/(?P<id>\d+)', array(
			array(
				'methods' => WP_REST_Server::DELETABLE,
				'callback' => array( $this, 'delete_image' ),
				'args' => $this->get_params_delete()
			)
		) );
	}

	public function get_params_delete(){
		return array(
			'force' => array(
				'type'        => 'boolean',
				'default'     => false,
				'description' => __( 'Whether to bypass trash and force deletion.' ),
			),
		);
	}

	public function upload_image($request){
		$response = array();
		try{
		
			$file   = $request->get_file_params();
			
			if(!isset($file['file'])){
				throw new Exception('File is required.');
			}

			if( !class_exists('WP_REST_Attachments_Controller') ){
				throw new Exception('WP API not installed.');
			}
			
			$media_controller = new WP_REST_Attachments_Controller( 'attachment' );


			$permission_check = $media_controller->create_item_permissions_check( $request );
			if( is_wp_error($permission_check) ){
				throw new Exception( $permission_check->get_error_message() );
			}

			$result = $media_controller->create_item( $request );
			$response = rest_ensure_response( $result );
		}
        catch(Exception $e){
			$response['result'] = "error";
			$response['message'] = $e->getMessage();
		}

		if( !empty($request['media_path']) ){
			remove_filter( 'upload_dir', array( $this, 'change_wp_upload_dir' ) );
		}

		return $response;
	}

	public function list_images($request){
		$response = array();
		try{
			if( !class_exists('WP_REST_Attachments_Controller') ){
				throw new Exception('WP API not installed.');
			}
			$media_controller = new WP_REST_Attachments_Controller( 'attachment' );

			$result = $media_controller->get_items( $request );
			$response = rest_ensure_response( $result );
		}
        catch(Exception $e){
			$response['result'] = "error";
			$response['message'] = $e->getMessage();
		}
		return $response;
	}

	public function delete_image($request){
		$response = array();
		try{
			if( !class_exists('WP_REST_Attachments_Controller') ){
				throw new Exception('WP API not installed.');
			}
			$media_controller = new WP_REST_Attachments_Controller( 'attachment' );

			$get_params = $request->get_query_params();
			$get_params['force'] = true;
			$request->set_query_params( $get_params );

			$result = $media_controller->delete_item( $request );
			$response = rest_ensure_response( $result );
		}
        catch(Exception $e){
			$response['result'] = "error";
			$response['message'] = $e->getMessage();
		}
		return $response;
	}

}