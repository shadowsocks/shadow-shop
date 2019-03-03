module.exports = function (grunt) { //The wrapper function

    // Project configuration & task configuration
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        // compile .po files to .mo files
        po2mo: {
            files: {
                src: 'languages/*.po',
                expand: true,
            },
        },

        // create a .pot template file
        pot: {
            options:{
                text_domain: 'shadowsocks-hub', //Your text domain. Produces shadowsocks-hub.pot
                dest: 'languages/', //directory to place the pot file
                overwrite: false,
                keywords: [ //WordPress localisation functions
                  '__:1',
                  '_e:1',
                  '_x:1,2c',
                  'esc_html__:1',
                  'esc_html_e:1',
                  'esc_html_x:1,2c',
                  'esc_attr__:1', 
                  'esc_attr_e:1', 
                  'esc_attr_x:1,2c', 
                  '_ex:1,2c',
                  '_n:1,2', 
                  '_nx:1,2,4c',
                  '_n_noop:1,2',
                  '_nx_noop:1,2,3c'
                 ],
            },
            files:{
                src:  [ '**/*.php' ], //Parse all php files
                expand: true,
            }
      },
    });

    //Loading the plug-ins
    grunt.loadNpmTasks('grunt-po2mo');
    grunt.loadNpmTasks('grunt-pot');
    


    // Default task(s), executed when you run 'grunt'
    grunt.registerTask('default', ['po2mo']);
};