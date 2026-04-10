'use strict';

// Import required Gulp plugins
const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const cssnano = require('gulp-cssnano');
const rename = require('gulp-rename');

// Paths configuration
const paths = {
	sass: './sass/**/*.scss',
	mainSass: './sass/style.scss',
	blocksSass: './sass/blocks.scss',
	adminSass: './sass/admin.scss',
	adminBlocksSass: './sass/admin-blocks.scss',
	cssDest: './',
	cssBlocksDest: './css/',
};

/**
 * Compile Sass files to CSS with minified versions
 * @param {string} src - Source Sass file path
 * @param {string} dest - Destination directory for compiled CSS
 * @returns {stream} Gulp stream
 */
function compileSass(src, dest) {
	return gulp
		.src(src, { allowEmpty: true })
		.pipe(sass({
			outputStyle: 'expanded'
		}).on('error', sass.logError))
		.pipe(gulp.dest(dest))
		.pipe(cssnano({
			preset: ['default', {
				normalizeSelectors: false,
				discardEmpty: false
			}]
		}))
		.pipe(rename({ extname: '.min.css' }))
		.pipe(gulp.dest(dest));
}

// Compile main stylesheet
function sassMain() {
	return compileSass(paths.mainSass, paths.cssDest);
}

// Compile blocks styles
function sassBlocks() {
	return compileSass(paths.blocksSass, paths.cssBlocksDest);
}

// Compile admin styles
function sassAdmin() {
	return compileSass(paths.adminSass, paths.cssBlocksDest);
}

// Compile admin blocks styles
function sassAdminBlocks() {
	return compileSass(paths.adminBlocksSass, paths.cssBlocksDest);
}

// Watch for Sass file changes
function watch() {
	gulp.watch(paths.sass, gulp.series(sassMain, sassBlocks, sassAdmin, sassAdminBlocks));
}

// Export tasks
exports.sass = gulp.series(sassMain, sassBlocks, sassAdmin, sassAdminBlocks);
exports.watch = watch;
exports.default = exports.sass;
