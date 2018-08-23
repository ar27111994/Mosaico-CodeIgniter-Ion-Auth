<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_templates extends CI_Controller {

    protected $mosaico_config = array();
	//Constructor
    public function __construct()
    {
        parent::__construct();
		$this->load->library('ion_auth');

		if ( $this->ion_auth->logged_in() ) 
		{
			$this->load->database();
	
			//LIBS
			$this->load->library('email');
			
			//Configs
			$this->config->load('mosaico_config');
			$this->mosaico_config = $this->config->item('mosaico_backend');
		}
	}
	
    public function index()
	{
		if ($this->ion_auth->logged_in())
		{
		    $data['templates'] = json_encode($this->db->get("email_templates")->result_array(), JSON_PRETTY_PRINT);
			$data['title']="Mosaico Email Templates";
			$data['descr']="";

			$this->load->mosaico(FALSE, $data);
			
			//$this->_example_output($output);
		}
		else {
			redirect('auth/login', 'refresh');
		}
	}
	
	public function forms()
	{
		if ($this->ion_auth->logged_in())
		{
			if ($this->ion_auth->logged_in()) {
	
				$crud = new grocery_CRUD();
	 
				$crud->set_table('forms_email_templates');
				$crud->set_relation('form', 'rsm_form', 'name_en');
				$crud->set_relation('template', 'email_templates', 'template_name');
				
				
				$crud->required_fields('form', 'template');

        		// only admin can reset user password
				$this->data['gcrud'] = $crud->render();
				
				
				$this->data['title']="Email Templates <-> Forms Mappings";
				$this->data['descr']="";
				$this->data['tpl_part']=$this->data['controller']."_forms";
				$this->_new_output($this->data);
			}
		}
		else {
			redirect('auth/login', 'refresh');
		}
	}
		
	public function editor()
	{
		if ($this->ion_auth->logged_in())
		{
		    //$this->load->library('../core/security');
		    if($this->input->get("template")){
		        //check if provided template url is absolute or relative 
		        $pattern = "/^(?:ftp|https?|feed):\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
                            (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
                            (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
                            (?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";
		        $data['template'] = (((bool) preg_match($pattern, $this->input->get("template"))) ? '' : base_url() . 'mosaico/') . $this->input->get("template");
		    }
		    
		    if($this->input->get("name")){
		        $data['name'] = $this->input->get("name");
		    }
		    
			$data['title']="Mosaico Email Template Editor";
			$data['descr']="";
			$this->load->mosaico(TRUE, $data);
			
			//$this->_example_output($output);
		}
		else {
			redirect('auth/login', 'refresh');
		}
	}
	
	public function save_template(){
	    if ($this->ion_auth->logged_in() && $this->input->method(TRUE) == "POST")
		{
    	    $hash = $this->input->post( 'hash' );
            $meta = $this->input->post( 'metadata' );
            $name = $this->input->post( 'name' );
            $content = $this->input->post( 'content' );
            $html = $this->input->post( 'html' );
            
            $data = array();
            
            if( isset( $html ) && $html !== '' ){
                $data['template_html'] = $html;
            }
            
            if( isset( $name ) && $name !== '' ){
                $data['template_name'] = $name;
            }

            if( isset( $meta ) && $meta !== '' ){
                $data['template_metadata'] = $meta;
            }
            
            if( isset( $content ) && $content !== '' ){
                $data['template_content'] = $content;
            }
            $data['template_hash'] = $hash;
            $this->db->where("template_hash", $hash);
            $this->db->from('email_templates');
            if($this->db->count_all_results() == 1){
                $this->db->set($data);
                $this->db->where("template_hash", $hash);
                $this->db->update('email_templates');
            } else{
                $data['template_hash'] = $hash;
                $this->db->insert('email_templates', $data);
            }
            $this->output->set_status_header(200)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Template saved successfully." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}    
        else {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please login to continue." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
	}
	
	public function get_template($template_hash){
	    if ($this->ion_auth->logged_in() && $this->input->method(TRUE) == "GET")
		{
            $this->db->where("template_hash", $template_hash);
            $this->db->from('email_templates');
            if($this->db->count_all_results() == 1){
                $result = $this->db->select('template_content, template_metadata')->get_where('email_templates', array("template_hash" => $template_hash))->row();
                $this->output->set_status_header(200)->set_content_type('application/json')->set_output( json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		    }
            else{
                $this->output->set_status_header(404)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Template with the key / hash " . $template_hash . " not found." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
            }
            
		}    
        else {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please login to continue." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
	}
	
	public function delete_template($template_hash){
	    if ($this->ion_auth->logged_in() && $this->input->method(TRUE) == "GET")
		{
            $this->db->where("template_hash", $template_hash);
            $this->db->from('email_templates');
            if($this->db->count_all_results() == 1){
                $result = $this->db->delete('email_templates', array("template_hash" => $template_hash));
                $this->output->set_status_header(200)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Template deleted successfully!" ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		    }
            else{
                $this->output->set_status_header(404)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Template with the key / hash " . $template_hash . " not found." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
            }
            
		}    
        else {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please login to continue." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
	}
	
	public function ProcessUploadRequest()
    {
        if ($this->ion_auth->logged_in())
		{

        	$files = array();
        
        	if ( $this->input->method(TRUE) == "GET" )
        	{
        
        		$dir = scandir( $this->mosaico_config[ 'BASE_DIR' ] . $this->mosaico_config[ 'THUMBNAILS_DIR' ] );
        		foreach ( $dir as $file_name )	
        		{
        			$file_path = $this->mosaico_config[ 'BASE_DIR' ] . $this->mosaico_config[ 'UPLOADS_DIR' ] . $file_name;
        			if ( is_file( $file_path ) )
        			{
        				$size = filesize( $file_path );
        				
        				$file = [
        					"name" => $file_name,
        					"url" => $this->mosaico_config[ 'BASE_URL' ] . $this->mosaico_config[ 'UPLOADS_URL' ] . $file_name,
        					"size" => $size
        				];
        
        
        				if ( file_exists( $this->mosaico_config[ 'BASE_DIR' ] . $this->mosaico_config[ 'THUMBNAILS_DIR' ] . $file_name ) )
        				{
        					$file[ "thumbnailUrl" ] = $this->mosaico_config[ 'BASE_URL' ] . $this->mosaico_config[ 'THUMBNAILS_URL' ] . $file_name;
        				} 
        
        				$files[] = $file;
        			}
        		}
        	}
        	else if ( !empty( $_FILES ) )
        	{
        		foreach ( $_FILES[ "files" ][ "error" ] as $key => $error )
        		{
        			if ( $error == UPLOAD_ERR_OK )
        			{
        				$tmp_name = $_FILES[ "files" ][ "tmp_name" ][ $key ];
        
        				$file_name = $_FILES[ "files" ][ "name" ][ $key ];
        				
        				$file_path = $this->mosaico_config[ 'BASE_DIR' ] . $this->mosaico_config[ 'UPLOADS_DIR' ] . $file_name;
        
        				if ( move_uploaded_file( $tmp_name, $file_path ) === TRUE )
        				{
        					$size = filesize( $file_path );
        
        					$image = new Imagick( $file_path );
        
        					$image->resizeImage( $this->mosaico_config[ 'THUMBNAIL_WIDTH' ], $this->mosaico_config[ 'THUMBNAIL_HEIGHT' ], Imagick::FILTER_LANCZOS, 1.0, TRUE );
        					$image->writeImage( $this->mosaico_config[ 'BASE_DIR' ] . $this->mosaico_config[ 'THUMBNAILS_DIR' ] . $file_name );
        					$image->destroy();
        					
        					$file = array(
        						"name" => $file_name,
        						"url" => $this->mosaico_config[ 'BASE_URL' ] . $this->mosaico_config[ 'UPLOADS_URL' ] . $file_name,
        						"size" => $size,
        						"thumbnailUrl" => $this->mosaico_config[ 'BASE_URL' ] . $this->mosaico_config[ 'THUMBNAILS_URL' ] . $file_name
        					);
        
        					$files[] = $file;
        				}
        				else
        				{
        					$this->output->set_status_header(400)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Error Uploading File(s). Make sure that the required directories exist on the server." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) )->_display();
        				    exit;
        				}
        			}
        			else
        			{
        				$this->output->set_status_header(500)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Error Uploading File(s)." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) )->_display();
        				exit;
        			}
        		}
        	}
        	
        	$this->output->set_header( "Content-Type: application/json; charset=utf-8" );
        	$this->output->set_header( "Connection: close" );
        
        	$this->output->set_content_type('application/json')->set_output( json_encode( array( "files" => $files ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
    	else {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please login to continue." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
    }
    
    /**
     * handler for img requests
     */
    public function ProcessImgRequest()
    {
        if ( $this->input->method(TRUE) == "GET" )
		{
			$method = $this->input->get( "method" );
	
			$params = explode( ",", $this->input->get( "params" ) );
	
			$width = (int) $params[ 0 ];
			$height = (int) $params[ 1 ];
	
			if ( $method == "placeholder" )
			{
				$image = new Imagick();
	
				$image->newImage( $width, $height, "#707070" );
				$image->setImageFormat( "png" );
	
				$x = 0;
				$y = 0;
				$size = 40;
	
				$draw = new ImagickDraw();
	
				while ( $y < $height )
				{
					$draw->setFillColor( "#808080" );
	
					$points = [
						[ "x" => $x, "y" => $y ],
						[ "x" => $x + $size, "y" => $y ],
						[ "x" => $x + $size * 2, "y" => $y + $size ],
						[ "x" => $x + $size * 2, "y" => $y + $size * 2 ]
					];
	
					$draw->polygon( $points );
	
					$points = [
						[ "x" => $x, "y" => $y + $size ],
						[ "x" => $x + $size, "y" => $y + $size * 2 ],
						[ "x" => $x, "y" => $y + $size * 2 ]
					];
	
					$draw->polygon( $points );
	
					$x += $size * 2;
	
					if ( $x > $width )
					{
						$x = 0;
						$y += $size * 2;
					}
				}
	
				$draw->setFillColor( "#B0B0B0" );
				$draw->setFontSize( $width / 5 );
				$draw->setFontWeight( 800 );
				$draw->setGravity( Imagick::GRAVITY_CENTER );
				$draw->annotation( 0, 0, $width . " x " . $height );
	
				$image->drawImage( $draw );
	
				$this->output->set_header( "Content-type: image/png" );
	
				echo $image;
			}
			else
			{
				$file_name = $this->input->get( "src" );
	
				$path_parts = pathinfo( $file_name );
	
				switch ( $path_parts[ "extension" ] )
				{
					case "png":
						$mime_type = "image/png";
						break;
	
					case "gif":
						$mime_type = "image/gif";
						break;
	
					default:
						$mime_type = "image/jpeg";
						break;
				}
	
				$file_name = $path_parts[ "basename" ];
	
				$image = $this->ResizeImage( $file_name, $method, $width, $height );
	
				$this->output->set_header( "Content-type: " . $mime_type );
	
				echo $image;
			}
		}
    }

    /**
     * handler for dl requests
     */
    public function ProcessDlRequest()
    {
        if ($this->ion_auth->logged_in())
		{
        	$html = $this->InsertImages($this->input->post(  "html" ));
        
        	/* perform the requested action */
        
        	switch ( $this->input->post( "action" ) )
        	{
        		case "download":
        		{
        			$this->load->helper('download');
                    force_download($this->input->post( "filename" ), $html);
                    
        			break;
        		}
        
        		case "email":
        		{
        			$to = $this->input->post( "rcpt" );
        			$subject = $this->input->post( "subject" );
        			
        			
        			
        			if ( !$this->email->valid_email( $to ) )
        			{
        				$this->output->set_status_header(500)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please provide a valid email." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) )->_display();
        				exit;
        			}
        			$this->config->load('email', TRUE);
        			$email_config = $this->config->item("email_config", "email");
        			$this->email->initialize($email_config);
        			$this->email->to($to);
                    $this->email->from($this->ion_auth->user()->row()->username . '@' . parse_url( base_url(), PHP_URL_HOST ),'CRM');
                    $this->email->set_newline("\r\n");
                    $this->email->subject($subject);
                    $this->email->message($html);
                    if($this->email->send())
                    {
                        $this->email->start_process();
                    }
                    else
                    {
                        $this->output->set_status_header(500)->set_content_type('application/json')->set_output( json_encode( array( "msg" => show_error($this->email->print_debugger()) ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) )->_display();
                    }
        			break;
        		}
        	}
        }
    	else {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please login to continue." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
    }
        
    protected function InsertImages($html)
    {
        if ($this->ion_auth->logged_in())
		{
        	/* create static versions of resized images */
        	$matches = [];
        
        	$num_full_pattern_matches = preg_match_all( '#<img.*?src=".*(img[^"]*)#i', $html, $matches); 
        
        
            for ( $i = 0; $i < $num_full_pattern_matches; $i++ ) 
        	{
        
        		if ( stripos( $matches[ 1 ][ $i ], "img/?src=" ) !== FALSE )
        		{
        
        		    $src_matches = [];
        
        
        			if ( preg_match( '#.*src=(.*)&amp;method=(.*)&amp;params=(.*)#i', $matches[ 1 ][ $i ], $src_matches ) !== FALSE )
        			{
        
        
        				$file_name = urldecode( $src_matches[ 1 ] );
        
        
        
        				$file_name = substr( $file_name, strlen( $this->mosaico_config[ 'BASE_URL' ] . $this->mosaico_config[ 'UPLOADS_URL' ] ) );
        
        				$method = urldecode( $src_matches[ 2 ] );
        
        				$params = urldecode( $src_matches[ 3 ] );
        				$params = explode( ",", $params );
        				$width = (int) $params[ 0 ];
        				$height = (int) $params[ 1 ];
        
        
        
        
        				if ( $width == 0 || $height == 0 )
        				{
        					    $image = new Imagick( $this->mosaico_config[ 'BASE_DIR' ] . $this->mosaico_config[ 'UPLOADS_DIR' ] . $file_name );
        					    $image_geometry = $image->getImageGeometry();
        					    $image_ratio =  (double) $image_geometry[ "width" ] / $image_geometry[ "height" ];
        					    if ( $width == 0 ) {
        					        $width =  $height * $image_ratio;
         					        $width = (int) $width;
        					    } else {
        					        $height = $width / $image_ratio;
        						    $height = (int) $height;
        					    }
        	    		}
        
        
        				$static_file_name = $method . "_" . $width . "x" . $height . "_" . $file_name;
        
        				
        				$html = str_ireplace(  $this->mosaico_config[ 'BASE_URL'] . $matches[ 1 ][ $i ], $this->mosaico_config[ 'SERVE_URL'] . $this->mosaico_config[ 'STATIC_URL' ] . ( $static_file_name ), $html );
        
        				$image = $this->ResizeImage( $file_name, $method, $width, $height );
        				$image->writeImage( $this->mosaico_config[ 'BASE_DIR' ] . $this->mosaico_config[ 'STATIC_DIR' ] . $static_file_name );
        			}
        
        		}
        
        
        	}
        	
        	return $html;
        
        }
    	else {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please login to continue." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
    }
    
    public function send_pending_emails()
    {
        $this->email->send_queue();
	}
	
	
    /**
     * function to resize images using resize or cover methods
     */
    
    function ResizeImage( $file_name, $method, $width, $height )
    {
        if ($this->ion_auth->logged_in())
		{
        	$image = new Imagick( $this->mosaico_config[ 'BASE_DIR' ] . $this->mosaico_config[ 'UPLOADS_DIR' ] . $file_name );
        
        	if ( $method == "resize" )
        	{
        	    $image->resizeImage( $width, $height, Imagick::FILTER_LANCZOS, 1.0 );
        	}
        	else // $method == "cover"
        	{
        		$image_geometry = $image->getImageGeometry();
        
        		$width_ratio = $image_geometry[ "width" ] / $width;
        		$height_ratio = $image_geometry[ "height" ] / $height;
        
        		$resize_width = $width;
        		$resize_height = $height;
        
        		if ( $width_ratio > $height_ratio )
        		{
        			$resize_width = 0;
        		}
        		else
        		{
        			$resize_height = 0;
        		}
        
        		$image->resizeImage( $resize_width, $resize_height, Imagick::FILTER_LANCZOS, 1.0 );
        
        		$image_geometry = $image->getImageGeometry();
        
        		$x = ( $image_geometry[ "width" ] - $width ) / 2;
        		$y = ( $image_geometry[ "height" ] - $height ) / 2;
        
        		$image->cropImage( $width, $height, $x, $y );
        	}
        	
        	return $image;
		}
    	else {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please login to continue." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
    }

	
}
?>