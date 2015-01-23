<?php
/**
 * Download an image from the specified URL and attach it to a post.
 * Modified version of core function media_sideload_image() in /wp-admin/includes/media.php  (which returns an html img tag instead of attachment ID)
 * Additional functionality: ability override actual filename, set as post thumbnail, and to pass $post_data to override values in wp_insert_attachment (original only allowed $desc)
 *
 * @since 1.4
 *
 * @param string $url (required) The URL of the image to download
 * @param int $post_id (required) The post ID the media is to be associated with
 * @param bool $thumb (optional) Whether to make this attachment the Featured Image for the post
 * @param string $filename (optional) Replacement filename for the URL filename (do not include extension)
 * @param array $post_data (optional) Array of key => values for wp_posts table (ex: 'post_title' => 'foobar', 'post_status' => 'draft')
 * @return int|object The ID of the attachment or a WP_Error on failure
 */
function somatic_attach_external_image( $url = null, $post_id = null, $thumb = null, $filename = null, $post_data = array() ) {
	if ( !$url || !$post_id ) return new WP_Error('missing', "Need a valid URL and post ID...");
	if ( !self::array_is_associative( $post_data ) ) return new WP_Error('missing', "Must pass post data as associative array...");

	// Download file to temp location, returns full server path to temp file, ex; /home/somatics/public_html/mysite/wp-content/26192277_640.tmp MUST BE FOLLOWED WITH AN UNLINK AT SOME POINT
	$tmp = download_url( $url );

	// If error storing temporarily, unlink
	if ( is_wp_error( $tmp ) ) {
		@unlink($file_array['tmp_name']); // clean up
		$file_array['tmp_name'] = '';
		return $tmp; // output wp_error
	}

	preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches); // fix file filename for query strings
	$url_filename = basename($matches[0]); // extract filename from url for title
	$url_type = wp_check_filetype($url_filename); // determine file type (ext and mime/type)

	// override filename if given, reconstruct server path
	if ( !empty( $filename ) ) {
		$filename = sanitize_file_name($filename);
		$tmppath = pathinfo( $tmp ); // extract path parts
		$new = $tmppath['dirname'] . "/". $filename . "." . $tmppath['extension']; // build new path
		rename($tmp, $new); // renames temp file on server
		$tmp = $new; // push new filename (in path) to be used in file array later
	}

	// assemble file data (should be built like $_FILES since wp_handle_sideload() will be using)
	$file_array['tmp_name'] = $tmp; // full server path to temp file

	if ( !empty( $filename ) ) {
		$file_array['name'] = $filename . "." . $url_type['ext']; // user given filename for title, add original URL extension
	} else {
		$file_array['name'] = $url_filename; // just use original URL filename
	}

	// set additional wp_posts columns
	if ( empty( $post_data['post_title'] ) ) {
		$post_data['post_title'] = basename($url_filename, "." . $url_type['ext']); // just use the original filename (no extension)
	}

	// make sure gets tied to parent
	if ( empty( $post_data['post_parent'] ) ) {
		$post_data['post_parent'] = $post_id;
	}

	// required libraries for media_handle_sideload
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/media.php');
	require_once(ABSPATH . 'wp-admin/includes/image.php');

	// do the validation and storage stuff
	$att_id = media_handle_sideload( $file_array, $post_id, null, $post_data ); // $post_data can override the items saved to wp_posts table, like post_mime_type, guid, post_parent, post_title, post_content, post_status

	// If error storing permanently, unlink
	if ( is_wp_error($att_id) ) {
		@unlink($file_array['tmp_name']); // clean up
		return $att_id; // output wp_error
	}

	// set as post thumbnail if desired
	if ($thumb) {
		set_post_thumbnail($post_id, $att_id);
	}

	return $att_id;
}
