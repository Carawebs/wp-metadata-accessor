<?php
namespace Carawebs\DataAccessor\DynamicFields;

/**
* Return data for flexible content fields.
*
*
* @package Carawebs
* @author David Egan <david@carawebs.com>
*
*/
class ContentSelector {

    /**
    * Name of the ACF Flexible Content Field
    * @var string
    */
    private $flex_fieldname;

    /**
    * Instantiate the object
    * @param string $flex_fieldname The ACF Flexible Field Name
    */
    public function __construct ( $flex_fieldname = 'flexible_content' ) {

        $this->post_ID = get_the_ID();
        $this->flex_fieldname = $flex_fieldname;

    }

    /**
    * Echo a block of flexible content.
    *
    * Loops through flexible content layout field, fetches the relevant HTML and
    * concatenates this into a string that is echoed. The array of flexible
    * content is set by an ACF Dynamic content field. Each section content is
    * built using the `Carawebs\Views\DynamicFields\Section()` class.
    *
    * @return string The HTML markup for the flexible content field
    */
    public function the_flexible_content () {

        $section = new Section( $this->post_ID, $this->flex_fieldname );

        /**
         * An array of the selected flexible field names, in the correct display order.
         * This means that the key for each array item corresponds to the display index.
         * @var array
         */
        $rows = get_post_meta( $this->post_ID, $this->flex_fieldname, true );

        if ( !$rows ) {
            return;
        }

        $row_data = '';

        foreach( (array)$rows as $index => $subfield) {

            switch ( $subfield ) {

                case 'map':
                    $row_data .= $section->the_map( $index, $subfield );
                break;

                case 'services_showcase';
                    $row_data .= $section->the_post_showcase( $index, $subfield, 'services-showcase' );
                break;

                case 'products_showcase';
                    $row_data .= $section->the_post_showcase( $index, $subfield );
                break;

                case 'content_showcase':
                    $row_data .= $section->the_content_showcase( $index, $subfield );
                break;

                case 'people_showcase':
                    $row_data .= $section->the_content_showcase( $index, $subfield );
                break;

                case 'content_tax':
                    $row_data .= $section->the_content_showcase( $index, $subfield );
                break;

                case 'projects_showcase';
                    $row_data .= $section->the_post_showcase( $index, $subfield );
                break;

                case 'pages_showcase';
                    $row_data .= $section->the_post_showcase( $index, $subfield );
                break;

                case 'two_column_section':
                    $row_data .= $section->the_two_column_section( $index, $subfield );
                break;

                case 'text_block':
                    $row_data .= $section->the_text_block( $index, $subfield );
                break;

                case 'testimonials':
                    $row_data .= $section->the_testimonials( $index, $subfield );
                break;

                case 'image_showcase':
                    $row_data .= $section->the_image_showcase( $index, $subfield );
                break;

                case 'logo_showcase':
                    $row_data .= $section->the_logo_showcase( $index, $subfield );
                break;

                case 'services_section':
                    $row_data .= $section->the_services( $index, $subfield );
                break;

                case 'call_to_action':
                    $row_data .= $section->the_call_to_action( $index, $subfield );
                break;

                case 'carousel':
                    $row_data .= $section->the_carousel( $index, $subfield );
                break;

            }

        }

        return $row_data;

    }

}
