<?php

use cli\Shell;

/**
 * Class TestShell
 */
class TestShell extends PHPUnit_Framework_TestCase {

    /**
     * Test getting TERM columns.
     */
    function testColumns() {
		// Save.
		$env_term = getenv( 'TERM' );
		$env_columns = getenv( 'COLUMNS' );
		$env_shell_columns_reset = getenv( 'PHP_CLI_TOOLS_TEST_SHELL_COLUMNS_RESET' );

		putenv( 'PHP_CLI_TOOLS_TEST_SHELL_COLUMNS_RESET=1' );

		// No TERM should result in default 80.

		putenv( 'TERM' );
		putenv( 'COLUMNS=80' );

		$columns = cli\Shell::columns();
		$this->assertSame( 80, $columns );

		// TERM and COLUMNS should result in whatever COLUMNS is.

		putenv( 'TERM=vt100' );
		putenv( 'COLUMNS=100' );

		$columns = cli\Shell::columns();
		$this->assertSame( 100, $columns );

		// No TERM or COLUMNS could return anything so just check > 0.

		putenv( 'TERM' );
		putenv( 'COLUMNS' );

		$columns = cli\Shell::columns();
		$this->assertTrue( $columns > 0 );


		// TERM and no COLUMNS could return anything so just check > 0.

		putenv( 'TERM=vt100' );
		putenv( 'COLUMNS' );

		$columns = cli\Shell::columns();
		$this->assertTrue( $columns > 0 );

		// Restore.
		putenv( false === $env_term ? 'TERM' : "TERM=$env_term" );
		putenv( false === $env_columns ? 'COLUMNS' : "COLUMNS=$env_columns" );
		putenv( false === $env_shell_columns_reset ? 'PHP_CLI_TOOLS_TEST_SHELL_COLUMNS_RESET' : "PHP_CLI_TOOLS_TEST_SHELL_COLUMNS_RESET=$env_shell_columns_reset" );
	}

	/**
	 * Test whether STDOUT is piped or not.
	 */
	function testIsPiped() {
		// Save.
		$env_shell_pipe = getenv( 'SHELL_PIPE' );

		$php = "require '" . dirname( __DIR__ ) . "/lib/cli/Shell.php'; exit( (int) \\cli\\Shell::isPiped() );";

		putenv( 'SHELL_PIPE' );

		// No `posix_isatty()` on Windows so only do real test on *nix.
		if ( '\\' !== DIRECTORY_SEPARATOR ) {
			exec( 'php -r ' . escapeshellarg( $php ) . ' 1>&0', $output, $return_var ); // Redirect STDOUT to STDIN to get tty in `exec()`.
			$this->assertTrue( 0 === $return_var );

			exec( 'php -r ' . escapeshellarg( $php ), $output, $return_var ); // `exec()` pipes STDOUT.
			$this->assertTrue( 1 === $return_var );

			exec( 'php -r ' . escapeshellarg( $php ) . ' 1>/dev/null', $output, $return_var ); // Redirect STDOUT to null device.
			$this->assertTrue( 1 === $return_var );
		}

		putenv( 'SHELL_PIPE=0' );
		exec( 'php -r ' . escapeshellarg( $php ), $output, $return_var ); // `exec()` pipes STDOUT.
		$this->assertTrue( 0 === $return_var );

		putenv( 'SHELL_PIPE=1' );
		exec( 'php -r ' . escapeshellarg( $php ) . ' 1>&0', $output, $return_var ); // Redirect STDOUT to STDIN to get tty in `exec()`.
		$this->assertTrue( 1 === $return_var );

		// Restore.
		putenv( false === $env_shell_pipe ? 'SHELL_PIPE' : "SHELL_PIPE=$env_shell_pipe" );
	}

	/**
	 * Test whether shell is bash-like.
	 */
	function testIsBashlike() {
		// Save.
		$env_shell = getenv( 'SHELL' );

		$php = "require '" . dirname( __DIR__ ) . "/lib/cli/Shell.php'; exit( (int) \\cli\\Shell::is_bashlike() );";

		putenv( 'SHELL' );
		exec( 'php -r ' . escapeshellarg( $php ), $output, $return_var );
		$this->assertTrue( 0 === $return_var );

		putenv( 'SHELL=/bin/sh' );
		exec( 'php -r ' . escapeshellarg( $php ), $output, $return_var );
		$this->assertTrue( 0 === $return_var );

		putenv( 'SHELL=/bin/bash' );
		exec( 'php -r ' . escapeshellarg( $php ), $output, $return_var );
		$this->assertTrue( 1 === $return_var );

		putenv( 'SHELL=/usr/bin/zsh' );
		exec( 'php -r ' . escapeshellarg( $php ), $output, $return_var );
		$this->assertTrue( 1 === $return_var );

		putenv( 'SHELL=C:\\cygwin64\\bin\\bash.exe' );
		exec( 'php -r ' . escapeshellarg( $php ), $output, $return_var );
		$this->assertTrue( 1 === $return_var );

		putenv( 'SHELL=C:\\Program Files\\Git\\usr\\bin\\bash.exe' );
		exec( 'php -r ' . escapeshellarg( $php ), $output, $return_var );
		$this->assertTrue( 1 === $return_var );

		// Restore.
		putenv( false === $env_shell ? 'SHELL' : "SHELL=$env_shell" );
	}
}
