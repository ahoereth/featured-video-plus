<?php
$options = array(
	'mode'      => 'replace',
	'alignment' => 'center',
	'conditions' => array(),

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
