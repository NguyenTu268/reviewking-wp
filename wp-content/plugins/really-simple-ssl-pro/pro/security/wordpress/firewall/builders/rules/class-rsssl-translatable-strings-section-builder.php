<?php
/**
 * class-rsssl-translatable-strings-section-builder.php
 *
 * Builds the “Strings for the block page” (translatable) section,
 * allowing optional overrides and outputting sanitized translations in builder format.
 *
 * @package RSSSL\Pro\Security\WordPress\Firewall\Builders\Rules
 */
namespace RSSSL\Pro\Security\WordPress\Firewall\Builders\Rules;

use RSSSL\Pro\Security\WordPress\Contracts\Rsssl_Rule_Section_Builder_Interface;

/**
 * Class Rsssl_Translatable_Strings_Section_Builder
 *
 * @package RSSSL\Pro\Security\WordPress\Firewall\Builders\Rules
 */
class Rsssl_Translatable_Strings_Section_Builder implements Rsssl_Rule_Section_Builder_Interface {


	/**
	 * Builds the translatable strings section.
	 */
	public function build(): string
	{
	    return $this->buildTranslatableStringsSection();
	}

	/**
	 * Builds the translatable strings section body.
	 */
	private function buildTranslatableStringsSection(): string
	{
	    $translations = [
	        'apology'            => $this->getApology(),
	        'message'            => $this->getMessage(),
	        'error_code'         => $this->getErrorCode(),
	        'apology_404'        => $this->getApology(),
	        'message_404'        => $this->getMessage404(),
	        'message_user_agent' => $this->getMessageUser(),
	    ];
	    $eol = PHP_EOL;
	    $content = '';
	    foreach ($translations as $var => $value) {
	        $content .= sprintf('$%s = "%s";%s', $var, $value, $eol);
	    }
	    return $content;
	}

	/**
	 * Retrieves the apology text.
	 */
	private function getApology(): string
	{
	    return htmlspecialchars(__( "We're sorry.", 'really-simple-ssl' ), ENT_QUOTES | ENT_HTML5);
	}

	/**
	 * Retrieves the message text.
	 */
	private function getMessage(): string
	{
	    return htmlspecialchars(__('This website is unavailable in your region.', 'really-simple-ssl'), ENT_QUOTES | ENT_HTML5);
	}

	/**
	 * Retrieves the 404 message text.
	 */
	private function getMessage404(): string
	{
	    return htmlspecialchars(__('Your access to this site has been temporarily denied', 'really-simple-ssl'), ENT_QUOTES | ENT_HTML5);
	}

	/**
	 * Retrieves the user agent message text.
	 */
	private function getMessageUser(): string
	{
	    return htmlspecialchars(__('Your access to this site has been denied', 'really-simple-ssl'), ENT_QUOTES | ENT_HTML5);
	}

	/**
	 * Retrieves the error code text.
	 */
	private function getErrorCode(): string
	{
	    return htmlspecialchars(__('Error code: 403', 'really-simple-ssl'), ENT_QUOTES | ENT_HTML5);
	}

	/**
	 * Returns the marker for this section.
	 */
	public function getMarker(): string
	{
	    return 'Translatable Strings Section';
	}
}