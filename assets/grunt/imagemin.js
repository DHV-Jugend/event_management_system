/**
 * Created by Christoph Bessei on 05.01.17.
 */
module.exports = {
    dynamic: {
        files: [{
            expand: true,
            cwd: 'src/img/',
            src: ['*.{jpg,jpeg,png,gif}'],
            dest: 'src/optimizedImg/'
        }]
    }
};