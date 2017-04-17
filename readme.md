WordPress Metadata Access
=========================
Access WordPress data held in the postmeta table.

## Usage
Run:
~~~bash
composer require carawebs/wp-metadata-accessor
~~~

## Examples: Simple Postmeta Field Data
From within theme files, instantiate `Carawebs\DataAccessor\PostMetaData`.

You can then easily return field data by means of the `PostMetaData::getField()` method.

~~~
PostMetaData::getField( string $fieldName, string $filter)
~~~

Sample usage:
~~~php
$postMeta = new Carawebs\DataAccessor\PostMetaData;

// returned content, unfiltered
$extra_content = $postMeta->getField('extra_content');

// returned content, filtered by `esc_html()`
$intro_text = $postMeta->getField('intro_text', 'text');

// returned content, filtered by `esc_html()`
$intro_text = $postMeta->getField('intro_text', 'esc_html');

// returned content filtered by WordPress 'the_content' filter
$extra_content = $postMeta->getContentField('extra_content');
~~~

## Returned Data
The `PostMetaData::getField()` method accepts an optional string denoting the field type as a second parameter. This is used to determine the filtering method that should be applied to the returned data.

If you're using ACF fields you don't specify a field type - this is determined automatically.

### Returned Data Processing

|Parameter|Filter or Method Used on `$content`|
|-|-|
|"relationship"| `Data::relationship(array $postIDs, $returnFormat = NULL)`: returns either an array of post IDs or customised post objects |
|"image"| `Data::image($id, $returnFormat = NULL)`: returns either an image data array, object, url or ID |
|"esc_html"| `esc_html($content)`|
|"text"|`esc_html( $content )`|
|"esc_url"| `esc_url( $content )`|
|"the_content"| `apply_filters( 'the_content', $content )`|
|"wysiwyg"| `apply_filters( 'the_content', $content )`|
|"date"| `date( 'M j, Y', strtotime( esc_html( $content ) ) )`|
|"float"|`(float)$content`|
|"int"|`(int)$content`|
|"OEmbed"| None |
|"object"| None|
|Unrecognized string|`wp_kses_post($content)`|

## ACF Repeater Field Data
Fetch repeater field data from post_meta table. Returns subfield data grouped by "row" into arrays.

ACF repeater field data is stored in the postmeta table as a collection of
records. Repeater fields allow the editor to add as many groups of subfields
as necessary.

The repeater field key returns an integer value that represents the number
of repeater field records - this allows each record to have a unique index.
The subfields are created by concatenating the repeater field name with the
index and the subfield name. This allows as many items as necessary to be
added. In key => value notation, the data collection looks like this:

Repeater field: $repeater => $count
First repeater subfield: $first_subfield => $repeater . '_' . $index . '_' . $first_subfield
Second repeater subfield: $second_sibfield => $repeater . '_' . $index . '_' . $second_subfield // etc

To use:

~~~php
$postMeta = new Carawebs\DataAccessor\PostMetaData;

$carouselSubfields = [
    'image' => ['image_ID', 'full'], // denotes an image ID subfield, image size to return
    'description' => 'text' // subfield name, filter to apply
];

$carouselData = $postMeta->getRepeaterField('slider', $carouselSubfields);
~~~

## Post-to-Post Relationships
The field type 'relationship' fetches an array of post IDs.

For an ACF relationship field, `$this->postMeta->getField('related_posts')` ...will return type based on that specified in the ACF field GUI.

Otherwise, you can pass in a field name and specify the 'relationship' type and an array of post IDs will be returned: `$this->postMeta->getField('related_posts', 'relationship')`.

If you're using ACF fields and the field 'return_format' is set to return an object, a modified WordPress post object with additional properties representing the post featured image and permalink will be returned. This Object is filtered with `'carawebs/wp-metadata-accessor/post-object'`. To use this filter, add something like this within the active theme:

~~~php
add_filter('carawebs/wp-metadata-accessor/post-object', function($obj, $id) {
    $obj->newProperty = someFunction($id);
    return $obj;
}, 1, 2);
~~~
