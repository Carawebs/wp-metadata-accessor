<?php
namespace Carawebs\DataAccessor;

/**
* Class that returns data for dynamic sections.
*
*/
class FlexibleSections extends PostMetaData {

    /**
    * Instantiate the object with fieldname and post ID.
    *
    * @param int       $post_ID The post ID
    * @param string    $flex_fieldname The name of the flexible field
    */
    public function __construct ($postID = NULL, $flex_fieldname = 'flex') {
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
        * An array of the selected flexible field names, in the required display
        * order. The key for each array item corresponds to the display index,
        * the value being the field name. E.g: `[$index => $fieldName]`
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
    public function metaData($index, $section = NULL, $classname = NULL)
    {
        $classes = $this->cssClasses($index, $section);
        $unique_id = $this->flex_fieldname . '_' . $index . '_' . $section;
        $data = get_post_meta($this->postID, NULL, true);
        $sectionFields = [];

        /**
        * Filter metadata fields so that $data relates to flexible field data only.
        * Unset the element if the current flex field string not included in
        * the field key. Exclude fields having keys that are prefixed with "_".
        * These represent ACF-generated fields that reference `acf-field` posts.
        */
        foreach ($data as $key => $value) {
            if (FALSE === strpos($key, $unique_id)) unset($data[$key]);
            if ('_' === $key[0]) unset($data[$key]);
        }

        /**
         * Create a running manifest of fields that are repeater subfields, so
         * that these can be unset later.
         */
        static $repeaterSubFields = [];

        /**
        * Loop through the postmeta fields that include the specified $unique_id
        * in the field key. This value corresponds to the ACF flexible field
        * for the current section - field keys containing this string denote
        * the fields associated with the current section.
        */
        foreach ($data as $key => $value) {
            $simpleKey = str_replace($unique_id.'_', '', $key);
            $value = maybe_unserialize($value[0]);
            $fieldMetadata = $this->getFieldAttributes($key);
            $returnFormat = $fieldMetadata['return_format'] ?? NULL;
            $type = $fieldMetadata['type'] ?? NULL;

            /**
             * Attach subfield data to any repeater fields as a 'subfields' array.
             */
            if ('repeater' === $type) {

                // Loop through all relevant postmeta fields again
                foreach ($data as $k => $v) {

                    /**
                     * Only postmeta relating to repeater field contained by
                     * current flex field - i.e. the subfield keys. We're looping
                     * through postmeta fields and this is a repeater field - so
                     * `$key` is the repeater field name.
                     */
                    if (FALSE === strpos($k, $key.'_')) continue;

                    $repeaterSubFields[] = $k;

                    // Build subfield Metadata
                    $subFieldMetadata = $this->getFieldAttributes($k);
                    $subFieldReturnFormat = $subFieldMetadata['return_format'] ?? NULL;
                    $subFieldType = $subFieldMetadata['type'] ?? NULL;
                    $subFieldData = [
                        'value' => maybe_unserialize($v)[0],
                        'type' => $subFieldType,
                        'returnFormat' => $subFieldReturnFormat,
                    ];

                    $k = str_replace($key . '_', '', $k);
                    $subKeyArray = explode('_', $k);
                    $index = $subKeyArray[0];
                    $simpleSubKey = $subKeyArray[1];
                    $sectionFields[$simpleKey]['subfields'][$index][$simpleSubKey] = $subFieldData;
                }
            }

            $sectionFields[$simpleKey]['value'] = $value;
            $sectionFields[$simpleKey]['type'] = $type;
            $sectionFields[$simpleKey]['returnFormat'] = $returnFormat;
        }

        /**
         * Unset the unecessary repeater fields because this data is now nested
         * in the main $sectionFields array under the repeater field.
         */
        $sectionFieldsTrimmed = $sectionFields;
        foreach ($repeaterSubFields as $unsetKey) {
            unset($sectionFieldsTrimmed[str_replace($unique_id.'_', '', $unsetKey)]);
        }

        $obj = new \stdClass;
        $obj->sectionName = $unique_id;
        $obj->sectionCssId = str_replace('_', '-', $unique_id);
        $obj->flexFieldType = $section;
        $obj->index = $index;
        $obj->cssClasses = $classes;
        $obj->data = $sectionFieldsTrimmed;
        $obj->filteredData = $this->filterDataByType($sectionFieldsTrimmed);
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
