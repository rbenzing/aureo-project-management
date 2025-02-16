module.exports = {
    plugins: {
        'postcss-import': {}, // Modular imports
        'postcss-nested': {}, // Nested rules
        'tailwindcss/nesting': 'postcss-nesting',
        tailwindcss: {},
        autoprefixer: {},   // Add vendor prefixes
        ...(process.env.NODE_ENV === 'production' ? { cssnano: {} } : {})
    },
};