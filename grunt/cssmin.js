/**
 * Created by Christoph Bessei on 04.01.17.
 */
module.exports = {
    dist: {
        options: {
            shorthandCompacting: true,
            keepSpecialComments: 0
        },
        files: [{
            expand: true,
            cwd: 'assets/dist/css',
            src: ['*.css'],
            dest: 'assets/dist/css',
            ext: '.css'
        }]
    }
};