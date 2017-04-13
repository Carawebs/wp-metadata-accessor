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
    function __construct()
    {
        $this->id = get_the_ID();
    }

    /**
     * Allow override of ID
     * @param string|int $id Post ID for which to fetch metadata
     */
    public function setID($id = NULL)
    {
        if (empty($id)) return $this;
        //$this->id = $id;
        return $this;
    }

    /**
     * Return field value.
     *
     * @param  string $fieldName  The postmeta field name
     * @param  string $filterType Filter to apply
     * @return string             Field value, possibly filtered
     */
    public function getField($fieldName, $filterType = NULL, $postID = NULL)
    {
        $id = !empty($postID) ? $postID : $this->id;
        $raw = get_post_meta($id, $fieldName, true);
        if ($filterType) {
            return $this->filter($raw, $filterType);
        } else {
            return $raw;
        }
    }

    /**
     * Apply 'the_content' WordPress filter to the returned post metadata.
     *
     * @param  string $fieldName The postmeta field name
     * @return string            HTML - filtered by 'the_content'
     */
    public function getContentField($fieldName)
    {
        return $this->getField($fieldName, 'the_content');
    }

    /**
     * Fetch repeater fields.
     *
     * If the subfield is an image subfield - structured [$subfield => ['image_ID', 'size']]
     * $output is the image_ID, $type[0] is the string 'image_ID' - to give the data type
     * $type[1] is a string denoting the specified image size to return.
     *
     * @param  string $fieldName The postmeta field name
     * @param  array  $subfields Array of subfield arguments
     * @return string            HTML for output
     */
    public function getRepeaterField($fieldName, $subfields, $id = NULL)
    {
        $id = !empty($id) ? $id : $this->id;
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
     * Helper method to return image data from an image ID stored in postmeta.
     *
     * Receives a postmeta fieldname that holds an image ID, returns an array of
     * data that can be used to markup the image.
     * @param  string $fieldName meta_key in WP postmeta table.
     * @param  array  $type      Specify image_ID => image_size.
     * @return array             Data required to build image.
     */
    public function getImage($fieldName, $type = NULL)
    {
        $imageID = get_post_meta( $this->id, $fieldName, true );
        return $this->imageFilter( $imageID, $type );
    }

    public function setFlexibleField($fieldName)
    {
        # code...
    }
}
