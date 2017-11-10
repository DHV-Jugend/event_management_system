/**
 * Created by Christoph Bessei on 04.01.17.
 */
module.exports = {
    dist: {
        files: [
            {
                expand: true,
                cwd: 'src/',
                src: ['fonts/**'],
                dest: 'dist/'
            },
            {
                expand: true,
                cwd: 'src/optimizedImg/',
                src: ['**'],
                dest: 'dist/img/'
            }
        ]
    }
};