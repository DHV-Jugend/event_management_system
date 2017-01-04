/**
 * Created by Christoph Bessei on 04.01.17.
 */
module.exports = {
    dev: {
        options: {
            style: 'nested'
        },
        files: [{
            expand: true,
            src: ['assets/src/sass/main.scss'],
            dest: 'assets/dist/css',
            flatten: true,
            ext: '.css'
        }]
    },
    dist: {
        options: {sourcemap: 'none'},
        files: [{
            expand: true,
            src: ['assets/src/sass/main.scss'],
            dest: 'assets/dist/css',
            flatten: true,
            ext: '.css'
        }]
    }
}