var gulp = require('gulp');
var concat = require('gulp-concat');
var requirejsOptimize = require('gulp-requirejs-optimize');
var importCss = require('gulp-import-css');
var cleanCSS = require('gulp-clean-css');
var uglify = require('gulp-uglify');
var pump = require('pump');
var rename = require("gulp-rename");

gulp.task('bundle', function () {
    return gulp.src('source/js/bundle.js')
        .pipe(requirejsOptimize({
            name: '../../vendors/almond',
            include: 'bundle',
            wrap: true,
            optimize: 'none'
        }))
        .pipe(gulp.dest('release/js'));
});

gulp.task('postbox', function () {
    return gulp.src('postbox/js/bundle.js')
        .pipe(requirejsOptimize({
            name: '../../vendors/almond',
            include: 'bundle',
            wrap: true,
            optimize: 'none'
        }))
        .pipe(rename('postbox.js'))
        .pipe(gulp.dest('release/js'));
});

gulp.task('concat', function() {
    return gulp.src([
            'source/js/loader.preinit.js',
            'vendors/lab.min.js',
            'source/js/loader.js'
        ])
        .pipe(concat('loader.js'))
        .pipe(gulp.dest('release/js'));
})

gulp.task('js', function () {
    var watcher = gulp.watch(
        ['source/js/**/*.js'],
        gulp.series(['bundle', 'concat'], function(done) {
            done();
            console.log('\x1b[36m%s\x1b[0m','\n watching files ...   \n');
        }));

    watcher.on('change', function(path) {
        console.log('\x1b[33m%s\x1b[0m','\n File ' + path + ' was changed  \n');
    });

    watcher.on('unlink', function(path) {
        console.log('\x1b[33m%s\x1b[0m','\n File ' + path + ' was removed \n');
    });
});

gulp.task('concat-css', function () {
    return gulp.src('source/css/override.css')
        .pipe(importCss())
        .pipe(cleanCSS())
        .pipe(gulp.dest('release/css'));
});

gulp.task('css', function() {
    var watcher = gulp.watch('source/css/**/*.css', gulp.series('concat-css', function(done) {
            done();
            console.log('\x1b[36m%s\x1b[0m','\n watching files ...   \n');
        }));

    watcher.on('change', function(path) {
        console.log('\x1b[33m%s\x1b[0m','\n File ' + path + ' was changed  \n');
    });

    watcher.on('unlink', function(path) {
        console.log('\x1b[33m%s\x1b[0m','\n File ' + path + ' was removed \n');
    });
})

gulp.task('uglify', function (cb) {
    pump([
        gulp.src('release/js/*.js'),
        uglify(),
        gulp.dest('release/js')
    ],
    cb
    );
})
gulp.task('build', gulp.series(['bundle', 'postbox', 'concat', 'uglify' ,'css']))