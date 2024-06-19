<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Parse file headers
 *
 * @package   rabbit-framework
 * @author    Sematico LTD <hello@sematico.com>
 * @copyright 2020 Sematico LTD
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link      https://sematico.com
 */

namespace Rabbit\Support;

trait WordPressFileHeaders {

	/**
	 * Return the file headers as an associative array.
	 *
	 * @param string $path
	 * @param array  $headers
	 *
	 * @return array
	 */
	public function headers( string $path, array $headers ): array {
		$data       = $this->read( $path );
		$properties = [];

		foreach ( $headers as $field => $regex ) {
			if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $data, $match ) && $match[1] ) {
				$properties[ $field ] = trim( preg_replace( '/\s*(?:\*\/|\?>).*/', '', $match[1] ) );
			} else {
				$properties[ $field ] = '';
			}
		}

		return $properties;
	}

	/**
	 * Get a partial content of given file.
	 *
	 * @param string $path
	 * @param int    $length
	 *
	 * @return string
	 */
	public function read( string $path, int $length = 8192 ): string {
		$handle  = fopen( $path, 'r' );
		$content = fread( $handle, $length );
		fclose( $handle );

		return str_replace( "\r", "\n", $content );
	}
}
