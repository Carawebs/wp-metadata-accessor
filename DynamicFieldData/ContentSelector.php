<?php
namespace Carawebs\DataAccessor\DynamicFieldData;

/**
* Return data for flexible content fields.
*
*
* @package Carawebs
* @author David Egan <david@carawebs.com>
*
*/
class ContentSelector extends FlexibleSections {

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
                    $row_data .= $this->the_map( $index, $subfield );
                break;

                case 'showcase';
                    $row_data[] = $this->showcase( $index, $subfield );
                break;

                case 'products_showcase';
                    $row_data .= $this->the_post_showcase( $index, $subfield );
                break;

                case 'content_showcase':
                    $row_data .= $this->the_content_showcase( $index, $subfield );
                break;

                case 'people_showcase':
                    $row_data .= $this->the_content_showcase( $index, $subfield );
                break;

                case 'content_tax':
                    $row_data .= $this->the_content_showcase( $index, $subfield );
                break;

                case 'projects_showcase';
                    $row_data .= $this->the_post_showcase( $index, $subfield );
                break;

                case 'pages_showcase';
                    $row_data .= $this->the_post_showcase( $index, $subfield );
                break;

                case 'two_column_section':
                    $row_data[]= $this->twoColumn( $index, $subfield );
                break;

                case 'text_block':
                    $row_data []= $this->textBlock( $index, $subfield );
                break;

                case 'testimonials':
                    $row_data .= $this->the_testimonials( $index, $subfield );
                break;

                case 'image_showcase':
                    $row_data .= $this->the_image_showcase( $index, $subfield );
                break;

                case 'logo_showcase':
                    $row_data .= $this->the_logo_showcase( $index, $subfield );
                break;

                case 'services_section':
                    $row_data .= $this->the_services( $index, $subfield );
                break;

                case 'call_to_action':
                    $row_data .= $this->the_call_to_action( $index, $subfield );
                break;

                case 'carousel':
                    $row_data .= $this->the_carousel( $index, $subfield );
                break;

            }

        }

        // var_dump($row_data);

        return $row_data;

    }

}
