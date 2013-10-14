<?php
/**
 * Glav.in
 *
 * A very simple CMS
 *
 *
 * @package		Glav.in
 * @author		Matt Sparks
 * @copyright	Copyright (c) 2013, Matt Sparks (http://www.mattsparks.com)
 * @license		http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link		http://glav.in
 * @since		Version 1.0.0-alpha
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Page {

        /**
         * Contains the name of the latest page loaded with Page->load
         * 
         * @var string 
         */
        private $current_page = '';
    
	/**
	 * Construct
	 */
	public function __construct( $data ) {
		$this->data = $data;
	}
        
        /**
         * Set current page
         * 
         * @param string $page attribute
         */
        private function set_current_page( $page ) {
            $this->current_page = $page;
        }
        
        /**
         * Get current page
         * 
         * @return string
         */
        private function get_current_page() {
            return $this->current_page;
        }

	/**
	 * Get Pages
	 *
	 * @return	array of all pages with full path
	 */	
	public function get_pages() {

		$pages = array();

		foreach ( glob( PAGES_DIR . "*.json" ) as $page ) {
			$pages[] = $page;
		}		

		return $pages;
	}

	/**
	 * Pages List
	 *
	 * @param string optional id attribute
         * @param string optional class_ul adds class name to <ul>
         * @param string optional class_li adds class name to <li>
         * @param string optional class_li_active adds class name to the current page <li>
	 * @return	html list of all pages
	 */	
	public function pages_list( $id='', $class_ul='', $class_li='', $class_li_active='active' ) {

		$pages = $this->get_pages();

		$id = $id ? ' id="'.$id.'"' : '';

		$list  = '<ul' . $id . ($class_ul != '' ? ' class="'.$class_ul.'"' : '') . '>';

		// Make homepage first.
                $list .= '<li ';
                $list .= 'class="'.$class_li . " " . ($this->get_current_page() == 'home' ? $class_li_active : '').'"';
                $list .= '>';
		$list .= '<a href="' . base_url() .'">';
		$list .= 'Home</a></li>';

		foreach( $pages as $page ) {

			$page_name = basename( $page, '.json' );

			if ( $page_name != '404' && $page_name != 'home' ) {

				$content = $this->data->get_content( PAGES_DIR . $page_name );
				$page    = $content['page'];

				// If the page is visible add it to the list.
				if ( $page['visible'] === true ) {
					$list .= '<li ';
                                        $list .= 'class="'.$class_li." ".($this->get_current_page() == $page_name ? $class_li_active : '').'"';
                                        $list .= '>';
					$list .= '<a href="' . base_url() . $page_name . '">';
					$list .= ucwords(str_replace('_', ' ', $content['page']['title']));
					$list .= '</a></li>';
				}
			}
		}

		$list .= '</ul>';

		return $list;

	}	

	/**
	 * Load the page
	 *
	 * @param	string	the page name being requested
	 */
	public function load( $page ) {

		// If $page is empty, lets assume the index/home page has been requested.
		if ( ( $page == '' ) || ( $page == 'index.php' ) || ( $page == 'index.html' ) ) {
			$page = 'home';
		} elseif ( !$this->data->file_exist( PAGES_DIR . $page ) ) {
			// If the page can't be found load the 404 page. 
			$page = '404';
		}
                
                // Set current page
                $this->set_current_page($page);
			
		$content       = $this->data->get_content( PAGES_DIR . $page );
		$page          = $content['page'];
		$template      = $page['template'];
		$template_path = BASEPATH . '/template/' . $template . '.php';

		// If the page isn't visible, set a message.
		if ( $page['visible'] === false ) {
			$page['content'] = 'This page is currently unavailable.';
		}

		// Make sure template exists
		if( file_exists( $template_path ) ) {
			include($template_path);
		} else {
			exit( '<strong>ERROR:</strong> Template "'.$template.'" not found.' );
		}
	}

	/**
	 * Create a page
	 *
	 * @param	array containing all our page info and content
	 * @return	bool
	 */
	public function create($p) {
		$page_name    = trim($p['page_name']);
		$page_title   = trim($p['page_title']);
		$page_content = $p['page_content'];
		$page_visible = $p['page_visible'] == 'true' ? true : false; // making boolean
		$page_created = time();
		$page_file    = PAGES_DIR . str_replace(' ', '_', strtolower($page_name));

		$page = array(
				'page' => array(
						'title'    => $page_title,
						'content'  => $page_content,
						'created'  => $page_created,
						
						// For the time being "page" will be the only option.
						// Eventually users will be able to choose from other templates.
						'template' => 'page',

						'visible'  => $page_visible
					)
			);

		return $this->data->create_file( $page_file, $page );
	}
}
