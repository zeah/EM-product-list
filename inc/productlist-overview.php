<?php 
defined('ABSPATH') or die('Blank Space');


final class Productlist_overview {
	/* singleton */
	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) self::$instance = new self();

		return self::$instance;
	}

	private function __construct() {
		add_action('admin_menu', array($this, 'add_menu'));
	}

	public function add_menu() {
		add_submenu_page('edit.php?post_type=productlist', 'Overview', 'Overview', 'manage_options', 'productlist-overview', array($this, 'add_page'));
	}

	public function add_page() {
		wp_enqueue_style('em-productlist-admin-style', LANLIST_SE_PLUGIN_URL . 'assets/css/admin/em-productlist.css', array(), '1.0.1');

		$args = [
			'post_type' 		=> array('page', 'post'),
			'posts_per_page'	=> -1
		];

		$posts = get_posts($args);

		$site = get_site_url();

		$html = '<table id="myTable2" style="font-size: 16px;"><tr><th width="400px" onclick="sortTable(0)">Url</th><th onclick="sortTable(1)">Name</th><th onclick="sortTable(2)">Shortcode</th></tr>';

		foreach ($posts as $post) {

			if (strpos($post->post_content, '[product') !== false) {
				preg_match_all('/\[product.*?\]/', $post->post_content, $matches);

				$m = '';

				foreach($matches[0] as $match)
					$m .= $match.' ';

				$html .= '<tr><td><a target="_blank" rel=noopener href="'.$site.'/wp-admin/post.php?post='.$post->ID.'&action=edit">'.str_replace(get_site_url(), '', get_permalink($post)).'</a></td><td>'.$post->post_title.'</td><td>'.$m.'</td></tr>';
			}
			else $html .= '<tr><td><a target="_blank" rel=noopener href="'.$site.'/wp-admin/post.php?post='.$post->ID.'&action=edit">'.str_replace(get_site_url(), '', get_permalink($post)).'</a></td><td>'.$post->post_title.'</td><td></td></tr>';

		}

		$html .= '</table>';
		echo $html;

		echo '<script>
				function sortTable(n) {
				  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
				  table = document.getElementById("myTable2");
				  switching = true;
				  // Set the sorting direction to ascending:
				  dir = "asc"; 
				  /* Make a loop that will continue until
				  no switching has been done: */
				  while (switching) {
				    // Start by saying: no switching is done:
				    switching = false;
				    rows = table.rows;
				    /* Loop through all table rows (except the
				    first, which contains table headers): */
				    for (i = 1; i < (rows.length - 1); i++) {
				      // Start by saying there should be no switching:
				      shouldSwitch = false;
				      /* Get the two elements you want to compare,
				      one from current row and one from the next: */
				      x = rows[i].getElementsByTagName("TD")[n];
				      y = rows[i + 1].getElementsByTagName("TD")[n];
				      /* Check if the two rows should switch place,
				      based on the direction, asc or desc: */
				      if (dir == "asc") {
				        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
				          // If so, mark as a switch and break the loop:
				          shouldSwitch = true;
				          break;
				        }
				      } else if (dir == "desc") {
				        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
				          // If so, mark as a switch and break the loop:
				          shouldSwitch = true;
				          break;
				        }
				      }
				    }
				    if (shouldSwitch) {
				      /* If a switch has been marked, make the switch
				      and mark that a switch has been done: */
				      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
				      switching = true;
				      // Each time a switch is done, increase this count by 1:
				      switchcount ++; 
				    } else {
				      /* If no switching has been done AND the direction is "asc",
				      set the direction to "desc" and run the while loop again. */
				      if (switchcount == 0 && dir == "asc") {
				        dir = "desc";
				        switching = true;
				      }
				    }
				  }
				}
				</script>';
	}

}