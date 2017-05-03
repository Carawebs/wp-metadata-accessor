<?php
namespace Carawebs\DataAccessor;

/**
* @since      1.0.0
* @package    WP Metadata Accessor
* @author     David Egan <david@carawebs.com>
* @link:      http://dev-notes.eu/
*/
class PostMetaData extends Data
{
    /**
     * Set the post id.
     */
    function __construct($postID = NULL)
    {
        parent::__construct($postID);
    }

    /**
     * Return field value.
     *
     * @param  string $fieldName The postmeta field name
     * @param  string $filterType Filter to apply
     * @return string Field value, possibly filtered
     */
    public function getField($fieldName, $filterType = NULL, $postID = NULL)
    {
        $id = !empty($postID) ? $postID : $this->postID;
        $rawValue = get_post_meta($id, $fieldName, true);
        if (empty($rawValue)) return;
        
        if ($filterType) {
            return $this->filter($rawValue, $filterType);
        } else {
            $fieldMetadata = $this->getFieldAttributes($fieldName);
            $type = $fieldMetadata['type'];
            $returnFormat = $fieldMetadata['return_format']  ?? NULL;

            /**
             * If this is an ACF field, return the properly filtered value based
             * on the returned field type. Otherwise return the raw value.
             */
            if (!empty($type)) {
                return $this->filter($rawValue, $type, $returnFormat);
            } else {
                return $rawValue;
            }
        }
    }

    /**
     * Apply 'the_content' WordPress filter to the returned post metadata.
     *
     * @see https://developer.wordpress.org/reference/hooks/the_content/  Documentation of 'the_content' WordPress filter
     * @param string $fieldName The postmeta field name
     * @param int|string $postID ID of post for which to fetch metadata
     * @return string HTML filtered by 'the_content'
     */
    public function getContentField($fieldName, $postID = NULL)
    {
        return $this->getField($fieldName, 'the_content', $postID);
    }

    /**
     * Fetch repeater fields.
     *
     * If the subfield is an image subfield - structured [$subfield => ['image_ID', 'size']]
     * $output is the image_ID, $type[0] is the string 'image_ID' - to give the data type
     * $type[1] is a string denoting the specified image size to return.
     *
     * @deprecated
     * @param  string $fieldName The postmeta field name
     * @param  array  $subfields Array of subfield arguments
     * @return string            HTML for output
     */
    public function getRepeaterField($fieldName, $subfields, $postID = NULL)
    {
        $id = !empty($postID) ? $postID : $this->postID;
        $repeater = get_post_meta( $id, $fieldName, true );
        if(!$repeater) return;
        $data = [];
        for( $i = 0; $i < $repeater; $i++ ) {
            $row = [];
            foreach($subfields as $subfield => $type) {
                $rawdata = $fieldName . '_' . $i . '_' . $subfield;
                $output = get_post_meta($id, $rawdata, true);
                if (is_array($type) && 'image_ID' == $type[0]) {
                    $output = $this->imageFilter( $output, $type );
                } else {
                    $output = $this->filter( $output, $type );
                }
                $row[$subfield] = $output;
            }
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Repeater field data.
     *
     * @param string $fieldName Name of the repeater field.
     * @param array $subFields Array of subfield names.
     * @param string|int $postID The post ID for which to fetch post metadata.
     * @return Array Repeater field data
     */
    public function getAcfRepeaterFieldData($fieldName, $subFields, $postID = NULL) : array
    {
        $id = !empty($postID) ? $postID : $this->postID;
        $repeater = get_post_meta($id, $fieldName, true); // Number of subfields
        if(!$repeater) return;

        $data = [];
        for($i = 0; $i < $repeater; $i++) {
            $row = [];
            foreach($subFields as $subField) {
                $subFieldName = $fieldName . '_' . $i . '_' . $subField;
                $subFieldData = get_post_meta($id, $subFieldName, true);
                $subFieldAtts = $this->getFieldAttributes($subFieldName);
                $type = $subFieldAtts['type'] ?? NULL;
                $returnFormat = $subFieldAtts['return_format'] ?? NULL;
                $subFieldData = $this->filter($subFieldData, $type, $returnFormat);
                $row[$subField] = $subFieldData;
            }
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Helper method to return image data from an image ID stored in postmeta.
     *
     * Receives a postmeta fieldname that holds an image ID, returns an array of
     * data that can be used to markup the image.
     * @param  string $fieldName meta_key in WP postmeta table.
     * @param  array  $type      Specify image_ID => image_size.
     * @return array             Data required to build image.
     */
    public function getImage($fieldName, $returnFormat = NULL)
    {
        $imageID = get_post_meta( $this->postID, $fieldName, true );
        if(empty($imageID)) return;

        $returnFormat = $returnFormat ?? 'array';
        return $this->filter( $imageID, 'image', $returnFormat );
    }
}
