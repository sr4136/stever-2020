'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');

sass.compiler = require('node-sass');


gulp.task( 'sass:main', function () {
    return gulp.src( './sass/style.scss' )
        .pipe( sass().on( 'error', sass.logError ) )
        .pipe( gulp.dest( './' ) );
} );
gulp.task( 'sass:blocks', function () {
    return gulp.src( './sass/blocks.scss' )
        .pipe( sass().on( 'error', sass.logError ) )
        .pipe( gulp.dest( './css/' ) );
} );
gulp.task( 'sass:admin', function () {
    return gulp.src( './sass/admin.scss' )
        .pipe( sass().on( 'error', sass.logError ) )
        .pipe( gulp.dest( './css/' ) );
} );


gulp.task( 'default', function() {
    gulp.watch('./sass/**/*.scss', gulp.series( [ 'sass:main', 'sass:blocks', 'sass:admin'] ) );
} );