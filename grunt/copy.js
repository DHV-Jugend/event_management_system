/**
 * Created by Christoph Bessei on 04.01.17.
 */
module.exports = {
    dist: {
        files: [
            {
                expand: true,
                cwd: 'assets/src/',
                src: ['fonts/**', 'img/**'],
                dest: 'assets/dist/'
            }
        ]
    }
};