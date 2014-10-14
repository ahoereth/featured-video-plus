<?php
$options = array (
	'mode'     => 'replace',
	'align'    => 'center',
	'issingle' => false,
	'ishome'   => false,

	'sizing' => array(
		'responsive' => true,
		'width'  => 640,
	),

	'default_args' => array(
		'general'     => array(),
		'vimeo'       => array(),
		'youtube'     => array(),
		'dailymotion' => array(),
	),
);

update_option( 'fvp-settings', $options );
update_option( 'fvp-version', FVP_VERSION );
