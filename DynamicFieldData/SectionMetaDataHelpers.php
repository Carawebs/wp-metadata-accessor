<?php
namespace Carawebs\DataAccessor\DynamicFieldData;

use Carawebs\Display;

trait SectionMetaDataHelpers {

    public function partial_selector( $partial ) {

        if ( file_exists( get_template_directory() . '/partials/sections/' . $partial . '.php' ) ) {

            return ( get_template_directory() . '/partials/sections/' . $partial . '.php' );

        } else {

            return ( dirname(__DIR__) . '/partials/sections/' . $partial . '.php' );

        }

    }

    /**
    * General Section metadata.
    *
    * @param  int|string   $count      The section index
    * @param  string       $prefix     The section type
    * @param  string       $classname  Unique classname
    * @return array                    General metadata for the section
    */
    public function sectionMetadata($count, $prefix = NULL, $classname = NULL) {
        $classes = ['flexible-field'];
        $classname = $classname ?? 'flex-section';
        $classes[] = $classname;
        $classes[] = $classname . '-' . $count;
        $classes[] = str_replace('_', '-', $prefix) . '-' . $count;
        $classes[] = str_replace('_', '-', $prefix);
        //$classes[] = 'full-width';
        $unique_id = $this->flex_fieldname . '_' . $count . '_' . $prefix;
        $items_per_row = get_post_meta( $this->post_ID, $unique_id . '_items_per_row', true );
        $fieldname = $this->flex_fieldname . '_' . $count . '_' . $prefix . '_collection';
        $data = get_post_meta( $this->post_ID, $fieldname, true );

        $metadata = [
            'unique_id' => $unique_id,
            'style' => $this->inline_style_data( $count, $unique_id ),
            'title' => get_post_meta( $this->post_ID, $unique_id . '_title', true ),
            'intro' => get_post_meta( $this->post_ID, $unique_id . '_intro', true ),
            'content' => apply_filters( 'the_content', get_post_meta( $this->post_ID, $unique_id . '_content', true ) ),
            'item_style' => $this->inline_width( $items_per_row, $classname . '-' . $count ),
            'items_per_row' => $items_per_row,
            'image_size' => apply_filters( 'carawebs/themehelper_dynamic_fields_image_size', 'thumbnail' ),
            'data' => $data,
            'count' => count( $data ),
            'fieldname' => $fieldname
        ];
        $metadata['style']['class'] = $classes;
        return $metadata;
    }

    /**
    * General Section metadata.
    *
    * @param  int|string $index The section index
    * @param  string $section The section name
    * @param  string $classname Unique classname
    * @return array General metadata for the section
    */
    public function newMetadata($index, $section = NULL, $classname = NULL)
    {
        $classes = $this->cssClasses($index, $section);
        $unique_id = $this->flex_fieldname . '_' . $index . '_' . $section;
        $data = get_post_meta($this->post_ID, NULL, true);
        $sectionFields = [];
        foreach ($data as $key => $value) {
            // Only fields with a custom field postmeta key
            if (FALSE !== strpos($key, $unique_id)) {
                // Exclude fields that are prefixed with "_"
                if ('_' === $key[0]) continue;
                $simpleKey = str_replace($unique_id.'_', '', $key);
                $value = maybe_unserialize($value[0]);
                $type = $this->fieldType('_'.$key);
                $sectionFields[$simpleKey]['value'] = $value;
                $sectionFields[$simpleKey]['type'] = $type;
            }
        }
        $obj = new \stdClass;
        $obj->sectionName = $unique_id;
        $obj->flexFieldType = $section;
        $obj->index = $index;
        $obj->cssClasses = [$section, $section.'-'.$index];
        $obj->data = $sectionFields;
        var_dump($obj);
        return $sectionFields;
    }

    /**
     * Return data about the field
     * @param  [type] $metaFieldKey [description]
     * @return [type]               [description]
     */
    private function fieldType($metaFieldKey)
    {
        global $wpdb;
        $postName = get_post_meta($this->post_ID, $metaFieldKey, true);
        $data = $wpdb->get_col( $wpdb->prepare(
            "
            SELECT      post_content
            FROM        $wpdb->posts
            WHERE       post_name = %s
            ",
            $postName
            ));

            $data = unserialize($data[0]);
            // foreach ($data as $key => $value) {
            //     if ('type' === $key){
            //         $fieldMetaData->type = $value;
            //     }
            // }
            return $data['type'];
        }

        /**
        * [cssClasses description]
        * @param [type] $count [description]
        * @param [type] $prefix [description]
        */
        private function cssClasses($count, $prefix)
        {
            $classes = ['flexible-field'];
            $classname = $classname ?? 'flex-section';
            $classes[] = $classname;
            $classes[] = $classname . '-' . $count;
            $classes[] = str_replace('_', '-', $prefix) . '-' . $count;
            $classes[] = str_replace('_', '-', $prefix);
            return $classes;
        }


        /**
        * Return metadata for a two-column section.
        *
        * @param  int|string  $count   The section index
        * @param  string      $prefix  The section type
        * @return array                Metadata for the section
        */
        public function twoColumnMetadata( $count, $prefix ) {

            $unique_id          = $this->flex_fieldname . '_' . $count . '_' . $prefix;
            $link_ID            = get_post_meta( $this->post_ID, $unique_id . '_link', true );
            $primary_content    = get_post_meta( $this->post_ID, $unique_id . '_content_column', true );
            $secondary_content  = get_post_meta( $this->post_ID, $unique_id . '_second_content_column', true );
            $fg_image_ID        = get_post_meta( $this->post_ID, $unique_id . '_foreground_image', true );
            $show_link          = get_post_meta( $this->post_ID, $unique_id . '_add_related', true );
            $link_ID            = ! empty( $link_ID ) ? array_values($link_ID)[0] : NULL;
            $link_text          = get_post_meta( $this->post_ID, $unique_id . '_link_text', true );
            $link_display       = get_post_meta( $this->post_ID, $unique_id . '_link_display', true );

            return [
                'layout'            => get_post_meta( $this->post_ID, $unique_id . '_layout', true ),
                'include_contacts'  => get_post_meta( $this->post_ID, $unique_id . '_include_contact_list', true ),
                'show_link'         => $show_link,
                'link_text'         => ! empty( $link_text ) ? esc_html( $link_text ) : "Find out more",
                'link_display'      => $link_display ?? NULL,
                'link_class'        => 'button' === $link_display ? ' class="btn btn-default"' : NULL,
                'primary_content'   => ! empty( $primary_content ) ? apply_filters( 'the_content', $primary_content ) : NULL,
                'secondary_content' => ! empty( $secondary_content ) ? apply_filters( 'the_content', $secondary_content ) : NULL,
                'image_html'        => wp_get_attachment_image( $fg_image_ID, 'large', '', ['class' => 'img-responsive'] ),
                'link_url'          => ! empty( $link_ID ) ? esc_url( get_permalink( $link_ID ) ) : NULL
            ];
        }

        /**Return metadata for a call to action section.
        *
        * @param  int|string  $count   The section index
        * @param  string      $prefix  The section type
        * @return array                Metadata for the section
        */
        public function call_to_action_metadata( $count, $prefix ) {

            $unique_id    = $this->flex_fieldname . '_' . $count . '_' . $prefix;
            $types        = get_post_meta( $this->post_ID, $unique_id . '_type', true );
            $custom_link  = get_post_meta( $this->post_ID, $unique_id . '_custom_link', true );
            $link_text    = get_post_meta( $this->post_ID, $unique_id . '_custom_link_text', true );
            $content      = get_post_meta( $this->post_ID, $unique_id . '_text_content', true );

            $CTA = Display\CTA::render_buttons(
                [
                    'types'       => $types,
                    'custom_link' => get_the_permalink( $custom_link ),
                    'link_text'   => $link_text
                ]);

                return [
                    'title'               => get_post_meta( $this->post_ID, $unique_id . '_title', true ),
                    'content'             => apply_filters( 'the_content', $content ),
                    'CTA'                 => $CTA,
                ];

            }

            /**
            * Return metadata for an image showcase section.
            *
            * @param  int|string  $count   The section index
            * @param  string      $prefix  The section type
            * @return array                Metadata for the section
            */
            public function image_showcase_metadata( $count, $prefix, $image_size = NULL ) {

                $unique_id = $this->flex_fieldname . '_' . $count . '_' . $prefix;
                $image_size = !empty($image_size) ? $image_size : apply_filters( 'carawebs/themehelper_image_showcase_size', 'thumbnail' );
                $image = ['image_ID', $image_size];
                // Remember: keys are the ACF repeater subfield fieldnames
                $subfields  = [
                    'title'  => 'text',
                    'link'   => 'esc_url',
                    'image'  => $image
                ];
                $fieldname  = $unique_id . '_images';
                $data       = Data::acf_repeater( $this->post_ID, $fieldname, $subfields );

                return [
                    'data'   => $data,
                    'count'  => count( $data )
                ];

            }

            /**
            * Return metadata for a testimonials section.
            *
            * @param  int|string  $count   The section index
            * @param  string      $prefix  The section type
            * @return array                Metadata for the section
            */
            public function testimonials_metadata( $count, $prefix ) {

                $unique_id  = $this->flex_fieldname . '_' . $count . '_' . $prefix;
                $fieldname      = $unique_id . '_testimonial';
                $image_style    = get_post_meta( $this->post_ID, $unique_id . '_image_option', true );
                $image_classes  = 'round' === $image_style ? 'img-responsive img-circle' : 'img-responsive';
                $display_option = get_post_meta( $this->post_ID, $unique_id . '_display_option', true );
                $subfields = [
                    'text'          => 'text',
                    'image'         => ['image_ID', apply_filters('carawebs/themehelper-testimonial-image', 'full')],
                    'name'          => 'text',
                    'company'       => 'text',
                    'person_title'  => 'text'
                ];

                $testimonials = $this->postMeta->getRepeater($fieldname, $subfields);

                return [
                    'testimonials'    => $testimonials,
                    'image_classes'   => $image_classes,
                    'display_option'  => $display_option
                ];

            }

        }
