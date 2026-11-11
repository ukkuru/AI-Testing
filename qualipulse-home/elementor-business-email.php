<?php
/**
 * QualiPulse: only accept business email addresses in Elementor Pro forms.
 *
 * Where to add it (pick one):
 *   1. The free "Code Snippets" plugin: Snippets > Add New, paste this file
 *      without the opening "<?php" line, set "Run snippet everywhere", Save and Activate.
 *   2. Your child theme's functions.php (never the parent theme, updates would erase it).
 *
 * What it does: when anyone submits an Elementor form, every Email field is checked.
 * If the address uses a free or personal provider, the form shows an error under
 * that field and is not sent.
 */
add_action( 'elementor_pro/forms/validation/email', function ( $field, $record, $ajax_handler ) {

	$free_domains = array(
		'gmail.com', 'googlemail.com', 'yahoo.com', 'yahoo.co.in', 'yahoo.co.uk', 'ymail.com',
		'hotmail.com', 'outlook.com', 'live.com', 'msn.com', 'aol.com', 'icloud.com', 'me.com',
		'mac.com', 'proton.me', 'protonmail.com', 'gmx.com', 'gmx.net', 'mail.com', 'yandex.com',
		'zoho.com', 'rediffmail.com', 'inbox.com', 'hey.com', 'fastmail.com', 'tutanota.com',
	);

	$value  = isset( $field['value'] ) ? strtolower( trim( $field['value'] ) ) : '';
	$at     = strrpos( $value, '@' );
	$domain = ( false === $at ) ? '' : substr( $value, $at + 1 );

	if ( '' !== $domain && in_array( $domain, $free_domains, true ) ) {
		$ajax_handler->add_error(
			$field['id'],
			'Please use your work email. Personal addresses such as Gmail, Yahoo or Outlook are not accepted.'
		);
	}
}, 10, 3 );
