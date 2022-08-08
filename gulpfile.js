'use strict';

var gulp        = require( 'gulp' );
var sass        = require( 'gulp-sass' );
var cssNano     = require( 'gulp-cssnano' );
var rename      = require( 'gulp-rename' );
sass.compiler   = require( 'node-sass' );

gulp.task( 'sass:main', function () {
    return gulp.src( './sass/style.scss' )
        .pipe( sass().on( 'error', sass.logError ) )
        .pipe( gulp.dest( './' ) )
        .pipe( cssNano() )
        .pipe( rename( {
            extname: '.min.css'
        } ) )
        .pipe( gulp.dest( './' ) );
} );

gulp.task( 'sass:blocks', function () {
    return gulp.src( './sass/blocks.scss', { sourcemaps: true } )
        .pipe( sass().on( 'error', sass.logError ) )
        .pipe( gulp.dest( './css/' ) )
        .pipe( cssNano() )
        .pipe( rename( {
            extname: '.min.css'
        } ) )
        .pipe( gulp.dest( './css/', { sourcemaps: true } ) );
} );

gulp.task( 'sass:admin', function () {
    return gulp.src( './sass/admin.scss', { sourcemaps: true } )
		.pipe( sass().on( 'error', sass.logError ) )
        .pipe( gulp.dest( './css/' ) )
        .pipe( cssNano() )
        .pipe( rename( {
            extname: '.min.css'
        } ) )
        .pipe( gulp.dest( './css/', { sourcemaps: true } ) );
} );

gulp.task( 'watch', function() {
    gulp.watch('./sass/**/*.scss', gulp.series( [ 'sass:main', 'sass:blocks', 'sass:admin'] ) );
} );

gulp.task( 'default', gulp.series( [ 'sass:main', 'sass:blocks', 'sass:admin' ] ) );