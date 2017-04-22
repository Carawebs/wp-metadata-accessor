<?php
namespace Carawebs\DataAccessor;

/**
* Class that returns data for dynamic sections.
*
*/
class FlexibleSections extends PostMetaData {

    /**
    * An array of the selected flexible field names, in the required display
    * order. The key for each array item corresponds to the display index,
    * the value being the field name. E.g: `[$index => $fieldName]`
    * @var string $flexRows [description]
    */
    private $flexRows;

    /**
    * Post metadata for the flexible field.
    * @var array $flex_fieldname [description]
    */
    private $flexFieldMetaData;

    /**
    * Instantiate the object with fieldname and post ID.
    * @param int|string $postID The post ID
    * @param string $flex_fieldname The name of the flexible field
    */
    public function __construct ($postID = NULL, $flex_fieldname = 'flex') {
        $this->flex_fieldname = $flex_fieldname;
        parent::__construct($postID);

        // Set all flexible fields as a property.
        $this->setFlexibleFieldData($postID);
    }

    private function setFlexibleFieldData($postID)
    {
        $this->flexRows = get_post_meta( $this->postID, $this->flex_fieldname, true );

        // Get all metadata attached to the post.
        $metaData = get_post_meta($this->postID, NULL, true);

        // Only include fields relating to the specific flexible field
        $flexFieldId = $this->flex_fieldname . '_';
        foreach ($metaData as $key => $value) {
            if (FALSE === strpos($key, $flexFieldId)) unset($metaData[$key]);
            if ('_' === $key[0]) unset($metaData[$key]);
        }
        $this->flexFieldMetaData = $metaData;
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
        * Get all flexible field metadata attached to the post.
        */
        if (!$this->flexRows) return;
        $rowData = [];
        foreach( (array)$this->flexRows as $index => $flexSection) {

            // All fields for this flexible section will contain this string
            $sectionKey = $this->flex_fieldname . '_' . $index . '_' . $flexSection;

            // Metadata array - for this section only
            $sectionMetaData = [];
            foreach ($this->flexFieldMetaData as $key => $value) {

                // Data relating to this $flexSection only
                if (FALSE === strpos($key, $sectionKey)) continue;

                // Exclude ACF "hidden" custom fields
                if ('_' === $key[0]) continue;
                $sectionMetaData[$key] = $value;
            }
            $rowData[] = $this->processMetaData($index, $flexSection, $sectionMetaData);
        }
        return $rowData;
    }

    /**
    * Build an object containing all data required for a given section.
    *
    * @param  int|string $index The section index.
    * @param  string $flexSection; The section name.
    * @param  array $data Metadata fields for this section.
    * @return object Metadata for this section.
    */
    public function processMetaData($index, $flexSection = NULL, $data)
    {
        $uniqueSectionId = $this->flex_fieldname . '_' . $index . '_' . $flexSection;
        $sectionFields = [];

        /**
        * Create a running manifest of fields that are repeater subfields, so
        * that these can be unset later. We don't want to return data for the
        * repeater subfields in the main array - they are nested under the
        * repeater fieldname.
        */
        static $repeaterSubFields = [];

        /**
        * Loop through the postmeta fields for the current section.
        */
        foreach ($data as $key => $value) {
            $simpleKey = str_replace($uniqueSectionId.'_', '', $key);
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
                    unset($subKeyArray[0]); // used as an identifier in a loop, so don't want an index
                    $simpleSubKey = implode('_', $subKeyArray);
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
            unset($sectionFieldsTrimmed[str_replace($uniqueSectionId.'_', '', $unsetKey)]);
        }

        $obj = new \stdClass;
        $obj->sectionName = $uniqueSectionId;
        $obj->sectionCssId = str_replace('_', '-', $uniqueSectionId);
        $obj->flexFieldType = $flexSection;;
        $obj->index = $index;
        $obj->cssClasses = $this->cssClasses($index, $flexSection);
        $obj->data = $sectionFieldsTrimmed;
        $obj->filteredData = $this->filterDataByType($sectionFieldsTrimmed);
        $obj->partial = str_replace('_', '-', $flexSection);
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
        return [
            'flexible-field',
            'flex-section',
            'flex-section-' . $count,
            str_replace('_', '-', $prefix) . '-' . $count,
            str_replace('_', '-', $prefix)
        ];
    }
}
