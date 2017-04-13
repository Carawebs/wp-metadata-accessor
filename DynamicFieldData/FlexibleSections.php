<?php
namespace Carawebs\DataAccessor\DynamicFieldData;

/**
* Class that returns HTML for dynamic sections
*
* Each method fetches data and builds it into a partial.
*
*/
class FlexibleSections {

    use SectionStyleHelpers;
    use SectionMetaDataHelpers;

    /**
    * Instantiate the object
    *
    * @param int       $post_ID The post ID
    * @param string    $flex_fieldname The name of the flexible field
    */
    public function __construct ( $post_ID = NULL, $flex_fieldname = 'flex' ) {
        $this->post_ID = $post_ID ?? get_the_ID();
        $this->flex_fieldname = $flex_fieldname;
    }

    public function twoColumn( $index, $prefix )
    {
        $metadata = array_merge(
            $this->sectionMetadata( $index, $prefix ),
            $this->twoColumnMetadata( $index, $prefix )
        );
        $data = $this->newMetaData($index, $prefix);
        $inlineStyle = $this->inline_style( $metadata['style'] );
        return compact('data', 'inlineStyle');
    }

    public function showcase( $index, $prefix )
    {
        $metadata = array_merge(
            $this->sectionMetadata( $index, $prefix ),
            $this->twoColumnMetadata( $index, $prefix )
        );
        $data = $this->newMetaData($index, $prefix);
        $inlineStyle = $this->inline_style( $metadata['style'] );
        return compact('data', 'inlineStyle');
    }

    /**
    * Output a simple text-block section.
    *
    * @param  int        $index      The flexible field placement counter
    * @param  string     $prefix     The specific subfield
    * @return string                 Section markup
    */
    public function textBlock( $index, $prefix = NULL, $partial = NULL)
    {
        $partial = $partial ?? 'text-block';
        $classname  = $partial;
        $metadata = $this->sectionMetadata( $index, $prefix, $classname );
        $inlineStyle = $this->inline_style( $metadata['style'] );
        return compact('metadata', 'inlineStyle');
    }

    public function the_map( $index, $prefix ) {
        $classname  = 'no-section-padding';
        extract( $this->section_metadata( $index, $prefix, $classname ) );
        extract( $this->inline_style( $style ) );

        ob_start();
        include $this->partial_selector( 'map' );
        return ob_get_clean();

    }

    /**
    * Markup for a call to action section.
    *
    * @param  [type] $index [description]
    * @return [type]        [description]
    */
    public function the_call_to_action( $index, $prefix = NULL ) {

        $classname  = 'full-width';
        $metadata = array_merge(
            $this->section_metadata( $index, $prefix, $classname ), // General metadata
            $this->call_to_action_metadata( $index, $prefix )       // Specific metadata
        );

        extract( $metadata );
        extract( $this->inline_style( $style ) );

        ob_start();
        include $this->partial_selector( 'call-to-action' );
        return ob_get_clean();

    }

    /**
    * Build a block to showcase images/text from a repeater field
    *
    * @param  int        $index      The flexible field placement counter
    * @return string                 Testimonials markup
    */
    public function the_image_showcase( $index, $prefix = NULL ) {
        $partial    = 'image-showcase';
        extract( $this->section_metadata( $index, $prefix ) );
        extract( $this->inline_style( $style ) );
        extract( $this->image_showcase_metadata( $index, $prefix ) );

        ob_start();
        include $this->partial_selector( $partial );
        return ob_get_clean();
    }

    /**
    * Build a block to showcase logos from a repeater field
    *
    * @param  int        $index      The flexible field placement counter
    * @return string                 Testimonials markup
    */
    public function the_logo_showcase( $index, $prefix = NULL ) {
        $partial    = 'logo-showcase';
        extract( $this->section_metadata( $index, $prefix ) );
        extract( $this->inline_style( $style ) );
        $imageSize = apply_filters( 'carawebs/themehelper_logo_showcase_size', 'thumbnail' );
        extract( $this->image_showcase_metadata( $index, $prefix, $imageSize) );

        ob_start();
        include $this->partial_selector( $partial );
        return ob_get_clean();
    }

    /**
    * Build a testimonials block
    *
    * @param  string    $index      The flexible field placement counter
    * @param  string    $prefix     The subfield
    * @return string                Testimonials markup
    */
    public function the_testimonials( $index, $prefix = NULL ) {
        $classname  = 'testimonials';
        $testimonialsData = $this->testimonials_metadata( $index, $prefix );
        $sectionData = $this->section_metadata( $index, $prefix, $classname);
        $styleOverride = $this->inline_style( $sectionData['style'] );
        // extract( $inlineStyle);

        if (empty( $testimonialsData['testimonials'])) return;

        if ( 'columns' === $testimonialsData['display_option'] ) {
            $partial = 'testimonials-columns';
        } elseif( 'slider' === $testimonialsData['display_option'] ) {
            $partial = 'testimonials-slider';
        }

        ob_start();
        include $this->partial_selector( $partial );
        return ob_get_clean();
    }

    /**
    * Build a block to showcase images/text from a repeater field.
    *
    * To filter the image size from a theme file, use 'carawebs/themehelper_posts_showcase_image_size'.
    *
    * @param  int        $index      The flexible field placement counter
    * @param  string     $prefix     The specific subfield
    * @return string                 Section markup
    */
    public function the_post_showcase( $index, $prefix = NULL, $partial = NULL ) {

        $classname = $partial ?? 'posts-showcase';
        $partial   = $classname;
        extract( $this->section_metadata( $index, $prefix, $classname ) );
        extract( $this->inline_style( $style ) );
        $image_size = apply_filters( 'carawebs/themehelper_posts_showcase_image_size', 'thumbnail' );
        $excerpt_length = apply_filters( 'carawebs/themehelper_posts_showcase_excerpt_length', 20 );

        ob_start();
        include $this->partial_selector( $partial );
        return ob_get_clean();

    }

    /**
    * Build a HTML block with intro content and selected CPT teasers
    *
    * @param  int        $index      The flexible field placement counter
    * @param  string     $prefix     The specific subfield
    * @return string                 Section markup
    */
    public function the_content_showcase( $index, $prefix = NULL, $partial = NULL ) {

        $classname  = $partial ?? 'content-showcase';
        $partial    = 'people_showcase' === $prefix ? 'people-showcase' : 'post-showcase';
        extract( $this->section_metadata( $index, $prefix, $classname ) );
        extract( $this->inline_style( $style ) );
        $image_size = apply_filters( 'carawebs/themehelper_content_showcase_image_size', 'thumbnail' );

        ob_start();
        include $this->partial_selector( $partial );
        return ob_get_clean();

    }

    /**
    * Insert a services section partial
    *
    * @param  string|int $index The flexible field count
    * @return string            HTML markup for the services section
    */
    public function the_services( $index, $prefix = NULL ) {

        $partial    = 'services-inline';
        extract( $this->section_metadata( $index, $prefix, $classname ) );
        extract( $this->inline_style( $style ) );
        extract( $this->services_metadata( $index, $prefix ) );

        ob_start();
        include $this->partial_selector( $partial );
        return ob_get_clean();

    }

}
