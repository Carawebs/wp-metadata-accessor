<?php
namespace Carawebs\DataAccessor\DynamicFieldData;

use Carawebs\Fetch;
use Carawebs\Extras;
use Carawebs\Loops;

/**
* Return markup for content fields
*
* @package Carawebs
* @subpackage Views
* @author David Egan <david@carawebs.com>
*
*/
trait SectionStyleHelpers {

    /**
    * Return an array of style data for a dynamic field
    *
    * @param  string|int $count  The count for the dynamic layout field
    * @return Array              An array of style variables
    */
    protected function inline_style_data ( $count, $prefix = NULL ) {

        return [
            'bg_image_ID' => get_post_meta( $this->post_ID, $prefix . '_image', true ),
            'bg_colour'   => get_post_meta( $this->post_ID, $prefix . '_background_colour', true ),
            'text_colour' => get_post_meta( $this->post_ID, $prefix . '_text_colour', true ), // 'light_text', 'dark_text', 'default_text'
            'opacity'     => get_post_meta( $this->post_ID, $prefix . '_image_opacity', true ),
            'fixed'       => '1' === get_post_meta( $this->post_ID, $prefix . '_fixed_image', true ) ? true : false, // 'fixed' position on background image
            'container'   => get_post_meta( $this->post_ID, $prefix . '_container', true )
        ];

    }

    protected function section_inline_style ( array $args ) {
        var_dump($args);

        extract( $args );

        // Set defaults
        // -------------------------------------------------------------------------
        $bg_image_ID = ! empty( $bg_image_ID) ? $bg_image_ID : NULL;
        $fixed = true === $fixed ? ' fixed' : NULL;
        $bg_colour = ! empty( $bg_colour ) ? 'style="background-color: ' . $bg_colour . ';' : NULL;
        $opacity = ! empty( $opacity ) ? ' opacity: ' . $opacity . ';"' : '"';
        $text_colour = ! empty( $text_colour ) ? ' '. $text_colour : NULL;
        $class = ! empty( $class ) ? ' ' . implode( " ", $class ) : NULL;
        $img_src = wp_get_attachment_image_src( $bg_image_ID, 'full' )[0];

        ob_start();
        ?>
        <div class="section<?= $class; ?>bg-image<?= $text_colour; ?>" style="background:url('<?= $img_src; ?>') center center<?= $fixed; ?>; background-size: cover;">
            <div class="opaque-layer" <?= $bg_colour;?><?= $opacity; ?>></div>
            <?php

            return ob_get_clean();

        }

        /**
        * An inline style rule only - no markup or CSS classes.
        *
        * Trying this for the sake of simplicity.
        * @param  array  $args    Style arguments
        * @return array  $style   Array containing $class (array) and $inline_string (string)
        */
        protected function inline_style ( array $args ) {

            extract( $args );

            $inline_style = $opaque_style = NULL;

            // Set defaults
            // -------------------------------------------------------------------------
            $bg_image_ID  = ! empty( $bg_image_ID )
            ? $bg_image_ID
            : NULL;
            $bg_img_src   = ! empty( $bg_img_src )
            ? $bg_img_src
            : "'" . wp_get_attachment_image_src( $bg_image_ID, 'full' )[0] . "'";
            $fixed        = true === $fixed         ? ' fixed' : NULL;
            $bg_colour    = ! empty( $bg_colour )   ? 'background-color: ' . $bg_colour . ';' : NULL;
            $opacity      = ! empty( $opacity )     ? 'opacity: ' . $opacity . ';' : NULL;
            $text_colour  = ! empty( $text_colour ) ? $text_colour : NULL;
            $class[]      = ! empty( $text_colour ) ? $text_colour : NULL;

            if( ! empty( $bg_img_src ) ) {

                $inline_style = ' style="background:url('. $bg_img_src . ') center center' . $fixed . '; background-size: cover;"';
                $class[] = 'bg-image';

            }

            if( ! empty( $opacity ) ) {

                $opaque_style = "style='{$bg_colour}{$opacity}'";

            }

            if( ! empty( $bg_colour ) && empty( $opacity ) ) {

                $opaque_style = "style='{$bg_colour}'";

            }

            $section_class = ! empty( $class ) ? ' ' . implode( " ", $class ) : NULL;

            $style = compact( 'section_class', 'inline_style', 'opaque_style' );

            return $style;

        }

        public function inline_width( $items_per_row, $unique_class ) {

            if( empty( $items_per_row ) ) { return; }

            $offset = apply_filters('carawebs/themehelper_post_showcase_offset', 0.01  );

            $width = ( 100 / $items_per_row ) - $offset;

            ob_start();

            ?>
            <style>
            @media (min-width: 1200px) {
                .<?= $unique_class; ?> .inline-container .item,
                .<?= $unique_class; ?> .inline-container .gridbreak {
                    width: <?= $width; ?>%!important;
                    padding: 0;
                }
            }
            </style>

            <?php

            return ob_get_clean();

        }

        public function section_wrapper( $content ) {

            ob_start();

            ?>
            <div class="section<?= $section_class; ?>"<?= $inline_style; ?>>
                <div class="opaque-layer"<?= $opaque_style; ?>></div>

            </div>
            <?php

            echo ob_get_clean();

        }

    }
