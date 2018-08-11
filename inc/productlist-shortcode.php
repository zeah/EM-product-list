<?php 

/**
 * WP Shortcodes
 */
final class Productlist_shortcode {
	/* singleton */
	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) self::$instance = new self();

		return self::$instance;
	}

	private function __construct() {
		$this->wp_hooks();
	}


	/**
	 * hooks for wp
	 */
	private function wp_hooks() {

		// loan list
		if (!shortcode_exists('product')) add_shortcode('product', array($this, 'add_shortcode'));
		else add_shortcode('emproduct', array($this, 'add_shortcode'));

		// loan thumbnail
		if (!shortcode_exists('product-bilde')) add_shortcode('product-bilde', array($this, 'add_shortcode_bilde'));
		else add_shortcode('emproduct-bilde', array($this, 'add_shortcode_bilde'));

		// loan button
		// if (!shortcode_exists('product-bestill')) add_shortcode('product-bestill', array($this, 'add_shortcode_bestill'));
		// else add_shortcode('emproduct-bestill', array($this, 'add_shortcode_bestill'));


		add_filter('search_first', array($this, 'add_serp'));
	}


	/**
	 * returns a list of loans
	 */
	public function add_shortcode($atts, $content = null) {

		add_action('wp_enqueue_scripts', array($this, 'add_css'));

		if (!is_array($atts)) $atts = [];

		$args = [
			'post_type' 		=> 'productlist',
			'posts_per_page' 	=> -1,
			'orderby'			=> [
										'meta_value_num' => 'ASC',
										'title' => 'ASC'
								   ],
			'meta_key'			=> 'productlist_sort'.($atts['product'] ? '_'.sanitize_text_field($atts['product']) : '')
		];


		$type = false;
		if (isset($atts['product'])) $type = $atts['product'];
		if ($type)
			$args['tax_query'] = array(
					array(
						'taxonomy' => 'productlisttype',
						'field' => 'slug',
						'terms' => sanitize_text_field($type)
					)
				);


		$names = false;
		if (isset($atts['name'])) $names = explode(',', preg_replace('/ /', '', $atts['name']));
		if ($names) $args['post_name__in'] = $names;
		
		$exclude = get_option('productlist_exclude');

		if (is_array($exclude) && !empty($exclude)) $args['post__not_in'] = $exclude;

		$posts = get_posts($args);	

		$sorted_posts = [];
		if ($names) {
			foreach(explode(',', preg_replace('/ /', '', $atts['name'])) as $n)
				foreach($posts as $p) 
					if ($n === $p->post_name) array_push($sorted_posts, $p);
		
			$posts = $sorted_posts;
		}
				

		$html = $this->get_html($posts);

		return $html;
	}


	/**
	 * returns only thumbnail from loan
	 */
	public function add_shortcode_bilde($atts, $content = null) {
		if (!isset($atts['name']) || $atts['name'] == '') return;

		$args = [
			'post_type' 		=> 'productlist',
			'posts_per_page'	=> 1,
			'name' 				=> sanitize_text_field($atts['name'])
		];

		$post = get_posts($args);

		if (!is_array($post)) return;

		if (!get_the_post_thumbnail_url($post[0])) return;

		add_action('wp_enqueue_scripts', array($this, 'add_css'));

		$meta = get_post_meta($post[0]->ID, 'productlist_data');
		if (isset($meta[0])) $meta = $meta[0];

		$float = false;
		if ($atts['float']) 
			switch ($atts['float']) {
				case 'left': $float = ' style="float: left; margin-right: 3rem;"'; break;
				case 'right': $float = ' style="float: right; margin-left: 3rem;"'; break;
			}

		// returns with anchor
		if ($meta['bestill']) return '<div class="productlist-logo-ls"'.($float ? $float : '').'><a target="_blank" rel=noopener href="'.esc_url($meta['bestill']).'"><img alt="'.esc_attr($post[0]->post_title).'" src="'.esc_url(get_the_post_thumbnail_url($post[0], 'full')).'"></a></div>';

		// anchor-less image
		return '<div class="productlist-logo-ls"'.($float ? $float : '').'><img alt="'.esc_attr($post[0]->post_title).'" src="'.esc_url(get_the_post_thumbnail_url($post[0], 'full')).'"></div>';
	}


	/**
	 * returns bestill button only from loan
	 */
	public function add_shortcode_bestill($atts, $content = null) {
		if (!isset($atts['name']) || $atts['name'] == '') return;

		$args = [
			'post_type' 		=> 'productlist',
			'posts_per_page'	=> 1,
			'name' 				=> sanitize_text_field($atts['name'])
		];

		$post = get_posts($args);
		if (!is_array($post)) return;

		$meta = get_post_meta($post[0]->ID, 'productlist_data');

		if (!is_array($meta)) return;

		$meta = $meta[0];

		if (!$meta['bestill']) return;

		$float = false;
		if ($atts['float']) 
			switch ($atts['float']) {
				case 'left': $float = ' style="float: left; margin-right: 3rem;"'; break;
				case 'right': $float = ' style="float: right; margin-left: 3rem;"'; break;
			}

		add_action('wp_enqueue_scripts', array($this, 'add_css'));
		return '<div class="productlist-bestill productlist-bestill-mobile"'.($float ? $float : '').'><a target="_blank" rel="noopener" class="productlist-link" href="'.esc_url($meta['bestill']).'"><svg class="productlist-svg" version="1.1" x="0px" y="0px" width="26px" height="20px" viewBox="0 0 26 20" enable-background="new 0 0 24 24" xml:space="preserve"><path fill="none" d="M0,0h24v24H0V0z"/><path class="productlist-thumb" d="M1,21h4V9H1V21z M23,10c0-1.1-0.9-2-2-2h-6.31l0.95-4.57l0.03-0.32c0-0.41-0.17-0.79-0.44-1.06L14.17,1L7.59,7.59C7.22,7.95,7,8.45,7,9v10c0,1.1,0.9,2,2,2h9c0.83,0,1.54-0.5,1.84-1.22l3.02-7.05C22.95,12.5,23,12.26,23,12V10z"/></svg> Ansök här!</a></div>';
	}


	/**
	 * adding sands to head
	 */
	public function add_css() {
        wp_enqueue_style('productlist-style', EM_PRODUCT_LIST_PLUGIN_URL.'assets/css/pub/em-productlist.css', array(), '1.0.1', '(min-width: 801px)');
        wp_enqueue_style('productlist-mobile', EM_PRODUCT_LIST_PLUGIN_URL.'assets/css/pub/em-productlist-mobile.css', array(), '1.0.1', '(max-width: 800px)');
	}


	/**
	 * returns the html of a list of loans
	 * @param  WP_Post $posts a wp post object
	 * @return [html]        html list of loans
	 */
	private function get_html($posts) {
		$html = '<ul class="productlist-ul">';

		foreach ($posts as $p) {
			
			$meta = get_post_meta($p->ID, 'productlist_data');

			// skip if no meta found
			if (isset($meta[0])) $meta = $meta[0];
			else continue;

			// sanitize meta
			$meta = $this->esc_kses($meta);

			// grid container
			$html .= '<li class="productlist-container">';

			$html .= '<div class="productlist-logo"><img class="productlist-image" alt="'.esc_html($p->post_title).'" src="'.get_the_post_thumbnail_url($p, $size = 'post-thumbnail').'"></div>';

			$html .= '<div class="productlist-title">'.esc_html($p->post_title).'</div>';

			$html .= '<div class="productlist-price">'.apply_filters('the_content', $meta['productprice']).'</div>';

			$html .= '<div class="productlist-description">'.apply_filters('the_content', $meta['productdescription']).'</div>';

			// wp_die('<xmp>'.print_r($meta, true).'</xmp>');
			

			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;
	}



	/**
	 * wp filter for adding to internal serp
	 * array_push to $data
	 * $data['html'] to be printed
	 * 
	 * @param [Array] $data [filter]
	 */
	public function add_serp($data) {
		global $post;

		if ($post->post_type != 'productlist') return $data;

		$exclude = get_option('productlist_exclude');

		if (!is_array($exclude)) $exclude = [];

		if (in_array($post->ID, $exclude)) return $data;

		$html['html'] = $this->get_html([$post]);

		array_push($data, $html);
		add_action('wp_enqueue_scripts', array($this, 'add_css'));

		return $data;
	}



	/**
	 * kisses the data
	 * recursive sanitizer
	 * 
	 * @param  Mixed $data Strings or Arrays
	 * @return Mixed       Kissed data
	 */
	private function esc_kses($data) {
		if (!is_array($data)) return wp_kses_post($data);

		$d = [];
		foreach($data as $key => $value)
			$d[$key] = $this->esc_kses($value);

		return $d;
	}
}