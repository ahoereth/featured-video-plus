/* global require */

var gulp   = require('gulp');
var less   = require('gulp-less');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var readme = require('gulp-readme-to-markdown');
var del    = require('del');


// run everything, then watch for changes
gulp.task('default', ['less', 'js', 'readme'], function() {
  gulp.watch(['./js/*.js', '!./js/*.min.js'], ['js']);
  gulp.watch('./styles/*.less', ['less']);
  gulp.watch('./readme.txt', ['readme']);
});


// remove minified js and compiled less/css
gulp.task('clean', function(cb) {
  del([ './styles/*.css', './js/*.min.js' ], cb);
});


// all *.less files in ./styles are compiled and minified and saved as *.css
gulp.task('less', function () {
  return gulp.src(['./styles/*.less', '!**/_*.less'])
    .pipe(less({
      cleancss: true
    }))
    .pipe(gulp.dest('./styles/'));
});


// all *.js files in ./js are minified and saved as *.min.js
gulp.task('js', function() {
  return gulp.src([ './js/*.js', '!./js/*.min.js' ])
    .pipe(uglify())
    .pipe(rename({
      suffix: '.min'
     }))
    .pipe(gulp.dest('./js'));
});

gulp.task('readme', function() {
  return gulp.src([ 'readme.txt' ])
    .pipe(readme({
      details: false,
      screenshot_ext: 'jpg',
      extract: {
        'changelog': 'CHANGELOG',
        'Frequently Asked Questions': 'FAQ'
      }
    }))
    .pipe(gulp.dest('.'));
});
