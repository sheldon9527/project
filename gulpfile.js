var gulp = require("gulp");
var elixir = require('laravel-elixir');
var replace = require('gulp-replace');
var less = require('gulp-less');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    // 复制assets目录中的内容到public
    mix.copy('resources/assets/fonts', 'public/fonts');
    mix.copy('resources/assets/images', 'public/images');
    mix.copy('resources/assets/css', 'public/css');
    mix.copy('resources/assets/js', 'public/js');
    mix.copy('resources/assets/swf', 'public/swf');
    // 执行替换js变量任务
    mix.task('replaceJS');
    // 编译less
    mix.task('compileLESS');
});

// 替换js中的变量
gulp.task('replaceJS', function() {
    // 返回一个数据流，表示任务结束
    return gulp.src(['public/js/config.js'])
        .pipe(replace(/\.\.\/\.\.\/assets/g, ''))
        .pipe(gulp.dest('public/js/'));
});

// 替换less中的变量
gulp.task('replaceLESS', function() {
    // 返回一个数据流，表示任务结束
    return gulp.src(['public/css/less/var.less'])
        .pipe(replace(/\/assets/g, ''))
        .pipe(gulp.dest('public/css/less/'));
});

// 编译less文件
gulp.task('compileLESS', ['replaceLESS'], function(){
    return gulp.src('./public/css/less/app.less')
            .pipe(less())
            .pipe(gulp.dest('./public/css'));
});
