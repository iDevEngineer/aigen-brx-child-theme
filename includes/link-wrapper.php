<?php


/**
 * ----------------------------------------
 * Link Wrapper Tag
 * ----------------------------------------
 * Usage: {block link}
 * Description: Make the a tag nestable to wrap elements on a child post/page.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Element_Link_Wrapper extends \Bricks\Element {
  public $category     = 'AIGEN';
  public $name         = 'link-wrapper';
  public $icon         = 'ti-link';
  public $css_selector = 'a';
  public $nestable     = true;
  
  // Set builder controls
  public function set_controls() {
    $this->controls['myLink'] = [
      'tab'         => 'content',
      'label'       => esc_html__( 'Link', 'bricks' ),
      'type'        => 'link',
      'pasteStyles' => false,
      'placeholder' => esc_html__( 'http://yoursite.com', 'bricks' ),
    ];
  }
  
  public function get_label() {
    return esc_html__( 'Link Wrapper', 'bricks' );
  }

  // Render element HTML
  public function render() {
    $selector = $this->name;
    $settings = $this->settings;
    
    // Bricks\Helpers::pre_dump($settings);
    
    $root_classes[] = $selector;
    
    $this->set_attribute('root', 'class', $root_classes);
    
    
    if ( isset( $this->settings['myLink'] ) ) {
      // Set link attributes by passing attribute key and link settings
      $this->set_link_attributes( 'a', $this->settings['myLink'] );

      echo '<a ' . $this->render_attributes( 'a' ) . '' . $this->render_attributes('_root') . '>' . \Bricks\Frontend::render_children( $this ) . '</a>';
    } else {
      esc_html_e( 'No link provided.', 'bricks' );
    }
  }
}