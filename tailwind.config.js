/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './src/**/*.php',       // Scan PHP files for Tailwind classes
        './src/**/*.html',      // Include HTML files if used
        './src/**/*.js',        // Include JavaScript files if used
    ],
    theme: {
        extend: {
            colors: {
                primary: '#4F46E5', // Example: Custom primary color
                secondary: '#EC4899', // Example: Custom secondary color
            },
        },
    },
    plugins: [],
};