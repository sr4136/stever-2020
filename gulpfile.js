"use strict";

// Import required Gulp plugins
var gulp = require("gulp");
var sass = require("gulp-sass");
var cssNano = require("gulp-cssnano");
var rename = require("gulp-rename");

// Initialize Sass with node-sass
sass.compiler = require("node-sass");

// Function to compile Sass files, minify CSS, and rename output
function compileSass(src, dest) {
  return gulp
    .src(src, { sourcemaps: true }) // Source files with sourcemaps
    .pipe(sass().on("error", sass.logError)) // Compile Sass and log errors
    .pipe(gulp.dest(dest, { sourcemaps: true })) // Output unminified CSS
    .pipe(cssNano()) // Minify CSS
    .pipe(rename({ extname: ".min.css" })) // Rename to .min.css
    .pipe(gulp.dest(dest, { sourcemaps: true })); // Output minified CSS
}

// Task to compile main Sass file
gulp.task("sass:main", function () {
  return compileSass("./sass/style.scss", "./"); // Compile main Sass
});

// Task to compile blocks Sass file
gulp.task("sass:blocks", function () {
  return compileSass("./sass/blocks.scss", "./css/"); // Compile blocks Sass
});

// Task to compile admin Sass file
gulp.task("sass:admin", function () {
  return compileSass("./sass/admin.scss", "./css/"); // Compile admin Sass
});

// Watch task to monitor changes in Sass files
gulp.task("watch", function () {
  gulp.watch("./sass/**/*.scss", gulp.series("default")); // Watch all .scss files
});

// Default task to run all Sass compilation tasks
gulp.task("default", gulp.series("sass:main", "sass:blocks", "sass:admin"));
