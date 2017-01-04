module.exports = function (grunt) {
    "use strict";

    grunt.registerTask('default', ['clean', 'sass:dev', 'concat', 'copy']);
    grunt.registerTask('dist', ['clean', 'sass:dist', 'concat', 'uglify', 'postcss', 'cssmin', 'copy']);
    
    require('load-grunt-config')(grunt);

};