/* global require */

var gulp   = require('gulp');
var less   = require('gulp-less');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var readme = require('gulp-readme-to-markdown');
var del    = require('del');
var path   = require('path');
var merge  = require('merge-stream');


// Return the foldername of a given file path.
function getSubdir(file) {
  var dir = path.dirname(path.normalize(file));
  return ('.' === dir) ? '' : dir;
}


// Run less, js and readme task and watch them for changes afterwards.
gulp.task('default', ['less', 'js', 'readme'], function() {
  gulp.watch(['./js/*.js', '!./js/*.min.js'], ['js']);
  gulp.watch('./styles/*.less', ['less']);
  gulp.watch('./readme.txt', ['readme']);
});


// Compile and minify all *.less files in ./styles as *.css
gulp.task('less', function () {
  return gulp.src(['./styles/*.less', '!**/_*.less'])
    .pipe(less({
      cleancss: true
    }))
    .pipe(gulp.dest('./styles/'));
});


// Minify all *.js files in ./js as *.min.js
gulp.task('js', function() {
  return gulp.src([ './js/*.js', '!./js/*.min.js' ])
    .pipe(uglify())
    .pipe(rename({
      suffix: '.min'
     }))
    .pipe(gulp.dest('./js'));
});


// Generate README.md etc from the WordPress readme.txt
gulp.task('readme', function() {
  return gulp.src([ 'readme.txt' ])
    .pipe(readme({
      details: false,
      screenshot_ext: 'jpg',
      extract: {
        'Changelog': 'CHANGELOG',
        'Frequently Asked Questions': 'FAQ',
        'Screenshots': null
      }
    }))
    .pipe(gulp.dest('.'));
});


// Copy everything needed for a SVN release to the /build directory.
gulp.task('build', ['js', 'less', 'readme'], function() {
  function copy() {
    var sources = [
      'img/*',
      'js/*',
      'lng/*',
      'php/*',
      'styles/*.css',
      '*.php',
      '*.txt',
      '*.md'
    ];

    var streams = [], stream;
    sources.forEach(function(source) {
      var dir = getSubdir(source);

      stream = gulp.src(source).pipe(gulp.dest('./build/' + dir));
      streams.push(stream);
    });

    return merge(streams);
  }

  return del('./build', copy);
});
