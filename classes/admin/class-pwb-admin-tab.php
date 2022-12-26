<?php

namespace Perfect_Woocommerce_Brands\Admin;

use WC_Admin_Settings, WC_Settings_Page;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

class Pwb_Admin_Tab {

	public function __construct() {
		$this->id    = 'pwb_admin_tab';
		$this->label = __( 'Brands', 'perfect-woocommerce-brands' );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_tab' ), 200 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
		add_action( 'admin_footer', array( __CLASS__, 'add_premium_css' ) );
	}

	public static function add_premium_css() {
		?>
		<style>
			.pwb-premium-field {
				opacity: 0.5; 
				pointer-events: none;
			}
			.pwb-premium-field .description {
				display: block!important;
			}
		</style>
		<script>
			const fields = document.querySelectorAll('.pwb-premium-field')
			Array.from(fields).forEach((field)=> {
				field.closest('tr')?.classList.add('pwb-premium-field');
			})
		</script>
		<?php
	}

	public function add_tab( $settings_tabs ) {
		$settings_tabs[ $this->id ] = $this->label;
		return $settings_tabs;
	}

	public function get_sections() {
		$sections = array(
			''               => __( 'General', 'perfect-woocommerce-brands' ),
			'archives'       => __( 'Shop & Categories', 'perfect-woocommerce-brands' ),
			'archives-brand' => __( 'Brands', 'perfect-woocommerce-brands' ),
			'producs'        => __( 'Products', 'perfect-woocommerce-brands' ),
			'tools'          => __( 'Tools', 'perfect-woocommerce-brands' ),
		);

		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) || 1 === count( $sections ) ) {
			return;
		}

		?>
			<ul class="subsubsub">
				<?php foreach ( $sections as $id => $label ) : ?>
					<li>
						<a 
							href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . $id ) ); ?>" 
							class="<?php echo ( $current_section === $id ? 'current' : '' ); ?>"><?php echo esc_html( $label ); ?>
						</a>
					</li> | 
				<?php endforeach; ?>
				<li><a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=pwb_suggestions' ) ); ?>"><?php esc_attr_e( 'Suggestions', 'perfect-woocommerce-brands' ); ?></a></li> | 
				<li><a target="_blank" href="<?php echo esc_url( PWB_DOCUMENTATION_URL ); ?>"><?php esc_html_e( 'Documentation', 'perfect-woocommerce-brands' ); ?></a></li> | 
				<li><a target="_blank" href="<?php echo esc_url( PWB_PREMIUM_SELL_URL ); ?>"><?php esc_html_e( 'Premium', 'perfect-woocommerce-brands' ); ?></a></li>
			</ul>
			<br class="clear" />
		<?php
	}

	public function get_settings( $current_section = '' ) {
		$available_image_sizes_adapted = array();
		$available_image_sizes         = get_intermediate_image_sizes();
		foreach ( $available_image_sizes as $image_size ) {
			$available_image_sizes_adapted[ $image_size ] = $image_size;
		}
		$available_image_sizes_adapted['full'] = 'full';

		$pages_select_adapted = array( '-' => '-' );
		$pages_select         = get_pages();
		foreach ( $pages_select as $page ) {
			$pages_select_adapted[ $page->ID ] = $page->post_title;
		}

		switch ( $current_section ) {
			case 'archives':
				$settings = apply_filters(
					'wc_pwb_admin_tab_archives_settings',
					array(
						'section_title' => array(
							'name' => __( 'Shop & Categories', 'perfect-woocommerce-brands' ),
							'type' => 'title',
							'desc' => '',
							'id'   => 'wc_pwb_admin_tab_section_title',
						),
						array(
							'name'    => __( 'Show brands in loop', 'perfect-woocommerce-brands' ),
							'type'    => 'select',
							'class'   => 'pwb-admin-tab-field',
							'desc'    => __( 'Show brand logo (or name) in product loop', 'perfect-woocommerce-brands' ),
							'id'      => 'wc_pwb_admin_tab_brands_in_loop',
							'options' => array(
								'no'          => __( 'No', 'perfect-woocommerce-brands' ),
								'brand_link'  => __( 'Show brand link', 'perfect-woocommerce-brands' ),
								'brand_image' => __( 'Show brand image (if is set)', 'perfect-woocommerce-brands' ),
							),
						),
						array(
							'name'  => __( 'Brands in loop separator', 'perfect-woocommerce-brands' ),
							'type'  => 'text',
							'class' => 'pwb-admin-tab-field pwb-premium-field',
							'desc'  => __( 'Show separator between brands', 'perfect-woocommerce-brands' ),
							'id'    => 'wc_pwb_admin_tab_brands_in_loop_separator',
						),
						array(
							'name'    => __( 'Show brands in loop hook', 'perfect-woocommerce-brands' ),
							'type'    => 'select',
							'class'   => 'pwb-admin-tab-field pwb-premium-field',
							'desc'    => __( 'Show brand logo (or name) in product loop hook', 'perfect-woocommerce-brands' ),
							'id'      => 'wc_pwb_admin_tab_brands_in_loop_hook',
							'options' => array(
								'shop_loop_item'        => __( 'Shop loop item', 'perfect-woocommerce-brands' ),
								'after_shop_loop_item'  => __( 'After shop loop item', 'perfect-woocommerce-brands' ),
								'after_shop_loop_item_title' => __( 'After shop loop item title', 'perfect-woocommerce-brands' ),
								'before_shop_loop_item' => __( 'Before shop loop item', 'perfect-woocommerce-brands' ),
								'before_shop_loop_item_title' => __( 'Before shop loop item title', 'perfect-woocommerce-brands' ),
							),
							'default' => 'after_shop_loop_item_title',
						),
						'section_end'   => array(
							'type' => 'sectionend',
							'id'   => 'wc_pwb_admin_tab_section_end',
						),
					)
				);
				break;
			case 'archives-brand':
				$settings = apply_filters(
					'wc_pwb_admin_tab_brand_pages_settings',
					array(
						'section_title' => array(
							'name' => __( 'Archives', 'perfect-woocommerce-brands' ),
							'type' => 'title',
							'desc' => '',
							'id'   => 'wc_pwb_admin_tab_section_title',
						),
						array(
							'name'    => __( 'Show brand title', 'perfect-woocommerce-brands' ),
							'type'    => 'select',
							'class'   => 'pwb-admin-tab-field pwb-premium-field',
							'default' => 'yes',
							'desc'    => __( 'Show brand title (if is set) on brand archive page', 'perfect-woocommerce-brands' ),
							'id'      => 'wc_pwb_admin_tab_brand_title',
							'options' => array(
								'yes'            => __( 'Yes, before product loop', 'perfect-woocommerce-brands' ),
								'yes_after_loop' => __( 'Yes, after product loop', 'perfect-woocommerce-brands' ),
								'no'             => __( 'No, hide description', 'perfect-woocommerce-brands' ),
							),
						),
						array(
							'name'    => __( 'Show brand description', 'perfect-woocommerce-brands' ),
							'type'    => 'select',
							'class'   => 'pwb-admin-tab-field',
							'default' => 'yes',
							'desc'    => __( 'Show brand description (if is set) on brand archive page', 'perfect-woocommerce-brands' ),
							'id'      => 'wc_pwb_admin_tab_brand_desc',
							'options' => array(
								'yes'            => __( 'Yes, before product loop', 'perfect-woocommerce-brands' ),
								'yes_after_loop' => __( 'Yes, after product loop', 'perfect-woocommerce-brands' ),
								'no'             => __( 'No, hide description', 'perfect-woocommerce-brands' ),
							),
						),
						array(
							'name'    => __( 'Show brand banner', 'perfect-woocommerce-brands' ),
							'type'    => 'select',
							'class'   => 'pwb-admin-tab-field',
							'default' => 'yes',
							'desc'    => __( 'Show brand banner (if is set) on brand archive page', 'perfect-woocommerce-brands' ),
							'id'      => 'wc_pwb_admin_tab_brand_banner',
							'options' => array(
								'yes'            => __( 'Yes, before product loop', 'perfect-woocommerce-brands' ),
								'yes_after_loop' => __( 'Yes, after product loop', 'perfect-woocommerce-brands' ),
								'no'             => __( 'No, hide banner', 'perfect-woocommerce-brands' ),
							),
						),
						array(
							'name'    => __( 'Columns', 'perfect-woocommerce-brands' ),
							'type'    => 'number',
							'class'   => 'pwb-admin-tab-field pwb-premium-field',
							'default' => 'yes',
							'desc'    => __( 'Number of columns in the brand page', 'perfect-woocommerce-brands' ),
							'id'      => 'wc_pwb_admin_tab_brand_columns',
						),
						array(
							'name'    => __( 'Show brands in loop', 'perfect-woocommerce-brands' ),
							'type'    => 'select',
							'class'   => 'pwb-admin-tab-field pwb-premium-field',
							'desc'    => __( 'Show brand logo (or name) in product loop', 'perfect-woocommerce-brands' ),
							'id'      => 'wc_pwb_admin_tab_archives_brand_in_loop',
							'options' => array(
								'no'          => __( 'No', 'perfect-woocommerce-brands' ),
								'brand_link'  => __( 'Show brand link', 'perfect-woocommerce-brands' ),
								'brand_image' => __( 'Show brand image (if is set)', 'perfect-woocommerce-brands' ),
							),
							'default' => get_option( 'wc_pwb_admin_tab_brands_in_loop', 'no' ),
						),
						array(
							'name'    => __( 'Brands in loop separator', 'perfect-woocommerce-brands' ),
							'type'    => 'text',
							'class'   => 'pwb-admin-tab-field pwb-premium-field',
							'desc'    => __( 'Show separator between brands', 'perfect-woocommerce-brands' ),
							'id'      => 'wc_pwb_admin_tab_archives_brand_in_loop_separator',
							'default' => get_option( 'wc_pwb_admin_tab_brands_in_loop_separator', '' ),
						),
						array(
							'name'    => __( 'Show brands in loop hook', 'perfect-woocommerce-brands' ),
							'type'    => 'select',
							'class'   => 'pwb-admin-tab-field pwb-premium-field',
							'desc'    => __( 'Show brand logo (or name) in product loop hook', 'perfect-woocommerce-brands' ),
							'id'      => 'wc_pwb_admin_tab_archives_brand_in_loop_hook',
							'options' => array(
								'shop_loop_item'        => __( 'Shop loop item', 'perfect-woocommerce-brands' ),
								'after_shop_loop_item'  => __( 'After shop loop item', 'perfect-woocommerce-brands' ),
								'after_shop_loop_item_title' => __( 'After shop loop item title', 'perfect-woocommerce-brands' ),
								'before_shop_loop_item' => __( 'Before shop loop item', 'perfect-woocommerce-brands' ),
								'before_shop_loop_item_title' => __( 'Before shop loop item title', 'perfect-woocommerce-brands' ),
							),
							'default' => 'after_shop_loop_item_title',
						),
						'section_end'   => array(
							'type' => 'sectionend',
							'id'   => 'wc_pwb_admin_tab_section_end',
						),
					)
				);
				break;
			case 'producs':
				$settings = apply_filters(
					'wc_pwb_admin_tab_settings',
					array(
						'section_title' => array(
							'name' => __( 'Products', 'perfect-woocommerce-brands' ),
							'type' => 'title',
							'desc' => '',
							'id'   => 'wc_pwb_admin_tab_section_title',
						),
						array(
							'name'    => __( 'Products tab', 'perfect-woocommerce-brands' ),
							'type'    => 'checkbox',
							'default' => 'yes',
							'desc'    => __( 'Show brand tab in single product page', 'perfect-woocommerce-brands' ),
							'id'      => 'wc_pwb_admin_tab_brand_single_product_tab',
						),
						array(
							'name'    => __( 'Show brands in single product', 'perfect-woocommerce-brands' ),
							'type'    => 'select',
							'class'   => 'pwb-admin-tab-field',
							'desc'    => __( 'Show brand logo (or name) in single product', 'perfect-woocommerce-brands' ),
							'default' => 'brand_image',
							'id'      => 'wc_pwb_admin_tab_brands_in_single',
							'options' => array(
								'no'          => __( 'No', 'perfect-woocommerce-brands' ),
								'brand_link'  => __( 'Show brand link', 'perfect-woocommerce-brands' ),
								'brand_image' => __( 'Show brand image (if is set)', 'perfect-woocommerce-brands' ),
							),
						),
						array(
							'name'    => __( 'Brand position', 'perfect-woocommerce-brands' ),
							'type'    => 'select',
							'class'   => 'pwb-admin-tab-field',
							'desc'    => __( 'For single product', 'perfect-woocommerce-brands' ),
							'id'      => 'wc_pwb_admin_tab_brand_single_position',
							'options' => array(
								'before_title'      => __( 'Before title', 'perfect-woocommerce-brands' ),
								'after_title'       => __( 'After title', 'perfect-woocommerce-brands' ),
								'after_price'       => __( 'After price', 'perfect-woocommerce-brands' ),
								'after_excerpt'     => __( 'After excerpt', 'perfect-woocommerce-brands' ),
								'after_add_to_cart' => __( 'After add to cart', 'perfect-woocommerce-brands' ),
								'meta'              => __( 'In meta', 'perfect-woocommerce-brands' ),
								'after_meta'        => __( 'After meta', 'perfect-woocommerce-brands' ),
								'after_sharing'     => __( 'After sharing', 'perfect-woocommerce-brands' ),
							),
						),
						array(
							'name'  => __( 'Brands in single product separator', 'perfect-woocommerce-brands' ),
							'type'  => 'text',
							'class' => 'pwb-admin-tab-field pwb-premium-field',
							'desc'  => __( 'Show separator between brands', 'perfect-woocommerce-brands' ),
							'id'    => 'wc_pwb_admin_tab_brands_in_single_separator',
						),
						array(
							'name'    => __( 'Brands label', 'perfect-woocommerce-brands' ),
							'type'    => 'text',
							'class'   => 'pwb-admin-tab-field pwb-premium-field',
							'default' => esc_html__( 'Brands', 'perfect-woocommerce-brands' ),
							'desc'    => __( 'Change or disable the brands label in the single products page.', 'perfect-woocommerce-brands' ),
							'id'      => 'wc_pwb_admin_tab_brand_single_label',
						),
						'section_end'   => array(
							'type' => 'sectionend',
							'id'   => 'wc_pwb_admin_tab_section_end',
						),
					)
				);
				break;
			case 'tools':
					$settings = apply_filters(
						'wc_pwb_admin_tab_tools_settings',
						array(
							'section_title' => array(
								'name' => __( 'Tools', 'perfect-woocommerce-brands' ),
								'type' => 'title',
								'desc' => '',
								'id'   => 'wc_pwb_admin_tab_section_tools_title',
							),
							array(
								'name'    => __( 'Import brands', 'perfect-woocommerce-brands' ),
								'type'    => 'select',
								'class'   => 'pwb-admin-tab-field',
								'desc'    => sprintf(
									__( 'Import brands from other brand plugin. <a href="%s" target="_blank">Click here for more details</a>', 'perfect-woocommerce-brands' ),
									str_replace( '/?', '/brands/?', PWB_DOCUMENTATION_URL )
								),
								'id'      => 'wc_pwb_admin_tab_tools_migrate',
								'options' => array(
									'-'         => __( '-', 'perfect-woocommerce-brands' ),
									'yith'      => __( 'YITH WooCommerce Brands Add-On', 'perfect-woocommerce-brands' ),
									'ultimate'  => __( 'Ultimate WooCommerce Brands', 'perfect-woocommerce-brands' ),
									'woobrands' => __( 'Offical WooCommerce Brands', 'perfect-woocommerce-brands' ),
								),
							),
							array(
								'name'    => __( 'Dummy data', 'perfect-woocommerce-brands' ),
								'type'    => 'select',
								'class'   => 'pwb-admin-tab-field',
								'desc'    => __( 'Import generic brands and assign it to products randomly', 'perfect-woocommerce-brands' ),
								'id'      => 'wc_pwb_admin_tab_tools_dummy_data',
								'options' => array(
									'-'            => __( '-', 'perfect-woocommerce-brands' ),
									'start_import' => __( 'Start import', 'perfect-woocommerce-brands' ),
								),
							),
							array(
								'name' => __( 'System status', 'perfect-woocommerce-brands' ),
								'type' => 'textarea',
								'desc' => __( 'Show system status', 'perfect-woocommerce-brands' ),
								'id'   => 'wc_pwb_admin_tab_tools_system_status',
							),
							'section_end'   => array(
								'type' => 'sectionend',
								'id'   => 'wc_pwb_admin_tab_section_tools_end',
							),
						)
					);
				break;
			default:
				$brands_url = get_option( 'wc_pwb_admin_tab_slug', __( 'brands', 'perfect-woocommerce-brands' ) ) . '/' . __( 'brand-name', 'perfect-woocommerce-brands' ) . '/';

				$settings = apply_filters(
					'wc_pwb_admin_tab_product_settings',
					array(
						'section_title' => array(
							'name' => __( 'General', 'perfect-woocommerce-brands' ),
							'type' => 'title',
							'desc' => '',
							'id'   => 'wc_pwb_admin_tab_section_title',
						),
						array(
							'name'        => __( 'Slug', 'perfect-woocommerce-brands' ),
							'type'        => 'text',
							'class'       => 'pwb-admin-tab-field',
							'desc'        => __( 'Brands taxonomy slug', 'perfect-woocommerce-brands' ),
							'desc_tip'    => sprintf(
								__( 'Your brands URLs will look like "%s"', 'perfect-woocommerce-brands' ),
								'https://site.com/' . $brands_url
							),
							'id'          => 'wc_pwb_admin_tab_slug',
							'placeholder' => get_taxonomy( 'pwb-brand' )->rewrite['slug'],
						),
						array(
							'name'     => __( 'Brand logo size', 'perfect-woocommerce-brands' ),
							'type'     => 'select',
							'class'    => 'pwb-admin-tab-field',
							'desc'     => __( 'Select the size for the brand logo image around the site', 'perfect-woocommerce-brands' ),
							'desc_tip' => __( 'The default image sizes can be configured under "Settings > Media". You can also define your own image sizes', 'perfect-woocommerce-brands' ),
							'id'       => 'wc_pwb_admin_tab_brand_logo_size',
							'options'  => $available_image_sizes_adapted,
						),
						array(
							'name'     => __( 'Brands page', 'perfect-woocommerce-brands' ),
							'type'     => 'select',
							'class'    => 'pwb-admin-tab-field pwb-admin-selectwoo',
							'desc'     => __( 'For linking breadcrumbs', 'perfect-woocommerce-brands' ),
							'desc_tip' => __( 'Select your "Brands" page (if you have one), it will be linked in the breadcrumbs.', 'perfect-woocommerce-brands' ),
							'id'       => 'wc_pwb_admin_tab_brands_page_id',
							'options'  => $pages_select_adapted,
						),
						array(
							'name'     => __( 'Brands search', 'perfect-woocommerce-brands' ),
							'type'     => 'select',
							'class'    => 'pwb-admin-tab-field pwb-premium-field',
							'desc'     => __( 'Better search experience', 'perfect-woocommerce-brands' ),
							'desc_tip' => __( 'Redirect if the search matchs with a brands name.', 'perfect-woocommerce-brands' ),
							'id'       => 'wc_pwb_admin_tab_brands_search',
							'options'  => array(
								'no'  => __( 'No', 'perfect-woocommerce-brands' ),
								'yes' => __( 'Yes', 'perfect-woocommerce-brands' ),
							),
						),
						array(
							'name'    => __( 'Brands breadcrumb', 'perfect-woocommerce-brands' ),
							'type'    => 'select',
							'class'   => 'pwb-admin-tab-field pwb-premium-field',
							'desc'    => __( 'Include brand in product breadcrumb', 'perfect-woocommerce-brands' ),
							'id'      => 'wc_pwb_admin_tab_brands_breadcrumb',
							'options' => array(
								'no'      => __( 'No', 'perfect-woocommerce-brands' ),
								'yes'     => __( 'Yes', 'perfect-woocommerce-brands' ),
								'replace' => __( 'Replace category', 'perfect-woocommerce-brands' ),
							),
						),
						'section_end'   => array(
							'type' => 'sectionend',
							'id'   => 'wc_pwb_admin_tab_section_end',
						),
					)
				);
				break;
		}

		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
	}

	public function output() {
		global $current_section;

		$settings = $this->get_settings( $current_section );

		WC_Admin_Settings::output_fields( $settings );

		if ( 'archives-brand' == $current_section ) {
			?>
				<a href="<?php echo admin_url( 'edit-tags.php?taxonomy=pwb-brand&post_type=product' ); ?>" class="page-title-action"><?php esc_html_e( 'Edit brands', 'perfect-woocommerce-brands' ); ?></a>
			<?php
		}
	}

	public function save() {

		update_option( 'old_wc_pwb_admin_tab_slug', get_taxonomy( 'pwb-brand' )->rewrite['slug'] );

		if ( isset( $_POST['wc_pwb_admin_tab_slug'] ) ) {
			$_POST['wc_pwb_admin_tab_slug'] = sanitize_title( $_POST['wc_pwb_admin_tab_slug'] );
		}

		global $current_section;

		$settings = $this->get_settings( $current_section );

		WC_Admin_Settings::save_fields( $settings );
	}
}

return new Pwb_Admin_Tab();
