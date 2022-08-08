'use strict';

var gulp        = require( 'gulp' );
var sass        = require( 'gulp-sass' );
var cssNano     = require( 'gulp-cssnano' );
var rename      = require( 'gulp-rename' );
var sourcemaps  = require( 'gulp-sourcemaps' );

sass.compiler = require( 'node-sass' );

gulp.task( 'sass:main', function () {
    return gulp.src( './sass/style.scss' )
        .pipe( sourcemaps.init() )
        .pipe( sass().on( 'error', sass.logError ) )
        .pipe( gulp.dest( './' ) )
        .pipe( cssNano() )
        .pipe( rename( {
            extname: '.min.css'
        } ) )
        .pipe( sourcemaps.write( './' ) )
        .pipe( gulp.dest( './' ) );
} );

gulp.task( 'sass:blocks', function () {
    return gulp.src( './sass/blocks.scss' )
        .pipe( sourcemaps.init() )
        .pipe( sass().on( 'error', sass.logError ) )
        .pipe( gulp.dest( './css/' ) )
        .pipe( cssNano() )
        .pipe( rename( {
            extname: '.min.css'
        } ) )
        .pipe( sourcemaps.write( './css/' ) )
        .pipe( gulp.dest( './' ) );
} );

gulp.task( 'sass:admin', function () {
    return gulp.src( './sass/admin.scss' )
        .pipe( sourcemaps.init() )
		.pipe( sass().on( 'error', sass.logError ) )
        .pipe( gulp.dest( './css/' ) )
        .pipe( cssNano() )
        .pipe( rename( {
            extname: '.min.css'
        } ) )
        .pipe( sourcemaps.write( './' ) )
        .pipe( gulp.dest( './css/' ) );
} );

gulp.task( 'watch', function() {
    gulp.watch('./sass/**/*.scss', gulp.series( [ 'sass:main', 'sass:blocks', 'sass:admin'] ) );
} );

gulp.task( 'default', gulp.series( [ 'sass:main', 'sass:blocks', 'sass:admin' ] ) );