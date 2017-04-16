<?php
namespace Carawebs\DataAccessor;

/**
* Class that returns data for dynamic sections.
*
*/
class FlexibleSections extends Data {

    /**
    * Instantiate the object with fieldname and post ID.
    *
    * @param int       $post_ID The post ID
    * @param string    $flex_fieldname The name of the flexible field
    */
    public function __construct ($postID = NULL, $flex_fieldname = 'flex') {
        // $this->postID = $postID ?? get_the_ID();
        $this->flex_fieldname = $flex_fieldname;
        parent::__construct($postID);
    }

   /**
    * Build an array of flexible content data.
    *
    * The array of flexible content is probably set by an ACF Dynamic content field.
    * @return array Section data as an array of objects.
    */
    public function flexibleContentData ()
    {
        /**
        * An array of the selected flexible field names, in the correct display order.
        * The key for each array item corresponds to the display index, e.g: `[$index => $fieldName]`
        * @var array
        */
        $rows = get_post_meta( $this->postID, $this->flex_fieldname, true );
        if (!$rows) return;
        $rowData = [];

        foreach( (array)$rows as $index => $subfield) {
            $rowData[] = $this->metaData($index, $subfield);
        }

        return $rowData;
    }

    /**
    * Section metadata.
    *
    * @param  int|string $index The section index
    * @param  string $section The section name
    * @param  string $classname Unique classname
    * @return array Metadata for this section
    */
    public function metadata($index, $section = NULL, $classname = NULL)
    {
        $classes = $this->cssClasses($index, $section);
        $unique_id = $this->flex_fieldname . '_' . $index . '_' . $section;
        $data = get_post_meta($this->postID, NULL, true);
        $sectionFields = [];

        foreach ($data as $key => $value) {

            // Only fields with a custom field postmeta key
            if (FALSE !== strpos($key, $unique_id)) {
                // Exclude fields that are prefixed with "_"
                if ('_' === $key[0]) continue;
                $simpleKey = str_replace($unique_id.'_', '', $key);
                $value = maybe_unserialize($value[0]);
                $fieldMetadata = $this->getFieldAttributes($key);
                $returnFormat = $fieldMetadata['return_format'] ?? NULL;
                $type = $fieldMetadata['type'] ?? NULL;

                $sectionFields[$simpleKey]['value'] = $value;
                $sectionFields[$simpleKey]['type'] = $type;
                $sectionFields[$simpleKey]['returnFormat'] = $returnFormat;
            }
        }

        $obj = new \stdClass;
        $obj->sectionName = $unique_id;
        $obj->flexFieldType = $section;
        $obj->index = $index;
        $obj->cssClasses = $classes;
        $obj->data = $sectionFields;
        $obj->filteredData = $this->filterDataByType($sectionFields);
        return $obj;
    }

    /**
    * Returns an array of appropriate CSS classes for a section,
    *
    * @param int|string $count The index of this section.
    * @param string $prefix The section type.
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
}
