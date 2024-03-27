const gulp = require('gulp');
const rename = require('gulp-rename');
const cleanCSS = require('gulp-clean-css');

gulp.task('minify-css', function(){
    return gulp.src(['assets/css/*.css', '!assets/css/*.min.css'])
        .pipe(cleanCSS())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('assets/css/'));
});
