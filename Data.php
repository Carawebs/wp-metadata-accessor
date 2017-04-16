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
abstract class Data
{
    public function __construct($postID = NULL)
    {
        $this->postID = $postID ?? get_the_ID();
    }

    /**
    * Create an array of appropriately filtered data.
    *
    * @param  array $data In the format `['value'=>$val, 'type'=>$type]`.
    * @return array Filtered data content.
    */
    public function filterDataByType($data) {
        $filteredData = [];
        foreach ($data as $key => $fieldAttributes) {
            if(empty($fieldAttributes['value'])) {continue;}
            $value = $this->filter($fieldAttributes['value'], $fieldAttributes['type'], $fieldAttributes['returnFormat']);
            $filteredData[$key] = $value;
        }
        return $filteredData;
    }

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
    public function filter($content, $type, $returnFormat = NULL) {
        $output = '';
        switch($type) {
        case 'relationship':
            $output = $this->relationship($content, $returnFormat);
            break;
        case 'image':
            $output = $this->image($content, $returnFormat);
            break;
        case "text":
        case "esc_html":
            $output = esc_html($content);
            break;
        case "wysiwyg":
        case "the_content":
            $output = apply_filters('the_content', $content);
            break;
        case "esc_url":
            $output = esc_url($content);
            break;
        case "OEmbed":
            $output = $content;
            break;
        case 'date':
            $output = date('M j, Y', strtotime(esc_html($content)));
            break;
        case 'time':
            $output = strtotime(esc_html($content));
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
    * Return the ACF field type.
    *
    * When an ACF custom field value is saved against the key `$fieldName`, a
    * corresponding metadata field is added, in the format `'_'.$fieldName`. The value
    * referenced by this key is a `post_name` for a post of post_type `acf-field`.
    * The post_content of this post holds a serialized array of data about the
    * field. The most useful value in this case is 'type'.
    *
    * @param  string $metaFieldKey Custom metadata field key associated with a `post_name`
    * @return string The type of field (retrieved from `post_content`)
    */
    protected function getFieldAttributes($metaFieldKey)
    {
        $postName = get_post_meta($this->postID, '_' . $metaFieldKey, true);
        if(empty($postName)) return;

        global $wpdb;
        $data = $wpdb->get_col( $wpdb->prepare(
            "
            SELECT      post_content
            FROM        $wpdb->posts
            WHERE       post_name = %s
            ",
            $postName
        ));

        $data = unserialize($data[0]);
        $fieldChars = [];
        if (!empty($data['return_format'])) {
            $fieldChars['return_format'] = $data['return_format'];
        }
        $fieldChars['type'] = $data['type'];
        return $fieldChars;
    }

    public function relationship(array $postIDs, $returnFormat = NULL)
    {
        $returnFormat = $returnFormat ?? 'ids';
        var_dump($returnFormat);
        if ('ids' === $returnFormat) {
            return $postIDs;
        } elseif ('object' === $returnFormat) {
            return array_map(function($id){
                return get_post($id);
            }, $postIDs);
        }
    }

    private function image($id, $returnFormat = NULL)
    {
        $imgArray = wp_prepare_attachment_for_js($id);
        if ('array' === $returnFormat) {
            return $imgArray;
        } elseif ('object' === $returnFormat) {
            return (object)$imgArray;
        } elseif ('url' === $returnFormat) {
            return $imgArray['url'];
        } else {
            return $id;
        }
    }
}
