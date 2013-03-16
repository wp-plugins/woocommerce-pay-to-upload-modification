<?php
/**
 * WC_Pay_To_Upload class.
 * 
 * @extends Airy_Framework
 */
class WC_Pay_To_Upload extends Airy_Framework {
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	function __construct() {
		
		$this->shortname = 'wc_pay_to_upload';
		$this->plugin_name = __('Pay to Upload', 'wc_pay_to_upload' );
		$this->desc = __('Let WC customers pay to upload files when purchasing specific products.', 'wc_pay_to_upload' );
		$this->version = '1.0.0';
		$this->menu_location = 'woocommerce';
		
		$uploads = wp_upload_dir();
		
		$this->fields = array(
			array(
				'name'		=> 'wc_ptu_default_limit',
				'title'		=> __( 'Default Upload Limit', 'wc_pay_to_upload' ),
				'type'		=> 'text',
				'desc'		=> __( 'Default upload limit when activating feature for products.', 'wc_pay_to_upload' ),
				'default'	=> 1,
			),
			array(
				'name'		=> 'wc_ptu_uploads_path',
				'title'		=> __( 'Uploads Path', 'wc_pay_to_upload' ),
				'type'		=> 'text',
				'desc'		=> __( 'Uploads path for all new uploads, subfolders with the order ID name will be created within this.', 'wc_pay_to_upload' ),
				'default'	=> trailingslashit( $uploads['basedir'] ) . 'wc-pay-to-upload',
			),
			array(
				'name'		=> 'wc_ptu_list_type',
				'title'		=> __( 'Allow or Disallow File Types', 'wc_pay_to_upload' ),
				'type'		=> 'select',
				'desc'		=> __( 'The file types that are allowed/disallowed by your selection above.', 'wc_pay_to_upload' ),
				'values'	=> array(
					'all'	=> __( 'Allow All', 'wc_pay_to_upload' ),
					'white'	=> __( 'Whitelist', 'wc_pay_to_upload' ),
					'black'	=> __( 'Blacklist', 'wc_pay_to_upload' ),
				),
			),
			array(
				'name'		=> 'wc_ptu_file_types',
				'title'		=> __('File Types', 'wc_pay_to_upload' ),
				'type'		=> 'text',
				'desc'		=> __('The file types that are allowed/disallowed by your selection above, separate file types by commas.', 'wc_pay_to_upload' ),
			),
		);
		
		add_action( 'add_meta_boxes', array( &$this, 'add_meta_boxes' ) );
		add_action( 'woocommerce_init', array( &$this, 'woocommerce_init' ) );
		add_action( 'save_post', array( &$this, 'save_meta_box' ) );
		add_action( 'woocommerce_view_order', array( &$this, 'uploader' ) );
	}
	
	/**
	 * add_meta_boxes function.
	 * 
	 * @access public
	 * @return void
	 */
	function add_meta_boxes() {
		add_meta_box( 'wc_ptu_enable', __( 'Pay to Upload', 'wc_pay_to_upload' ), array( &$this, 'product_upload_options'), 'product', 'side' );
		add_meta_box( 'wc_ptu_files', __( 'Uploaded Files', 'wc_pay_to_upload' ), array( &$this, 'order_uploaded_files'), 'shop_order', 'side' );
	}
	
	/**
	 * woocommerce_init function.
	 * 
	 * @access public
	 * @return void
	 */
	function woocommerce_init() {
		$statuses = get_terms( 'shop_order_status', array( 'hide_empty' => false ) );
		$values = array();
		foreach( $statuses as $status ) {
			$values[ $status->slug ] = $status->name;
		}
		$this->fields[] = array(
			'name'		=> 'wc_ptu_order_statuses',
			'title'		=> __('Required Status(es)', 'wc_pay_to_upload' ),
			'type'		=> 'multiselect',
			'desc'		=> __('The required order statuses to allow customers to upload files, hold Control or Command to select multiple options.', 'wc_pay_to_upload' ),
			'values'	=> $values,
			'default'	=> array( 'completed', 'processing' ),
		);
		parent::__construct();
	}
	
	/**
	 * order_uploaded_files function.
	 * 
	 * @access public
	 * @param mixed $post
	 * @return void
	 */
	function order_uploaded_files( $post ) {
		$limit = $this->check_for_uploadables( $post->ID );
		for ($i = 1; $i <= $limit; $i++) {
			$url = home_url( str_replace( ABSPATH, '', get_post_meta( $post->ID, '_wc_uploaded_file_path_' . $i, true ) ) );
			$name = get_post_meta( $post->ID, '_wc_uploaded_file_name_' . $i, true );
			if( !empty( $url ) && !empty( $name ) ) {
				printf( __('File #%s: <a href="%s" target="_blank">%s</a>', 'wc_pay_to_upload'), $i, $url, $name );
			} else {
				printf( __('File #%s has not been uploaded.', 'wc_pay_to_upload'), $i );
			}
			echo '<br/>';
		}
	}
	
	/**
	 * product_upload_options function.
	 * 
	 * @access public
	 * @param mixed $post
	 * @return void
	 */
	function product_upload_options( $post ) {
		wp_nonce_field( 'wc_ptu_nonce', 'wc_ptu_nonce' );
		echo '<p>';
			echo '<label for="_wc_ptu_enable" style="float:left; width:50px;">' . __('Enable', 'wc_pay_to_upload' ) . '</label>';
			echo '<input type="hidden" name="_wc_ptu_enable" value="0" />';
			echo '<input type="checkbox" id="_wc_ptu_enable" class="checkbox" name="_wc_ptu_enable" value="1" ' . checked( get_post_meta( $post->ID, '_wc_ptu_enable', true ), 1, false ) . ' />';
		echo '</p>';
		echo '<p>';
			$value = get_post_meta( $post->ID, '_wc_ptu_limit', true );
			$value = ( !empty( $value ) ) ? $value : $this->wc_ptu_default_limit;
			echo '<label for="_wc_ptu_limit" style="float:left; width:50px;">' . __('Limit', 'wc_pay_to_upload' ) . '</label>';
			echo '<input type="text" id="_wc_ptu_limit" class="short" name="_wc_ptu_limit" value="' . $value . '" />';
		echo '</p>';
	}
	
	/**
	 * save_meta_box function.
	 * 
	 * @access public
	 * @param mixed $post_id
	 * @return void
	 */
	function save_meta_box( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( !isset( $_POST['wc_ptu_nonce'] ) || !wp_verify_nonce( $_POST['wc_ptu_nonce'], 'wc_ptu_nonce' ) ) return;
		update_post_meta( $post_id, '_wc_ptu_enable', (int) $_POST['_wc_ptu_enable'] );
		update_post_meta( $post_id, '_wc_ptu_limit', (int) $_POST['_wc_ptu_limit'] );		
	}
	
	/**
	 * check_for_uploadables function.
	 * 
	 * @access public
	 * @param mixed $order_id
	 * @return void
	 */
	function check_for_uploadables( $order_id ) {
	//Changed the Previous parameters to existing parameters for wordpress Version 3.5.1 Ashok G
	global $woocommerce;
	$order = new WC_Order( $order_id );
	$items = get_post_meta( $order_id ,'_order_items', true);
	$new_items = $order->get_items();
		


$limits = 0;		
		if( is_array( $new_items ) ) {
			foreach( $new_items as $item ) {
				$limit = (int) get_post_meta( $item['product_id'], '_wc_ptu_limit', true ); //Changed $item['item_id'] to $item['product_id'] for wordpress version 3.5.1 Ashok G
				if( get_post_meta( $item['product_id'], '_wc_ptu_enable', true ) == 1 && $limit > 0 ) {
					$limits += $limit;
				}
			}
		} else {
			echo wpautop( __( 'Sorry, no files have been uploaded yet.', 'wc_pay_to_upload' ) );
			
		}
		return $limits;
		
	}
	
	/**
	 * uploader function.
	 * 
	 * @access public
	 * @param mixed $order_id
	 * @return void
	 */
	function uploader( $order_id ) {
		$order = new WC_Order( $order_id );
		$limits = $this->check_for_uploadables( $order_id );
		if( $limits < 1 || ( ( is_array( $this->wc_ptu_order_statuses ) && !in_array( $order->status, $this->wc_ptu_order_statuses ) ) ) || $order->status == $this->wc_ptu_order_statuses ) return;
		echo '<h2>' . __( 'Upload Files', 'wc_pay_to_upload' ) . '</h2>';
		if( isset( $_FILES ) ) {
			$path = trailingslashit( trailingslashit( $this->wc_ptu_uploads_path ) . $order_id );
			foreach( $_FILES as $key => $file ) {
				if( empty( $file['name'] ) ) continue;
				wp_mkdir_p( $path );
				$filepath = $path . $file['name'];
				$ext = strtolower( pathinfo( $filepath, PATHINFO_EXTENSION ) );
				$types = explode( ',', $this->wc_ptu_file_types );
				foreach( $types as $k => $v ) { $types[$k] = strtolower( trim( $v ) ); }
				switch( $this->wc_ptu_list_type ) {
					case 'all':
						$allow = true;
						break;
					case 'white':
						if( in_array( $ext, $types ) ) $allow = true;
						else $allow = false;
						break;
					case 'black':
						if( in_array( $ext, $types ) ) $allow = false;
						else $allow = true;
						break;
				}
				if( $allow == true ) {
					if( copy( $file['tmp_name'], $filepath ) ) {
						echo '<p class="success">' . __( 'Your file(s) were uploaded successfully.', 'wc_pay_to_upload') . '</p>';
						update_post_meta( $order_id, '_wc_uploaded_file_name_' . $key, $file['name'] );
						update_post_meta( $order_id, '_wc_uploaded_file_path_' . $key, $filepath );
					} else {
						echo '<p class="error">' . __( 'There was an error in uploading your file(s).', 'wc_pay_to_upload') . '</p>';
					}
				} else {
					echo '<p class="error">' . sprintf( __( 'There %s filetype is not allowed.', 'wc_pay_to_upload'), $ext ) . '</p>';
				}
			}
		}
		
		$max_upload = (int)(ini_get('upload_max_filesize'));
		$max_post = (int)(ini_get('post_max_size'));
		$memory_limit = (int)(ini_get('memory_limit'));
		$upload_mb = min($max_upload, $max_post, $memory_limit);
		
		echo '<form enctype="multipart/form-data" action="" method="POST">';
			$upload = false;
			for ($i = 1; $i <= $limits; $i++) {
				echo '<label for="' . $i . '">File ' . $i . ': </label>';
				$name = get_post_meta( $order_id, '_wc_uploaded_file_name_' . $i, true );
				if( empty( $name ) ) {
					echo '<input type="file" name="' . $i . '" />';
					$upload = true;
				} else {
					echo $name;
				}
				echo '<br/>';
			}
			if( $upload ) {
				echo '<input type="submit" class="button" value="' . __( 'Upload', 'wc_pay_to_upload' ) . '" /><br/>';
				echo '<p>' . sprintf( __( 'Max upload size: %s', 'wc_pay_to_upload' ), $upload_mb ) . 'MB</p>';
			}
		echo '</form>';
	}
}