<?php

/**
 * Reference Class - demonstrates how to generate inline style rules
 *
 * Access this from a controller within the theme - it's tightly coupled to field names,
 * so it's not appropriate for the metadata accessor library.
 */
class ClassName extends AnotherClass
{

    /**
    * Generate inline CSS style rules.
    *
    * @param array $args Style arguments (from the post metadata)
    * @return array $style Array containing $class (array) and $inline_string (string)
    */
    private function inlineStyle (array $args)
    {
        $inline_style = $opaque_style = NULL;

        $bg_image_ID = $args['bg_image_ID'] ?? NULL;
        $bg_img_src = !empty($bg_image_ID) ? "'" . wp_get_attachment_image_src( $bg_image_ID, 'full' )[0] . "'" : NULL;
        $fixed = true === $args['fixed'] ? ' fixed' : NULL;
        $bg_colour = !empty($args['bg_colour']) ? 'background-color: ' . $args['bg_colour'] . ';' : NULL;
        $opacity = !empty($args['opacity']) ? 'opacity: ' . $args['opacity'] . ';' : NULL;
        $text_colour = ! empty($args['text_colour']) ? $args['text_colour'] : NULL;
        $class[] = !empty($args['text_colour']) ? $args['text_colour'] : NULL;

        if(!empty($bg_img_src)) {
            $inline_style = ' style="background:url('. $bg_img_src . ') center center' . $fixed . '; background-size: cover;"';
            $class[] = 'bg-image';
        }

        if( !empty($opacity)) {
            $opaque_style = " style='{$bg_colour}{$opacity}'";
        }

        if(!empty($bg_colour) && empty($opacity)) {
            $opaque_style = " style='{$bg_colour}'";
        }

        $section_class = ! empty( $class ) ? ' ' . implode( " ", $class ) : NULL;
        $style = compact( 'section_class', 'inline_style', 'opaque_style' );
        return $style;
    }
}
