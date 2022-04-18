// https://github.com/taver/gulp-starter-knacss

'use strict';

// Chargement et initialisation des composants utilisés
let gulp = require('gulp'),
    $ = require('gulp-load-plugins')(),
    pump = require('pump');
let uglify = require('gulp-uglify-es').default;

// Configuration générale du projet et des composants utilisés
let project = {
  	name: 'projectName', // nom du projet
  	globalJSFile: 'global.min.js', // nom du fichier JS après concaténation
  	plugins: { // activation ou désactivation de certains plugins à la carte
    	babel: false // utilisation de Babel pour JavaScript
  	},
  	configuration: { // configuration des différents composants de ce projet
	    cssbeautify: {
	    	indent: '  ',
	    },
	    imagemin: {
	      	svgoPlugins: [
  	      	{
  	          	removeViewBox: false,
  	        },
  	        {
  	          	cleanupIDs: false,
  	        },
	      	],
	    }
  	}
};

// Chemins vers les ressources ciblées
let paths = {
  	root: './', // dossier actuel
  	dev: './dev/', // dossier de travail
  	dest: './dist/', // dossier destiné à la livraison
  	vendors: './node_modules/', // dossier des dépendances du projet
  	styles: {
    	root: '/css/', // fichier contenant les fichiers CSS & Sass
    	css: {
      		mainFile: '/css/styles.css', // fichier CSS principal
      		files: '/css/*.css', // cible tous les fichiers CSS
    	},
    	sass: {
      		mainFile: '/sass/global.scss', // fichier Sass principal
      		files: '/sass/{,includes/}*.scss', // fichiers Sass à surveiller
      		root: '/sass/',
    	},
  	},
  	scripts: {
    	root: '/js/', // dossier contenant les fichiers JavaScript
      no_min: '/js/no-minified/', // dossier contenant les fichiers JavaScript
    	external: '/js/external/*.js', // fichiers JavaScript (hors vendor)
      custom: '/js/custom/*.js', // fichiers JavaScript Custom
  	},
  	images: '/img/*.{png,jpg,jpeg,gif,svg}', // fichiers images à compresser
  	maps: '/maps/', // fichiers provenant de sourcemaps
};

// Copie des fichiers Hamburgers
gulp.task('copy-hamburgers', function () {
	return gulp.src(paths.vendors + 'hamburgers/_sass/**/*')
		.pipe(gulp.dest(paths.dev + paths.styles.sass.root))
		.pipe($.notify({ 
				message : 'Fichiers Hamburgers copiés avec succès !',
				onLast: true,
			})
		);
});

// Copie des fichiers Bootstrap 4
gulp.task('copy-bootstrap', function () {
  	return gulp.src(paths.vendors + '/bootstrap/scss/**/*')
    	.pipe(gulp.dest(paths.dev + paths.styles.sass.root + '/bootstrap/'))
    	.pipe($.notify({ 
       			message : 'Fichiers Boostrap copiés avec succès !',
        		onLast: true,
      		})
    	);
});

// Ressources JavaScript utilisées par ce projet (vendors + scripts JS spécifiques /dev/js/**/*.js)
let vendors = [
    paths.vendors + 'core-js/client/core.min.js',
    paths.vendors + 'jquery/dist/jquery.js',
    paths.vendors + 'popper.js/dist/umd/popper.min.js',
    paths.vendors + 'tether/dist/tether.js',
    paths.vendors + 'bootstrap/dist/js/bootstrap.min.js',
    paths.vendors + 'toastr/build/toastr.min.js',
    paths.vendors + 'bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js',
    paths.vendors + 'bootstrap-datepicker/dist/locales/bootstrap-datepicker.fr.min.js',
    paths.vendors + 'sweetalert2/dist/sweetalert2.js',
    paths.vendors + 'bootstrap-tagsinput/dist/bootstrap-tagsinput.js',
    paths.vendors + 'dropzone/dist/min/dropzone.min.js',
    paths.vendors + 'lodash/lodash.min.js',
    // paths.vendors + 'list.js/dist/list.min.js',
    //paths.vendors + 'jquery-validation/dist/jquery.validate.min.js',
    //paths.vendors + 'jquery-validation/dist/additional-methods.js',
    //paths.vendors + 'jquery-validation/dist/localization/messages_fr.js',
    paths.dev + paths.scripts.external,
    paths.dev + paths.scripts.custom,
];

// Tâche CSS : Sass + Autoprefixer + CSScomb + beautify + minify (si prod)
gulp.task('css', function () {
  	return gulp.src(paths.dev + paths.styles.sass.mainFile)
	    .pipe($.plumber({ errorHandler: $.notify.onError("Erreur: <%= error.message %>") }))
	    .pipe($.sourcemaps.init())
	    .pipe($.sass())
	    // .pipe($.csscomb())
	    // .pipe($.cssbeautify(project.configuration.cssbeautify))
	    .pipe($.autoprefixer({ grid: true }))
	    .pipe(gulp.dest(paths.dev + paths.styles.root))
	    .pipe($.rename({ suffix: '.min' }))
	    .pipe($.csso({ comments: false }))
	    .pipe($.sourcemaps.write(paths.maps))
	    .pipe(gulp.dest(paths.dest + paths.styles.root));
});

gulp.task('css-prod', function () {
    return gulp.src(paths.dev + paths.styles.sass.mainFile)
      .pipe($.plumber({ errorHandler: $.notify.onError("Erreur: <%= error.message %>") }))
      .pipe($.sourcemaps.init())
      .pipe($.sass())
      .pipe($.csscomb())
      .pipe($.cssbeautify(project.configuration.cssbeautify))
      .pipe($.autoprefixer({ grid: true }))
      .pipe(gulp.dest(paths.dev + paths.styles.root))
      .pipe($.rename({ suffix: '.min' }))
      .pipe($.csso({ comments: false }))
      .pipe($.sourcemaps.write(paths.maps))
      .pipe(gulp.dest(paths.dest + paths.styles.root));
});


// Tâche JS : copie des fichiers JS et vendor + babel (+ concat et uglify si prod)
gulp.task('js', function (callback) {
  pump([
      gulp.src(vendors),
      $.concat(project.globalJSFile),
      gulp.dest(paths.dev + paths.scripts.no_min),
    //  $.uglify(),
      gulp.dest(paths.dest + paths.scripts.root)
    ],
    callback
  );
});

gulp.task('js-prod', function (callback) {
  pump([
      gulp.src(vendors),
      $.concat(project.globalJSFile),
      gulp.dest(paths.dev + paths.scripts.no_min),
      uglify(),
      gulp.dest(paths.dest + paths.scripts.root)
    ],
    callback
  );
});


// Tâche IMG : optimisation des images
gulp.task('img', function () {
  	return gulp.src(paths.src + paths.images)
    	.pipe($.plumber({ errorHandler: $.notify.onError("Erreur: <%= error.message %>") }))
		  .pipe($.changed(paths.dest + paths.images))
		  .pipe($.imagemin(project.configuration.imagemin))
		  .pipe(gulp.dest(paths.dest + paths.images));
});

// Tâches principales : récapitulatif

	// Tâche BUILD : tapez "gulp" ou "gulp build"
	gulp.task('build', ['css', 'js', 'img']);

	// Tâche Copy File : tapez "gulp copy"
	gulp.task('copy', ['copy-bootstrap', 'copy-hamburgers']);

	// Tâche par défaut
	gulp.task('default', ['build']);

	// Watcher par défaut
	gulp.task('watch', function() {
		gulp.watch('dev/sass/**/*.scss', ['css']);
		gulp.watch('dev/css/**/*.css', ['css']);
		gulp.watch('dev/js/**/*.js', ['js']);
	});

	// Watcher CSS
	gulp.task('watch-css', function() {
		gulp.watch('dev/sass/**/*.scss', ['css']);
		gulp.watch('dev/css/**/*.css', ['css']);
	});

	// Watcher JS
	gulp.task('watch-js', function() {
		gulp.watch('dev/js/**/*.js', ['js']);
	});