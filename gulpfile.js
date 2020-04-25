/* eslint-disable */
/**
 * Gulp File
 *
 * 1) Make sure you have node and npm installed locally
 *
 * 2) Install all the modules from package.json:
 * $ npm install
 *
 * 3) Run gulp to minify javascript and css using the 'gulp' command.
 *
 * 4a) Run `gulp serve` to start a BrowserSync server that pushes updates on file changes.
 * Browser tabs accessing the site via this proxy (http://localhost:8242) will get CSS updates injected, and reloaded on JS changes.
 * Configuration: set environment variables at runtime or in a `.env` file:
 * WORDPRESS_HOST= URL of local Wordpress instance (Default: localhost:8240)
 * BROWSERSYNC_PORT= Port for (Default: 8242)
 *
 * 4b) Run `gulp watch` to automatically compile CSS and JS files when they change.
 *
 */

var babel           = require( 'gulp-babel' );
var checktextdomain = require( 'gulp-checktextdomain' );
var chmod           = require( 'gulp-chmod' );
var del             = require( 'del' );
var exec            = require( 'child_process' ).exec;
var gulp            = require( 'gulp' );
var cleanCSS        = require( 'gulp-clean-css' );
var rename          = require( 'gulp-rename' );
var sass            = require( 'gulp-sass' );
var sort            = require( 'gulp-sort' );
var uglify          = require( 'gulp-uglify' );
var wpPot           = require( 'gulp-wp-pot' );
var zip             = require( 'gulp-zip' );
var process         = require( 'process' );
var env             = process.env;

var paths = {
	scripts: [ 'assets/js/**/*.js', '!assets/js/**/*.min.js' ],
	css: [ 'assets/css/**/*.scss' ],
	packageContents: [
		'assets/**/*',
		'!assets/**/*.scss',
		'changelog.txt',
		'readme.txt',
		'includes/**/*',
		'lang/**/*',
		'sensei-lms-status.php'
	],
	packageDir: 'build/sensei-lms-status',
	packageZip: 'build/sensei-lms-status.zip'
};

gulp.task( 'clean', gulp.series( function( cb ) {
	return del( [
		'assets/js/**/*.min.js',
		'assets/js/**/*.min.js',
		'assets/css/**/*.min.css',
		'build'
	], cb );
} ) );

function buildCSS() {
	return gulp.src( paths.css )
		.pipe( sass().on( 'error', sass.logError ) )
		.pipe( cleanCSS() )
		.pipe( gulp.dest( 'assets/css' ) );
}

function buildJS() {
	return gulp.src( paths.scripts )
		.pipe( babel( {
			'configFile': './.babelrc-legacy',
		} ) )
		// This will minify and rename to *.min.js
		.pipe( uglify() )
		.pipe( rename( { extname: '.min.js' } ) )
		.pipe( chmod( 0o644 ) )
		.pipe( gulp.dest( 'assets/js' ) );
}

gulp.task( 'CSS', gulp.series( buildCSS ) );

gulp.task( 'JS', gulp.series( buildJS ) );

gulp.task( 'pot', gulp.series( function() {
	return gulp.src( [ '**/**.php', '!node_modules/**', '!vendor/**', '!build/**' ] )
		.pipe( sort() )
		.pipe( wpPot( {
			domain: 'sensei-lms-status',
			bugReport: 'https://translate.wordpress.org/projects/wp-plugins/sensei-lms-status/'
		} ) )
		.pipe( gulp.dest( 'lang/sensei-lms-status.pot' ) );
} ) );

gulp.task( 'textdomain', gulp.series( function() {
	return gulp.src( [ '**/*.php', '!node_modules/**', '!build/**' , '!vendor/**' ] )
		.pipe( checktextdomain( {
			text_domain: 'sensei-lms-status',
			keywords: [
				'__:1,2d',
				'_e:1,2d',
				'_x:1,2c,3d',
				'esc_html__:1,2d',
				'esc_html_e:1,2d',
				'esc_html_x:1,2c,3d',
				'esc_attr__:1,2d',
				'esc_attr_e:1,2d',
				'esc_attr_x:1,2c,3d',
				'_ex:1,2c,3d',
				'_n:1,2,4d',
				'_nx:1,2,4c,5d',
				'_n_noop:1,2,3d',
				'_nx_noop:1,2,3c,4d'
			]
		} ) );
} ) );

gulp.task( 'test', gulp.series( function phpunit( cb ) {
	var phpunitProcess = exec( './vendor/bin/phpunit', cb );
	phpunitProcess.stdout.pipe( process.stdout );
	phpunitProcess.stderr.pipe( process.stderr );
} ) );

gulp.task( 'build', gulp.series( 'test', 'clean', 'CSS', 'JS' ) );
gulp.task( 'build-unsafe', gulp.series( 'clean', 'CSS', 'JS' ) );

gulp.task( 'copy-package', function() {
	return gulp.src( paths.packageContents, { base: '.' } )
		.pipe( gulp.dest( paths.packageDir ) );
} );

gulp.task( 'zip-package', function() {
	return gulp.src( paths.packageDir + '/**/*', { base: paths.packageDir + '/..' } )
		.pipe( zip( paths.packageZip ) )
		.pipe( gulp.dest( '.' ) );
} );

gulp.task( 'package', gulp.series( 'build', 'copy-package', 'zip-package' ) );
gulp.task( 'package-unsafe', gulp.series( 'build-unsafe', 'copy-package', 'zip-package' ) );

gulp.task( 'default', gulp.series( 'build' ) );

gulp.task( 'watch', function() {
	gulp.watch( paths.css, { delay: 50 }, gulp.series( 'CSS' ) );
	gulp.watch( paths.scripts, gulp.series( 'JS' ) );
} );
