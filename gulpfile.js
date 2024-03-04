let gulp           = require('gulp');
let	sass           = require('gulp-sass')(require('sass'));
let	concat         = require('gulp-concat');
let	uglify         = require('gulp-uglify');
let	cleanCSS       = require('gulp-clean-css');
let	rename         = require('gulp-rename');
let	autoprefixer   = require('gulp-autoprefixer');
let	notify         = require("gulp-notify");




gulp.task('js', function() {
	return gulp.src([
    'assets/js/backend-scripts.js', 
		])
	.pipe(concat('backend-scripts.min.js'))
	.pipe(uglify())
	.pipe(gulp.dest('assets/js'));
});

gulp.task('sass', function() {
	return gulp.src('assets/scss/**/*.scss')
	.pipe(sass({outputStyle: 'expanded'}).on("error", notify.onError()))
	.pipe(rename({suffix: '.min', prefix : ''}))
	.pipe(autoprefixer(['last 15 versions']))
	.pipe(cleanCSS()) 
	.pipe(gulp.dest('assets/css'));
});


gulp.task('watch', function() {
	gulp.watch('assets/scss/**/*.scss', gulp.parallel('sass'));
	gulp.watch('assets/js/backend-scripts.js', gulp.parallel('js'));
});



gulp.task('default', gulp.parallel('sass', 'js', 'watch'));