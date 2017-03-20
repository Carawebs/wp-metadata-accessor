WordPress Metadata Access
=========================
Access WordPress data held in the postmeta table.

## Usage
Run:
~~~bash
composer require carawebs/wp-metadata-accessor
~~~

## Examples
From within theme files, instantiate `Carawebs\DataAccessor\PostMetaData`.

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
$carouselSubfields = [
    'image' => ['image_ID', 'full'], // denotes an image ID subfield, image size to return
    'description' => 'text' // subfield name, filter to apply
];

$carouselData = $this->postMeta->getRepeaterField('slider', $carouselSubfields);
~~~
