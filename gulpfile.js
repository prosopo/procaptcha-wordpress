const gulp = require('gulp');
const rename = require('gulp-rename');
const cleanCSS = require('gulp-clean-css');
const minify = require('gulp-minify');

gulp.task('minify-css', function () {
    return gulp.src(['assets/css/*.css', '!assets/css/*.min.css'])
        .pipe(cleanCSS())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('assets/css/'));
});

gulp.task('compress', function () {
    return gulp.src(['assets/js/!(*.min)*.js'])
        .pipe(minify({
            ext: {
                min: '.min.js'
            }
        }))
        .pipe(gulp.dest('assets/js/'))
});
