/* eslint-env node */
module.exports = function(grunt) {

	'use strict';
	var adminDirCSS = './admin/css/',
		adminDirJS = './admin/js/',
		publicDirCSS = './public/css/',
		publicDirJS = './public/js/',

		csssrc = [{
			expand: true,
			cwd: adminDirCSS,
			dest: adminDirCSS,
			src: [
				'*.css',
				'!*.min.css',
				'!*-rtl.css'
			],
			ext: '.min.css'
		}, {
			expand: true,
			cwd: publicDirCSS,
			dest: publicDirCSS,
			src: [
				'*.css',
				'!*.min.css',
				'!*-rtl.css',
			],
			ext: '.min.css'
		}],

		csssrcRTL = [{
			expand: true,
			cwd: adminDirCSS,
			dest: adminDirCSS,
			src: [
				'*.css',
				'*.min.css',
				'!*-rtl.css'
			],
			ext: '.min-rtl.css'
		}, {
			expand: true,
			cwd: publicDirCSS,
			dest: publicDirCSS,
			src: [
				'*.css',
				'*.min.css',
				'!*-rtl.css'
			],
			ext: '.min-rtl.css'
		}],


		jssrc = [{
			expand: true,
			cwd: adminDirJS,
			dest: adminDirJS,
			src: [
				'*.js',
				'!*.min.js'
			],
			ext: '.min.js'
		}, {
			expand: true,
			cwd: publicDirJS,
			dest: publicDirJS,
			src: [
				'*.js',
				'!*.min.js'
			],
			ext: '.min.js'
		}];

	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		// VVV (Varying Vagrant Vagrants) Paths.
		vvv: {
			'plugin': '/srv/www/wordpress-default/wp-content/plugins/<%= pkg.name %>'
		},

		// Shell actions.
		shell: {
			readme: {
				command: 'cd ./dev-lib && ./generate-markdown-readme' // Generate the readme.md
			},
			phpunit: {
				command: 'vagrant ssh -c "cd <%= vvv.plugin %> && phpunit"'
			}
		},

		// Run tasks whenever watched files change.
		watch: {
			scripts: {
				files: [
					adminDirJS + '*.js',
					publicDirJS + '*.js',
					'!' + adminDirJS + '*.min.js',
					'!' + publicDirJS + '*.min.js'
				],
				tasks: ['scripts:dev'],
				options: {
					interrupt: true,
				},
			},
			styles: {
				files: [
					adminDirCSS + '*.css',
					publicDirCSS + '*.css',
					'!' + adminDirCSS + '*.min.css',
					'!' + adminDirCSS + '*.min-rtl.css',
					'!' + publicDirCSS + '*.min.css',
					'!' + publicDirCSS + '*.min-rtl.css'
				],
				tasks: ['styles:dev'],
				options: {
					interrupt: true,
				},
			},
			readme: {
				files: ['readme.txt'],
				tasks: ['readme'],
				options: {
					interrupt: true,
				},
			}
		},

		// JavaScript linting with ESLint.
		eslint: {
			options: {
				fix: true
			},
			target: [
				adminDirJS + '*.js',
				publicDirJS + '*.js'
			]
		},

		// Minify .js files.
		uglify: {
			dev: {
				options: {
					preserveComments: false,
					sourceMap: true
				},
				files: jssrc
			},
			build: {
				options: {
					preserveComments: false
				},
				files: jssrc
			}
		},

		// Minify .css files.
		cssmin: {
			dev: {
				options: {
					sourceMap: true
				},
				files: csssrc
			},
			rtl: {
				files: csssrcRTL
			},
			build: {
				files: csssrc
			}
		},

		// Transforming CSS LTR to RTL.
		rtlcss: {
			options: {
				map: false,
				saveUnmodified: false
			},
			reg: {
				files: [{
					expand: true,
					cwd: adminDirCSS,
					dest: adminDirCSS,
					ext: '-rtl.css',
					src: [
						'*.css',
						'!*-rtl.css',
						'!*.min.css'
					]
				}, {
					expand: true,
					cwd: publicDirCSS,
					dest: publicDirCSS,
					ext: '-rtl.css',
					src: [
						'*.css',
						'!*-rtl.css',
						'!*.min.css'
					]
				}]
			}
		},

		// Check textdomain errors.
		checktextdomain: {
			options: {
				text_domain: '<%= pkg.name %>',
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
				src: [
					'*.php', // Include all files
					'**/*.php', // Include all files
					'!includes/bb-metabox/**', // Exclude sub-modules/
					'!includes/bb-metabox-extend/**', // Exclude sub-modules/
					'!includes/wp-settings/**', // Exclude sub-modules/
					'!<%= pkg.name %>/**', // Exclude build/
					'!build/**', // Exclude build/
					'!node_modules/**', // Exclude node_modules/
					'!tests/**' // Exclude tests/
				],
				expand: true
			}
		},

		// Build a deploy-able plugin.
		copy: {
			build: {
				src: [
					'*.php',
					'admin/**',
					'public/**',
					'widgets/**',
					'includes/**',
					'languages/**',
					'readme.txt',
					'!**/*.map',
					'!**/changelog.md',
					'!**/readme.md',
					'!**/README.md',
					'!**/contributing.md'
				],
				dest: './build/',
				expand: true,
				dot: false
			}
		},

		// Compress files and folders.
		compress: {
			build: {
				options: {
					archive: '<%= pkg.name %>.<%= pkg.version %>.zip'
				},
				files: [{
					expand: true,
					cwd: './build/',
					src: ['**'],

					// When the .zip file is uncompressed (e.g. 'ninecodes-social-media').
					dest: './<%= pkg.name %>/'
				}, ]
			},
		},

		// Clean files and folders.
		clean: {
			build: ['./build/'],
			zip: ['./<%= pkg.name %>*.zip']
		},

		// Deploys a build directory to the WordPress SVN repo.
		wp_deploy: {
			deploy: {
				options: {
					plugin_slug: '<%= pkg.name %>',
					build_dir: 'build',
					assets_dir: 'wp-assets'
				}
			}
		}
	});

	// Load tasks
	grunt.loadNpmTasks('grunt-shell');
	grunt.loadNpmTasks('grunt-checktextdomain');
	grunt.loadNpmTasks('grunt-eslint');
	grunt.loadNpmTasks('grunt-rtlcss');
	grunt.loadNpmTasks('grunt-wp-deploy');

	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-watch');

	// Register task to compile "readme.txt" to "readme.md"
	grunt.registerTask('readme', [
		'shell:readme'
	]);

	// Register Grunt task to run PHPUnit in VVV.
	grunt.registerTask('phpunit', [
		'shell:phpunit'
	]);

	// Register WordPress specific tasks.
	grunt.registerTask('wordpress', [
		'readme',
		'checktextdomain',
		'phpunit'
	]);

	// Register stylesheet specific tasks in "development" stage.
	grunt.registerTask('styles:dev', [
		'rtlcss',
		'cssmin:dev',
		'cssmin:rtl'
	]);

	// Register stylesheet specific tasks for "build".
	grunt.registerTask('styles:build', [
		'rtlcss',
		'cssmin:build',
		'cssmin:rtl'
	]);

	// Register JavaScript specific tasks for "development" stage.
	grunt.registerTask('scripts:dev', [
		'eslint',
		'uglify:dev'
	]);

	// Register JavaScript specific tasks for "build" stage.
	grunt.registerTask('scripts:build', [
		'eslint',
		'uglify:build'
	]);

	// Register grunt default tasks.
	grunt.registerTask('default', [
		'wordpress',
		'styles:dev',
		'scripts:dev',
		'watch'
	]);

	// Build the plugin.
	grunt.registerTask('build', [
		'clean:zip',
		'wordpress',
		'styles:build',
		'scripts:build',
		'copy:build'
	]);

	// Build and package the plugin.
	grunt.registerTask('package', [
		'build',
		'compress:build',
		'clean:build'
	]);

	// Deploy plugin to WordPress.org repository.
	grunt.registerTask('deploy', [
		'build',
		'wp_deploy',
		'clean:build'
	]);
};
