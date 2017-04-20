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
    * This method receives an array of postmeta data. Each element of the `$data`
    * array is structured like this:
    * ```php
    * "field_name" => [
    *    "value": "the value",
    *    "type": "text",
    *    "returnFormat": null
    * ],
    * ```
    *
    * @param  array $data Contains all necessary field attributes.
    * @return array Filtered data content.
    */
    public function filterDataByType(array $data) {
        $this->logger($data, false);
        $filteredData = [];

        foreach ($data as $key => $fieldAttributes) {
            if(empty($fieldAttributes['value'])) continue;
            if ('repeater' === $fieldAttributes['type']) {
                $value = [];
                foreach ($fieldAttributes['subfields'] as $index => $subField) {
                    foreach ($subField as $subFieldName => $subFieldAttributes) {
                        $value[$index][$subFieldName] = $this->filter(
                            $subFieldAttributes['value'],
                            $subFieldAttributes['type'],
                            $subFieldAttributes['returnFormat']);
                    }
                }
            } else {
                $value = $this->filter(
                    $fieldAttributes['value'],
                    $fieldAttributes['type'],
                    $fieldAttributes['returnFormat']);
            }
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
        case 'post_object':
            $output = $this->acfPostObjectField($content, $returnFormat);
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
        case "repeater":
        case "OEmbed":
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
    * @param string $metaFieldKey Custom metadata field key associated with a `post_name`
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

    /**
     * Process an array of Post IDs.
     *
     * Used to build post-to-post relationships.
     *
     * @param array $postIDs Post IDs
     * @param string $returnFormat Specify 'id' or 'object'
     * @return array Post IDs|Post objects
     */
    public function relationship(array $postIDs, $returnFormat = NULL)
    {
        $returnFormat = $returnFormat ?? 'id';
        if ('id' === $returnFormat) {
            return $postIDs;
        } elseif ('object' === $returnFormat) {
            return array_map(function($id) {
                return $this->postObject($id);
            }, $postIDs);
        }
    }

    public function AcfPostObjectField($postID, $returnFormat = NULL)
    {
        $returnFormat = $returnFormat ?? 'id';
        if ('id' === $returnFormat) {
            return $postID;
        } elseif ('object' === $returnFormat) {
            return $this->postObject($postID);
        }
    }

    /**
     * Create image data necessary for markup based on an image ID input.
     *
     * @param string|int $id The image ID
     * @param string $returnFormat The return format
     * @return array|object|string|int Determined by $returnFormat
     */
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

    /**
     * Prepare a modified post object.
     *
     * This returns the standard WordPress post object, with some additional properties:
     * - An array of data relating to the post featured image
     * - The post permalink
     * This saves work in the controller - these data are almost invariably needed
     * when using post objects returned from postmeta.
     *
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function postObject($id)
    {
        $obj = get_post($id);
        $obj->featuredImage = wp_prepare_attachment_for_js(get_post_thumbnail_id($id));
        $obj->permalink = get_permalink($id);
        return apply_filters('carawebs/wp-metadata-accessor/post-object', $obj, $id);
    }

    function logger($value, $overwrite = true)
    {
        $processedValue = json_encode($value, JSON_PRETTY_PRINT);
        $dump = var_export($value, true);
        $time = date('l jS F Y h:i:s A');
        $break = "\n";
        $break .= "-------------------------------------------------------------";
        $break .= "\n";

        $file = dirname(__FILE__) . '/log.txt';
        if (false === $overwrite) {
            $current = file_get_contents($file);
            $current .= $time . $break . $processedValue . $break . $dump;
        } else {
            $current = $time . $break . $processedValue . $break . $dump;
        }

        file_put_contents($file, $current);
    }
}
