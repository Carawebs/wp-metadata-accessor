<?php
namespace Carawebs\DataAccessor;

/**
* Abstract class contains core methods for return of Data stored by means of ACF
*
* @since      1.0.0
* @package    WP Metadata Accessor
* @author     David Egan <david@carawebs.com>
* @link:      http://dev-notes.eu/
*/
abstract class Data {
    /**
    * Filter/Sanitize data according by type
    *
    * @since 1.0.0
    * @uses esc_html()
    * @uses wp_kses_post()
    * @param  string $content Data to be filtered
    * @param  string $type    Type of data - denotes the filter to use
    * @return string          Filtered data
    */
    public function filter($content, $type) {
        $output = '';
        switch( $type ) {
            case "OEmbed":
            $output = $content;
            break;

            case "esc_html":
            $output = esc_html( $content );
            break;

            case "esc_url":
            $output = esc_url( $content );
            break;

            case "the_content":
            $output = apply_filters( 'the_content', $content );
            break;

            case "text":
            $output = esc_html( $content );
            break;

            case 'date':
            $output = date( 'M j, Y', strtotime( esc_html( $content ) ) );
            break;

            case 'time':
            $output = strtotime(esc_html($content ));
            break;

            case "float":
            $output = (float)$content;
            break;

            case "int":
            $output = (int)$content;
            break;

            case "object":
            return $content;
            break;

            default:
            return wp_kses_post($content);
            break;
        }
        return $output;
    }

    /**
    * Filter and return an image.
    *
    * This static method will return an array of necessary attributes to enable
    * construction of HTML for an image.
    *
    * @since 1.0.0
    * @uses wp_prepare_attachment_for_js()
    * @param  string|integer $image_ID Post ID of the image to be returned
    * @param  array  $meta             An array containing image size
    * @return array  Necessary data to build an image (ID, src, title, height, width, alt)
    */
    public function imageFilter( $image_ID, array $meta ) {
        $image_object = wp_prepare_attachment_for_js( $image_ID );
        $image_size = $meta[1];
        $output = [
            'ID'      => $image_ID,
            'url'     => $image_object['sizes'][$image_size]['url'],
            'title'   => $image_object['title'],
            'height'  => $image_object['sizes'][$image_size]['height'],
            'width'   => $image_object['sizes'][$image_size]['width'],
            'alt'     => $image_object['alt'],
            'caption' => $image_object['caption']
        ];
        return $output;
        //return $image_object;
    }

    /**
    * Return image markup
    *
    * @param  int|string $image_ID  Post ID of image
    * @param  string $image_size    Size of image to return
    * @return string                HTML markup of image
    */
    static public function get_image( $image_ID, $image_size = 'full' ) {
        $image_object = wp_prepare_attachment_for_js( $image_ID );
        $src          = $image_object['sizes'][$image_size]['url'];
        $title        = $image_object['title'];
        $height       = $image_object['sizes'][$image_size]['height'];
        $width        = $image_object['sizes'][$image_size]['width'];
        $alt          = $image_object['alt'];
        $image ="<img src='$src' width='$width' height='$height' title='$title' class='img-responsive'/>";
        return $image;
    }

    static public function the_image( $image_ID, $image_size = 'full' ) {
        echo self::get_image( $image_ID, $image_size );
    }
}
