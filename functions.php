<?php

require 'psource/psource-plugin-update/psource-plugin-updater.php';
use Psource\PluginUpdateChecker\v5\PucFactory;
$MyUpdateChecker = PucFactory::buildUpdateChecker(
	'https://n3rds.work//wp-update-server/?action=get_metadata&slug=uf-parrot',
	__FILE__, 
	'uf-parrot'
);

include_once get_template_directory() . '/library/upfront_functions.php';
include_once get_template_directory() . '/library/class_upfront_debug.php';
include_once get_template_directory() . '/library/class_upfront_server.php';
include_once get_template_directory() . '/library/class_upfront_theme.php';

class Uf_parrot extends Upfront_ChildTheme {

	protected $_exports_images = true;

	public function initialize() {
		add_filter('upfront_augment_theme_layout', array($this, 'augment_layout'));
		$this->add_actions_filters();
		$this->populate_pages();
	}

	public function get_prefix(){
		return 'uf-parrot';
	}

	public static function serve(){
		return new self();
	}

	public function populate_pages() {

	}

	protected function add_actions_filters() {
		// Include current theme style
		add_action('wp_head', array($this, 'enqueue_styles'), 200);
	}

	public function enqueue_styles() {
		wp_enqueue_style('current_theme', get_stylesheet_uri());
	}
	public function augment_layout ($layout) {
		if (empty($layout['regions'])) return $layout;
		$layout['regions'] = $this->augment_regions($layout['regions']);
		return $layout;
	}

	public function augment_regions ($regions) {
		if (!empty($this->_slider_imported)) return $regions;

		if (empty($regions) || !is_array($regions)) return $regions;
		foreach ($regions as $idx => $region) {
			if (empty($region['properties']) || !is_array($region['properties'])) continue;
			foreach($region['properties'] as $pidx => $prop) {
				if (empty($prop['name']) || empty($prop['value']) || 'background_slider_images' !== $prop['name']) continue;
				foreach ($prop['value'] as $order_id => $attachment_src) {
					if (is_numeric($attachment_src)) continue; // A hopefully existing image.
					$regions[$idx]['properties'][$pidx]['value'][$order_id] = $this->_import_slider_image($attachment_src);
				}
			}
		}

		$this->_slider_imported = true;
		return $regions;
	}
}

Uf_parrot::serve();

function parrot_scripts() {
	wp_enqueue_script( 'parrot_js', get_stylesheet_directory_uri() . '/js/parrot.js', array( 'jquery' ), '1.0', true );
}
add_action('wp_enqueue_scripts', 'parrot_scripts');

if (file_exists(dirname(__FILE__) . '/compat/compat.php')) require_once(dirname(__FILE__) . '/compat/compat.php');