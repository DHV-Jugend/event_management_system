/**
 * Created by Christoph Bessei on 05.01.17.
 */
module.exports = {
    target5: ['clean'],
    target10: [['sass:dist', 'concat'],'imagemin'],
    target15: [['postcss', 'cssmin'], 'uglify'],
    target20: ['copy']
};