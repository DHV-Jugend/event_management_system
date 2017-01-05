/**
 * Created by Christoph Bessei on 05.01.17.
 */
module.exports = {
    'default': ['clean', 'sass:dev', 'concat', 'copy'],
    'dist': ['concurrent:target5', 'concurrent:target10', 'concurrent:target15', 'concurrent:target20'],
};