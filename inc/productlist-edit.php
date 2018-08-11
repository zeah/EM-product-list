<?php 
defined('ABSPATH') or die('Blank Space');


/*
*/
final class Productlist_edit {
	/* singleton */
	private static $instance = null;


	public static function get_instance() {
		if (self::$instance === null) self::$instance = new self();
		return self::$instance;
	}



	private function __construct() {


		add_action('manage_productlist_posts_columns', array($this, 'column_head'));
		add_filter('manage_productlist_posts_custom_column', array($this, 'custom_column'));
		add_filter('manage_edit-productlist_sortable_columns', array($this, 'sort_column'));
		
		/* metabox, javascript */
		add_action('add_meta_boxes_productlist', array($this, 'create_meta'));
		/* hook for page saving/updating */
		add_action('save_post', array($this, 'save'));


		add_filter('emtheme_doc', array($this, 'add_doc'), 99);

	}

	/**
	 * theme filter for populating documentation
	 * 	
	 * @param [array] $data [array passing through theme filter]
	 */
	public function add_doc($data) {
		$data['productlist']['title'] = '<h1 id="productlist">Lånlist Sverige (Plugin)</h1>';

		$data['productlist']['index'] = '<li><h2><a href="#productlist">Product List (Plugin)</a></h2>
											<ul>
												<li><a href="#productlist-shortcode">Shortcode</a></li>
												<li><a href="#productlist-aldri">Aldri vis</a></li>
												<li><a href="#productlist-sort">Sorting order</a></li>
												<li><a href="#productlist-overview">Overview</a></li>
											</ul>
										</li>';
		$data['productlist']['info'] = '<li id="productlist-shortcode"><h2>Shortcodes</h2>
										<ul>
											<li><b>[product]</b>
											<p>[product] will show all.</p>
											</li>
											<li><b>[product name="xx, yy"]</b>
											<p>Shows only the loans that is mentioned in the shortcode.
											<br>The name needs to be the slug-name of the product.
											<br>Loans are sorted by the position they have in name=""
											<br>eks.: [product name="lendo"] will only show the product with slug-name "lendo".
											<br>[product name="lendo, axo"] will show 2 loans: lendo and axo.</p>
											<li><b>[product product="xx"]</b>
											<p>product must match the slug-name of the product type.
											<br>The loans are sorted by the sort order given in load edit page for that type.
											<br>Eks: [product product="frontpage"] shows all loans with the category "frontpage" in the order of lowest number
											<br>of field "Sort frontpage" has in the load editor page.</p>
											</li>
											</li>
											<li><b>[product-bilde name="xx"]</b>
											<p>Name is required. Will show the product\'s thumbnail.</p></li>'.
											// <li><b>[product-bestill name="xx"]</b>
											// <p>Name is required. Will show the product\'s button.</p></li>
										'</ul>
										</li>
										<li id="productlist-aldri"><h2>Aldri vis</h2>
										<p>If tagged, then the product will never appear on the front-end.</p>
										</li>
										</li>
										<li id="productlist-sort"><h2>Sorting order</h2>
										<p>The loans will be shown with the lowest "Sort"-value first.
										<br>When only showing a specific category on product page, then the sort order column will reflect 
										<br>that category\'s sort order.</p>
										</li>
										<li id="productlist-overview"><h2>Overview</h2>
										<p> The <a target="_blank" href="'.get_site_url().'/wp-admin/edit.php?post_type=productlist&page=productlist-overview">overview page</a> will show every post and page and whether or not there are
										<br>any product shortcodes in them.
										<br>You can sort the columns alphabetically</p>
										</li>
										';

		return $data;
	}

	/**
	 * wp filter for adding columns on ctp list page
	 * 
	 * @param  [array] $defaults [array going through wp filter]
	 * @return [array]           [array going through wp filter]
	 */
	public function column_head($defaults) {
		$defaults['productlist_sort'] = 'Sorting Order';
		return $defaults;
	}


	/**
	 * filter for populating columns on ctp list page
	 * 
	 * @param  [array] $defaults [array going through wp filter]
	 * @return [array]           [array going through wp filter]
	 */
	public function custom_column($column_name) {
		global $post;
		// echo $_SERVER['QUERY_STRING'];

		// echo parse_url()
		
		// echo print_r($q_out, true);

		if ($column_name == 'productlist_sort') {
			$q_out = null;
			parse_str($_SERVER['QUERY_STRING'], $q_out);

			$meta = 'productlist_sort';
			if (isset($q_out['productlisttype'])) $meta = $meta.'_'.$q_out['productlisttype'];

			$meta = get_post_meta($post->ID, $meta);
			
			if (isset($meta[0])) echo $meta[0];
		}
	}


	/**
	 * filter for sorting by columns on ctp list page
	 * 
	 * @param  [array] $defaults [array going through wp filter]
	 * @return [array]           [array going through wp filter]
	 */
	public function sort_column($columns) {
		$columns['productlist_sort'] = 'productlist_sort';
		return $columns;
	}



	/*
		creates wordpress metabox
		adds javascript
	*/
	public function create_meta() {

		/* product info meta */
		add_meta_box(
			'productlist_meta', // name
			'Produkt', // title 
			array($this,'create_meta_box'), // callback
			'productlist' // page
		);

		/* to show or not on front-end */
		add_meta_box(
			'productlist_exclude',
			'Aldri vis',
			array($this, 'exclude_meta_box'),
			'productlist',
			'side'
		);
		
		/* adding admin css and js */
		wp_enqueue_style('em-productlist-admin-style', EM_PRODUCT_LIST_PLUGIN_URL . 'assets/css/admin/em-productlist.css', array(), '1.0.1');
		wp_enqueue_script('em-productlist-admin', EM_PRODUCT_LIST_PLUGIN_URL . 'assets/js/admin/em-productlist.js', array(), '1.0.1', true);
	}


	/*
		creates content in metabox
	*/
	public function create_meta_box($post) {
		wp_nonce_field('em'.basename(__FILE__), 'productlist_nonce');

		$meta = get_post_meta($post->ID, 'productlist_data');
		$sort = get_post_meta($post->ID, 'productlist_sort');

		$tax = wp_get_post_terms($post->ID, 'productlisttype');

		$taxes = [];
		if (is_array($tax))
			foreach($tax as $t)
				array_push($taxes, $t->slug);

		$json = [
			'meta' => isset($meta[0]) ? $this->sanitize($meta[0]) : '',
			'productlist_sort' => isset($sort[0]) ? floatval($sort[0]) : '',
			'tax'  => $taxes
		];

		$ameta = get_post_meta($post->ID);
		foreach($ameta as $key => $value)
			if (strpos($key, 'productlist_sort_') !== false && isset($value[0])) $json[$key] = esc_html($value[0]);


		wp_localize_script('em-productlist-admin', 'productlist_meta', json_decode(json_encode($json), true));
		echo '<div class="productlist-meta-container"></div>';
	}
 

 	/**
 	 * [exclude_meta_box description]
 	 */
	public function exclude_meta_box() {
		$option = get_option('productlist_exclude');
		global $post;

		if (!is_array($option)) $option = [];
		// echo 'hi'.print_r($option, true);


		echo '<input name="productlist_exclude" id="productlist_exc" type="checkbox"'.(array_search($post->ID, $option) !== false ? ' checked' : '').'><label for="productlist_exc">Produktet vil ikke vises på front-end når boksen er markert.</label>';
	}



	/**
	 * wp action when saving
	 */
	public function save($post_id) {
		// post type is productlist
		if (!get_post_type($post_id) == 'productlist') return;

		// is on admin screen
		if (!is_admin()) return;

		// user is logged in and has permission
		if (!current_user_can('edit_posts')) return;

		// nonce is sent
		if (!isset($_POST['productlist_nonce'])) return;

		// nonce is checked
		if (!wp_verify_nonce($_POST['productlist_nonce'], 'em'.basename(__FILE__))) return;

		// saves to wp option instead of post meta
		// when adding
		if (isset($_POST['productlist_exclude'])) {
			$option = get_option('productlist_exclude');

			// to avoid php error
			if (!is_array($option)) $option = [];

			// if not already added
			if (array_search($post_id, $option) === false) {

				// if to add to collection
				if (is_array($option)) {
					array_push($option, intval($post_id));

					update_option('productlist_exclude', $option);
				}
				
				// if to create collection (of one)
				else update_option('productlist_exclude', [$post_id]);
			}
		}
		// when removing
		else {
			$option = get_option('productlist_exclude');

			if (array_search($post_id, $option) !== false) {
				unset($option[array_search($post_id, $option)]);
				update_option('productlist_exclude', $option);
			}
		}

		// data is sent, then sanitized and saved
		if (isset($_POST['productlist_data'])) update_post_meta($post_id, 'productlist_data', $this->sanitize($_POST['productlist_data']));
		if (isset($_POST['productlist_sort'])) update_post_meta($post_id, 'productlist_sort', floatval($_POST['productlist_sort']));

		// saving productlist_sort_***
		foreach($_POST as $key => $po) {
			if (strpos($key, 'productlist_sort_') !== false)
				update_post_meta($post_id, sanitize_text_field(str_replace(' ', '', $key)), floatval($po));
		}

	}


	/*
		recursive sanitizer
	*/
	private function sanitize($data) {
		if (!is_array($data)) return wp_kses_post($data);

		$d = [];
		foreach($data as $key => $value)
			$d[$key] = $this->sanitize($value);

		return $d;
	}
}