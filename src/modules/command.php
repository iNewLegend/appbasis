<?php
/**
 * @file   : modules/command.php
 * @author Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Modules;

class Command {

	/**
	 * Command name (aka controller).
	 *
	 * @var string
	 */
	private $name = 'welcome';

	/**
	 * Command method (function).
	 *
	 * @var string
	 */
	private $method = 'index';

	/**
	 * Command arguments.
	 *
	 * @var array
	 */
	private $arguments = [];

	/**
	 * Function __construct() : Construct Command Module and parse `$cmd`.
	 *
	 * @param string $cmd
	 * @param array  $args
	 */
	public function __construct( string $cmd = '', array $args = [] ) {
		$this->setArguments( $args );

		if ( ! empty( $cmd ) ) {
			$this->parse( $cmd );
		}
	}

	/**
	 * Function parse() : Parse command from format eg: '/name/methods/params'.
	 *
	 * @param string $cmd
	 *
	 * @return void
	 */
	public function parse( string $cmd ) {
		if ( ! empty( $cmd ) && is_string( $cmd ) ) {
			// Remove forward slash from the start & end.
			$cmd = trim( $cmd, '/' );
			$cmd = rtrim( $cmd, '/' );

			// Removes all illegal URL characters from a string.
			$cmd = explode( '/', $cmd );

			// Set name aka controller.
			if ( isset( $cmd[0] ) && ! empty( $cmd[0] ) ) {
				// only abc for controller name
				$cmd[0] = preg_replace( "/[^a-zA-Z]+/", "", $cmd[0] );

				$this->name = $cmd[0];
				unset( $cmd[0] );
			}

			// Set method.
			if ( isset( $cmd[1] ) ) {
				// Only abc and digits for method name.
				$cmd[1] = preg_replace( "/[^a-zA-Z0-9]+/", "", $cmd[1] );

				$this->method = $cmd[1];
				unset( $cmd[1] );
			}

			// Set args.
			if ( ! empty( $cmd ) ) {

				foreach ( $cmd as $key => $param ) {
					$cmd[ $key ] = filter_var( $param, FILTER_SANITIZE_STRING );
				}

				$this->arguments = array_values( $cmd );
			}
		}
	}

	/**
	 * Function getName() : Get command name.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Function setName() : Set command name.
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	public function setName( string $name ) {
		$this->name = $name;
	}

	/**
	 * Function getMethod() : Get command method name.
	 *
	 * @return string
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * Function setMethod() : Set method name.
	 *
	 * @param string $method
	 *
	 * @return void
	 */
	public function setMethod( string $method ) {
		$this->method = $method;
	}

	/**
	 * Function getArguments() : Get command parameters
	 *
	 * @return array
	 */
	public function getArguments() {
		return $this->arguments;
	}

	/**
	 * Function setArguments() : Set command arguments.
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function setArguments( array $args ) {
		$this->arguments = $args;
	}

	/**
	 * Function isEmpty() : Check is empty command on `$this->cmd`.
	 *
	 * @return bool
	 */
	public function isEmpty() {
		return empty( $this->cmd );
	}

	/**
	 * Function hasArguments().
	 *
	 * @return bool
	 */
	public function hasArguments() {
		return boolval( count( $this->arguments ) );
	}

	/**
	 * Function __toString() : Return's command in JSON format.
	 *
	 * @return string
	 */
	public function __toString() {
		return json_encode( [
			$this->name,
			$this->method,
			$this->arguments,
		] );
	}
} // EOF modules/command.php
