var gulp = require('gulp');
var sass = require('gulp-sass');
var concat = require('gulp-concat');
var sort = require('gulp-sort');
var uglify = require('gulp-uglify');
var minify = require('gulp-clean-css');
var sourcemaps = require('gulp-sourcemaps');
var rev = require('gulp-rev');

var paths = {
	assets: {
		base: './resources',
		styles: './resources/styles',
		scripts: './resources/scripts'
	},
	build: {
		base: './resources/build',
		styles: './resources/build/styles',
		scripts: './resources/build/scripts'
	}
};

gulp.task('scss', function () {
	return gulp.src(paths.assets.styles + '/**/*.scss')
		.pipe(sass().on('error', sass.logError))
		.pipe(gulp.dest(paths.build.styles));
});

gulp.task('css', function () {
	return gulp.src(paths.assets.styles + '/**/*.css')
		.pipe(gulp.dest(paths.build.styles));
});

gulp.task('styles', ['scss', 'css'], function () {
	return gulp.src(paths.build.styles + '/*.css')
		.pipe(sort())
		.pipe(concat('styles.css'))
		.pipe(minify())
		.pipe(rev())
		.pipe(gulp.dest(paths.build.base))
		.pipe(rev.manifest(paths.build.base + '/rev-manifest.json', {
			merge: true,
			base: paths.build.base
		}))
		.pipe(gulp.dest(paths.build.base));
});

gulp.task('js', function () {
	return gulp.src(paths.assets.scripts + '/**/*.js')
		.pipe(gulp.dest(paths.build.scripts));
});

gulp.task('scripts', ['js'], function () {
	return gulp.src(paths.build.scripts + '/*.js')
		.pipe(sourcemaps.init())
		.pipe(sort())
		.pipe(concat('scripts.js'))
		.pipe(uglify())
		.pipe(rev())
		.pipe(sourcemaps.write('.'))
		.pipe(gulp.dest(paths.build.base))
		.pipe(rev.manifest(paths.build.base + '/rev-manifest.json', {
			merge: true,
			base: paths.build.base
		}))
		.pipe(gulp.dest(paths.build.base));
});

gulp.task('watch',function () {
	gulp.watch(paths.assets.styles + '/**/*.*', [
		'scss',
		'css',
		'styles',
	]);
	gulp.watch(paths.assets.scripts + '/**/*.*', [
		'js',
		'scripts'
	]);
});

gulp.task('default', [
	'scss',
	'css',
	'styles',
	'js',
	'scripts'
]);