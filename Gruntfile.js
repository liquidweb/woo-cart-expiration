module.exports = function( grunt ) {

  'use strict';

  // Project configuration
  grunt.initConfig( {

    pkg: grunt.file.readJSON( 'package.json' ),

    // Process the textdomain.
    addtextdomain: {
      options: {
        textdomain: 'woo-cart-expiration',
      },
      update_all_domains: {
        options: {
          updateDomains: true
        },
        src: [ '*.php', '**/*.php', '!\.git/**/*', '!bin/**/*', '!node_modules/**/*', '!tests/**/*' ]
      }
    },

    wp_readme_to_markdown: {
      your_target: {
        files: {
          'README.md': 'readme.txt'
        }
      },
    },

    // Make our POT file.
    makepot: {
      target: {
        options: {
          domainPath: '/languages',
          exclude: [ '\.git/*', 'bin/*', 'node_modules/*', 'tests/*' ],
          mainFile: 'woo-cart-expiration.php',
          potFilename: 'woo-cart-expiration.pot',
          potHeaders: {
            poedit: true,
            'x-poedit-keywordslist': true
          },
          type: 'wp-plugin',
          updateTimestamp: true
        }
      }
    },

    // Set the required items.
    required: {
      libs: {
        options: {
          // npm install missing modules
          install: true
        },
        // Search for require() in all js files in the assets folder
        src: ['assets/*.js']
      }
    },

    // Uglify JS
    uglify: {
      options: {
        mangle: true,
        compress: {
          drop_console: true
        }
      },
      all: {
        files: [{
          expand: true,
          cwd: 'assets/js/',
          src: ['*.js', '!*.min.js', '!assets/js/ext/*.js'],
          dest: 'assets/js/',
          ext: '.min.js'
        }]
      }
    },

    // Compile Sass
    sass: {
      dist: {
        options: {
        style: 'nested',
        unixNewlines: true
      },
        expand: true,
        cwd: 'assets/scss',
        src: ['*.scss'],
        dest: 'assets/css/',
        ext: '.css'
      },
      dev: {
        options: {
        style: 'nested',
        lineNumbers: true,
        unixNewlines: true
      },
        expand: true,
        cwd: 'assets/scss',
        src: ['*.scss'],
        dest: 'assets/css/',
        ext: '.css'
      },
    },

    // Minify CSS
    cssmin: {
      minify: {
        expand: true,
        cwd: 'assets/css',
        src: ['*.css', '!*.min.css'],
        dest: 'assets/css/',
        ext: '.min.css'
      }
    },

    // Watch for changes during development
    watch: {
      options: {
        livereload: true
      },
      styles: {
        files: ['assets/scss/*.scss','assets/scss/**/*.scss'],
        tasks: ['sass:dist','cssmin']
      },
      scripts: {
        files: ['assets/js/*.js', '!assets/js/*.min.js', '!assets/js/ext/*.js'],
        tasks: ['uglify']
      },
    }

  } );

  grunt.loadNpmTasks( 'grunt-wp-i18n' );
  grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );
  grunt.loadNpmTasks( 'grunt-required' );
  grunt.loadNpmTasks( 'grunt-check-modules' );
  grunt.loadNpmTasks( 'grunt-contrib-copy' );
  grunt.loadNpmTasks( 'grunt-contrib-compress' );
  grunt.loadNpmTasks( 'grunt-contrib-clean' );
  grunt.loadNpmTasks( 'grunt-contrib-uglify' );
  grunt.loadNpmTasks( 'grunt-contrib-sass' );
  grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
  grunt.loadNpmTasks( 'grunt-contrib-watch' );
  grunt.loadNpmTasks( 'grunt-replace' );

  grunt.registerTask( 'default', ['i18n', 'readme', 'check-modules'] );
  grunt.registerTask( 'i18n', ['addtextdomain', 'makepot'] );
  grunt.registerTask( 'readme', ['wp_readme_to_markdown'] );

  // Compile SASS and minify assets
  grunt.registerTask( 'pre-commit', ['sass:dist', 'cssmin', 'uglify:all'] );

  grunt.util.linefeed = '\n';

};
