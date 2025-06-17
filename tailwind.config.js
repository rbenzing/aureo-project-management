
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
            // Override container to remove max-width restrictions
            container: {
                center: false,
                padding: '0',
            },
            // Add custom spacing for full-width layouts
            spacing: {
                'content': '1.5rem', // 24px - standard content padding
                'content-sm': '1rem', // 16px - smaller content padding
                'content-lg': '2rem', // 32px - larger content padding
            },
        },
    },
    plugins: [
        require('@tailwindcss/typography'), // Add the Typography plugin
        require('@tailwindcss/aspect-ratio'), // Add the Aspect Ratio plugin
    ],
};