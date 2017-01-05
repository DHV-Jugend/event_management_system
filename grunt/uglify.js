/**
 * Created by Christoph Bessei on 04.01.17.
 */
module.exports = {
    dist: {
        files: [{
            expand: true,
            cwd: 'assets/dist/js',
            src: '**/*.js',
            dest: 'assets/dist/js'
        }]
    },
    options: {
        report: 'min',
        mangle: false,
        screwIE8: true
    }
};