const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const uglify = require('gulp-uglify');
const concat = require('gulp-concat');
const cleanCSS = require('gulp-clean-css');
const imagemin = require('gulp-imagemin');
const sourcemaps = require('gulp-sourcemaps');
const browserSync = require('browser-sync').create();
const autoprefixer = require('gulp-autoprefixer');
const rename = require('gulp-rename');
const del = require('del');
const plumber = require('gulp-plumber');
const notify = require('gulp-notify');

// Rutas de archivos
const paths = {
  styles: {
    src: 'src/scss/**/*.scss',
    dest: 'assets/css/'
  },
  scripts: {
    src: 'src/js/**/*.js',
    dest: 'assets/js/'
  },
  images: {
    src: 'src/images/**/*',
    dest: 'assets/images/'
  },
  php: {
    src: '**/*.php'
  }
};

// Configuración de error handling
const errorHandler = {
  errorHandler: notify.onError({
    title: 'Gulp Error',
    message: 'Error: <%= error.message %>'
  })
};

// Limpiar archivos de distribución
function clean() {
  return del(['assets/css/*', 'assets/js/*', 'assets/images/*']);
}

// Compilar Sass para desarrollo
function styles() {
  return gulp.src(paths.styles.src)
    .pipe(plumber(errorHandler))
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'expanded',
      includePaths: ['node_modules']
    }))
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.styles.dest))
    .pipe(browserSync.stream());
}

// Compilar SCSS específico del plugin de ubicaciones
function ubicacionesStyles() {
  return gulp.src('src/scss/main.scss')
    .pipe(plumber(errorHandler))
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'expanded',
      includePaths: ['node_modules']
    }))
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(rename('ubicaciones.css'))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.styles.dest))
    .pipe(browserSync.stream());
}

// Tarea específica para modal de contacto
function modalStyles() {
  return gulp.src('src/scss/components/modal-contacto.scss')
    .pipe(plumber(errorHandler))
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'expanded',
      includePaths: ['node_modules']
    }))
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.styles.dest))
    .pipe(browserSync.stream());
}

// Compilar Sass para producción (comprimido)
function stylesProd() {
  return gulp.src(paths.styles.src)
    .pipe(plumber(errorHandler))
    .pipe(sass({
      outputStyle: 'compressed',
      includePaths: ['node_modules']
    }))
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(paths.styles.dest));
}

// Compilar SCSS para producción
function ubicacionesStylesProd() {
  return gulp.src('src/scss/main.scss')
    .pipe(plumber(errorHandler))
    .pipe(sass({
      outputStyle: 'compressed',
      includePaths: ['node_modules']
    }))
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(rename('ubicaciones.min.css'))
    .pipe(gulp.dest(paths.styles.dest));
}

// Modal para producción
function modalStylesProd() {
  return gulp.src('src/scss/components/modal-contacto.scss')
    .pipe(plumber(errorHandler))
    .pipe(sass({
      outputStyle: 'compressed',
      includePaths: ['node_modules']
    }))
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(rename('modal-contacto.min.css'))
    .pipe(gulp.dest(paths.styles.dest));
}

// Procesar JavaScript para desarrollo
function scripts() {
  return gulp.src(paths.scripts.src)
    .pipe(plumber(errorHandler))
    .pipe(sourcemaps.init())
    .pipe(concat('main.js'))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.scripts.dest))
    .pipe(browserSync.stream());
}

// Procesar JavaScript para producción (minificado)
function scriptsProd() {
  return gulp.src(paths.scripts.src)
    .pipe(plumber(errorHandler))
    .pipe(concat('main.min.js'))
    .pipe(uglify({
      compress: {
        drop_console: true,
        drop_debugger: true
      }
    }))
    .pipe(gulp.dest(paths.scripts.dest));
}

// Minificar CSS existente
function minifyCSS() {
  return gulp.src([paths.styles.dest + '*.css', '!' + paths.styles.dest + '*.min.css'])
    .pipe(cleanCSS({
      level: 2
    }))
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(paths.styles.dest));
}

// Minificar JavaScript existente
function minifyJS() {
  return gulp.src([paths.scripts.dest + '*.js', '!' + paths.scripts.dest + '*.min.js'])
    .pipe(uglify({
      compress: {
        drop_console: true,
        drop_debugger: true
      }
    }))
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(paths.scripts.dest));
}

// Optimizar imágenes
function images() {
  return gulp.src(paths.images.src)
    .pipe(imagemin([
      imagemin.gifsicle({ interlaced: true }),
      imagemin.mozjpeg({ quality: 80, progressive: true }),
      imagemin.optipng({ optimizationLevel: 5 }),
      imagemin.svgo({
        plugins: [
          { removeViewBox: true },
          { cleanupIDs: false }
        ]
      })
    ]))
    .pipe(gulp.dest(paths.images.dest));
}

// BrowserSync para desarrollo local
function serve() {
  browserSync.init({
    proxy: 'http://localhost:10043',
    notify: false,
    open: false
  });

  // Watchers actualizados
  gulp.watch('src/scss/**/*.scss', gulp.parallel(ubicacionesStyles, modalStyles));
  gulp.watch(paths.scripts.src, scripts);
  gulp.watch(paths.images.src, images);
  gulp.watch(paths.php.src).on('change', browserSync.reload);
}

// Watchers sin BrowserSync
function watch() {
  gulp.watch('src/scss/**/*.scss', gulp.parallel(ubicacionesStyles, modalStyles));
  gulp.watch(paths.scripts.src, scripts);
  gulp.watch(paths.images.src, images);
}

// Tareas de desarrollo y producción
const dev = gulp.series(clean, gulp.parallel(styles, scripts, images));
const build = gulp.series(clean, gulp.parallel(stylesProd, scriptsProd, images));
const buildWithMinify = gulp.series(dev, gulp.parallel(minifyCSS, minifyJS));

// Tareas específicas para ubicaciones
const devUbicaciones = gulp.series(clean, gulp.parallel(ubicacionesStyles, modalStyles, scripts, images));
const buildUbicaciones = gulp.series(clean, gulp.parallel(ubicacionesStylesProd, modalStylesProd, scriptsProd, images));

// Exportar tareas
exports.clean = clean;
exports.styles = styles;
exports.ubicacionesStyles = ubicacionesStyles;
exports.modalStyles = modalStyles;
exports.scripts = scripts;
exports.images = images;
exports.minifyCSS = minifyCSS;
exports.minifyJS = minifyJS;
exports.minify = gulp.parallel(minifyCSS, minifyJS);
exports.watch = watch;
exports.serve = serve;
exports.dev = dev;
exports.devUbicaciones = devUbicaciones;
exports.build = build;
exports.buildUbicaciones = buildUbicaciones;
exports.prod = buildWithMinify;
exports.default = devUbicaciones;