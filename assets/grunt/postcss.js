/**
 * Created by Christoph Bessei on 04.01.17.
 */
module.exports = {
    options: {
        map: true,
        processors: [
            require('autoprefixer')({
                browsers: ['last 5 versions']
            })
        ]
    },
    dist: {
        src: 'dist/css/*.css'
    }
};