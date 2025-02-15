module.exports = {
    plugins: [
        require('postcss-import'), // Modular imports
        require('postcss-nested'), // Nested rules
        require('autoprefixer'),   // Add vendor prefixes
        ...(process.env.NODE_ENV === 'production'
            ? [require('cssnano')({ preset: 'default' })] // Minify CSS in production
            : []),
    ],
};