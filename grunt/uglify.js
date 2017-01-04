/**
 * Created by Christoph Bessei on 04.01.17.
 */
module.exports = {
    dist: {
        files: {
            'assets/dist/js/header.js': ['assets/dist/js/header.js'],
            'assets/dist/js/footer.js': ['assets/dist/js/footer.js']
        }
    },
    options: {
        report: 'min',
        mangle: false,
        screwIE8: true
    }
};