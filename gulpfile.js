var gulp = require('gulp');
var less = require('gulp-less');
var uglify = require('gulp-uglify');
var rename = require("gulp-rename");
var rimraf = require('gulp-rimraf');


// run everything, then watch for changes
gulp.task('default', ['less', 'js'], function() {
  gulp.watch('./js/*.js', ['js']);
  gulp.watch('./styles/*.less', ['less']);
});


// remove minified js and compiled less/css
gulp.task('clean', function() {
  return gulp.src([ './styles/*.css', './js/*.min.js' ], { read: false })
    .pipe(rimraf());
});


// all *.less files in ./styles are compiled and minified and saved as *.css
gulp.task('less', function () {
  gulp.src(['./styles/*.less', '!**/_*.less'])
    .pipe(less({
      cleancss: true
    }))
    .pipe(gulp.dest('./styles/'));
});


// all *.js files in ./js are minified and saved as *.min.js
gulp.task('js', function() {
  gulp.src('./js/*.js')
    .pipe(uglify())
    .pipe(rename({
      suffix: ".min"
     }))
    .pipe(gulp.dest('./js'))
});
