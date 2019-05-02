module.exports = function(grunt) {
	'use strict';

	var sass = require( 'node-sass' );

	require('load-grunt-tasks')(grunt);

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		// SASS to CSS
		sass: {
			options: {
				implementation: sass,
				sourcemap: 'none'
			},
			dist: {
				files: {
					'assets/css/admin/<%= pkg.name %>.css' : 'assets/scss/admin.scss'
				}
			}
		},

		// Post CSS
		postcss: {
			options: {
				//map: false,
				processors: [
					require('autoprefixer')({
						browsers: [
							'> 0.1%',
							'ie 8',
							'ie 9'
						]
					})
				]
			},
			dist: {
				src: [
					'assets/css/admin/*.css'
				]
			}
		},

		// Minify CSS
		cssmin: {
			target: {
				files: [{
					expand: true,
					cwd: 'assets/css/admin',
					src: [
						'*.css',
						'!*.min.css'
					],
					dest: 'assets/css/admin',
					ext: '.min.css'
				}]
			}
		},

		// Minify JavaScript
		uglify: {
			options: {
				compress: {
					global_defs: {
						"EO_SCRIPT_DEBUG": false
					},
					dead_code: true
				},
				banner: '/*! <%= pkg.title %> v<%= pkg.version %> <%= grunt.template.today("dddd dS mmmm yyyy HH:MM:ss TT Z") %> */'
			},
			build: {
				files: [{
					expand: true, // Enable dynamic expansion.
					src: [
						// Admin
						'assets/js/admin/*.js',
						'!assets/js/admin/*.min.js',

						// Customizer
						'assets/js/customizer/*.js',
						'!assets/js/customizer/*.min.js',

						// Frontend
						'assets/js/frontend/*.js',
						'!assets/js/frontend/*.min.js',
						'!assets/js/frontend/*.dev.js',
					],
					ext: '.min.js', // Dest filepaths will have this extension.
				}]
			}
		},

		// Watch for changes made in SASS or JavaScript.
		watch: {
			css: {
				files: [
					'assets/scss/*.scss',
					'assets/scss/admin/*.scss',
				],
				tasks: ['sass', 'postcss']
			},
			js: {
				files: [
					// Admin
					'assets/js/admin/*.js',
					'!assets/js/admin/*.min.js',

					// Customizer
					'assets/js/customizer/*.js',
					'!assets/js/frontend/*.min.js',

					// Frontend
					'assets/js/frontend/*.js',
					'!assets/js/frontend/*.min.js',
				],
				tasks: [
					'jshint',
					'uglify'
				]
			}
		},

		// Check for Javascript errors with "grunt-contrib-jshint"
		// Reports provided by "jshint-stylish"
		jshint: {
			options: {
				reporter: require('jshint-stylish'),
				globals: {
					"EO_SCRIPT_DEBUG": false,
				},
				'-W099': true, // Mixed spaces and tabs
				'-W083': true, // Fix functions within loop
				'-W082': true, // Declarations should not be placed in blocks
				'-W020': true, // Read only - error when assigning EO_SCRIPT_DEBUG a value.
			},
			all: [
				// Admin
				'assets/js/admin/*.js',
				'!assets/js/admin/*.min.js',

				// Customizer
				'assets/js/customizer/*.js',
				'!assets/js/customizer/*.min.js',

				// Frontend
				'assets/js/frontend/*.js',
				'!assets/js/frontend/*.min.js',
				'assets/js/frontend/*.dev.js'
			]
		},

		// Check for Sass errors with "stylelint"
		stylelint: {
			options: {
				configFile: '.stylelintrc'
			},
			all: [
				'assets/scss/**/*.scss',
			]
		},

		// Generate .pot file
		makepot: {
			target: {
				options: {
					cwd: '',
					domainPath: 'languages',                                  // Where to save the POT file.
					exclude: [
						'releases',
						'node_modules',
						'vendor'
					],
					mainFile: '<%= pkg.name %>.php',                          // Main project file.
					potComments: '# Copyright (c) {{year}} Sébastien Dumont', // The copyright at the beginning of the POT file.
					potFilename: '<%= pkg.name %>.pot',                       // Name of the POT file.
					potHeaders: {
						'poedit': true,                                       // Includes common Poedit headers.
						'x-poedit-keywordslist': true,                        // Include a list of all possible gettext functions.
						'Report-Msgid-Bugs-To': 'https://github.com/autoloadnextpost/auto-load-next-post/issues',
						'language-team': 'Sébastien Dumont <mailme@sebastiendumont.com>',
						'language': 'en_US'
					},
					type: 'wp-plugin',                                        // Type of project.
					updateTimestamp: true,                                    // Whether the POT-Creation-Date should be updated without other changes.
				}
			}
		},

		// Check strings for localization issues
		checktextdomain: {
			options:{
				text_domain: '<%= pkg.name %>', // Project text domain.
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src:  [
					'*.php',
					'**/*.php', // Include all files
					'!node_modules/**', // Exclude node_modules/
					'!vendor/**', // Exclude vendor files
				],
				expand: true
			},
		},

		potomo: {
			dist: {
				options: {
					poDel: false
				},
				files: [{
					expand: true,
					cwd: 'languages',
					src: ['*.po'],
					dest: 'languages',
					ext: '.mo',
					nonull: false
				}]
			}
		},

		// Bump version numbers (replace with version in package.json)
		replace: {
			Version: {
				src: [
					'readme.txt',
					'<%= pkg.name %>.php'
				],
				overwrite: true,
				replacements: [
					{
						from: /Stable tag:.*$/m,
						to: "Stable tag: <%= pkg.version %>"
					},
					{
						from: /Version:.*$/m,
						to: "Version:     <%= pkg.version %>"
					},
					{
						from: /public static \$version = \'.*.'/m,
						to: "public static $version = '<%= pkg.version %>'"
					}
				]
			}
		},

		// Copies the plugin to create deployable plugin.
		copy: {
			deploy: {
				src: [
					'**',
					'!.*',
					'!.*/**',
					'!.htaccess',
					'!Gruntfile.js',
					'!releases/**',
					'!auto-load-next-post-git/**',
					'!auto-load-next-post-svn/**',
					'!node_modules/**',
					'!.DS_Store',
					'!npm-debug.log',
					'!assets/sass/**',
					'!assets/**/*.scss',
					'!*.scss',
					'!*.json',
					'!*.md',
					'!*.sh',
					'!*.zip',
					'!*.jpg',
					'!*.jpeg',
					'!*.gif',
					'!*.png'
				],
				dest: '<%= pkg.name %>',
				expand: true,
				dot: true
			}
		},

		// Compresses the deployable plugin folder.
		compress: {
			zip: {
				options: {
					archive: './releases/<%= pkg.name %>-v<%= pkg.version %>.zip',
					mode: 'zip'
				},
				files: [
					{
						expand: true,
						cwd: './<%= pkg.name %>/',
						src: '**',
						dest: '<%= pkg.name %>'
					}
				]
			}
		},

		// Deletes the deployable plugin folder once zipped up.
		clean: [ '<%= pkg.name %>' ]
	});

	// Set the default grunt command to run test cases.
	grunt.registerTask( 'default', [ 'test' ] );

	// Checks for errors with the javascript, sass and for any text domain issues.
	grunt.registerTask( 'test', [ 'jshint', 'stylelint', 'checktextdomain' ]);

	// Updates version, minify css and javascript and finaly runs i18n tasks.
	grunt.registerTask( 'dev', [ 'replace', 'sass', 'postcss', 'cssmin', 'uglify', 'makepot' ]);

	/**
	 * Run i18n related tasks.
	 *
	 * This includes extracting translatable strings, updating the master pot file.
	 * If this is part of a deploy process, it should come before zipping everything up.
	 */
	grunt.registerTask( 'update-pot', [ 'checktextdomain', 'makepot' ]);

	/**
	 * Creates a deployable plugin zipped up ready to upload
	 * and install on a WordPress installation.
	 */
	grunt.registerTask( 'zip', [ 'copy', 'compress', 'clean' ]);
};
