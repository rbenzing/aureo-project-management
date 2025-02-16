
/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './src/**/*.php',
        './src/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                primary: '#4F46E5', // Example: Custom primary color
                secondary: '#EC4899', // Example: Custom secondary color
            },
        },
    },
    plugins: [
        require('@tailwindcss/typography'), // Add the Typography plugin
        require('@tailwindcss/aspect-ratio'), // Add the Aspect Ratio plugin
    ],
};