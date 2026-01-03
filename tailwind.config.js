import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import animate from 'tailwindcss-animate';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: ['class'],
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // CSS Variable-based colors for theming
                border: 'hsl(var(--border))',
                input: 'hsl(var(--input))',
                ring: 'hsl(var(--ring))',
                background: 'hsl(var(--background))',
                foreground: 'hsl(var(--foreground))',
                primary: {
                    DEFAULT: 'hsl(var(--primary))',
                    foreground: 'hsl(var(--primary-foreground))',
                },
                secondary: {
                    DEFAULT: 'hsl(var(--secondary))',
                    foreground: 'hsl(var(--secondary-foreground))',
                },
                destructive: {
                    DEFAULT: 'hsl(var(--destructive))',
                    foreground: 'hsl(var(--destructive-foreground))',
                },
                muted: {
                    DEFAULT: 'hsl(var(--muted))',
                    foreground: 'hsl(var(--muted-foreground))',
                },
                accent: {
                    DEFAULT: 'hsl(var(--accent))',
                    foreground: 'hsl(var(--accent-foreground))',
                },
                popover: {
                    DEFAULT: 'hsl(var(--popover))',
                    foreground: 'hsl(var(--popover-foreground))',
                },
                card: {
                    DEFAULT: 'hsl(var(--card))',
                    foreground: 'hsl(var(--card-foreground))',
                },
                sidebar: {
                    DEFAULT: 'hsl(var(--sidebar))',
                    foreground: 'hsl(var(--sidebar-foreground))',
                    hover: 'hsl(var(--sidebar-hover))',
                },
            },
            borderRadius: {
                lg: 'var(--radius)',
                md: 'calc(var(--radius) - 2px)',
                sm: 'calc(var(--radius) - 4px)',
            },
            boxShadow: {
                'glass': '0 8px 32px 0 rgba(31, 38, 135, 0.15)',
                'card': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1)',
                'card-hover': '0 10px 20px -5px rgba(0, 0, 0, 0.15), 0 4px 6px -2px rgba(0, 0, 0, 0.1)',
                'nav': '0 -2px 10px rgba(0, 0, 0, 0.1)',
            },
            keyframes: {
                'accordion-down': {
                    from: { height: '0' },
                    to: { height: 'var(--radix-accordion-content-height)' },
                },
                'accordion-up': {
                    from: { height: 'var(--radix-accordion-content-height)' },
                    to: { height: '0' },
                },
                'fade-in': {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                'slide-in-left': {
                    '0%': { transform: 'translateX(-100%)' },
                    '100%': { transform: 'translateX(0)' },
                },
                'slide-in-right': {
                    '0%': { transform: 'translateX(100%)' },
                    '100%': { transform: 'translateX(0)' },
                },
                'slide-in-up': {
                    '0%': { transform: 'translateY(20px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                'slide-in-down': {
                    '0%': { transform: 'translateY(-20px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                'float': {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-10px)' },
                },
                'pulse-subtle': {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.7' },
                },
                'bounce-in': {
                    '0%': { transform: 'scale(0.9)', opacity: '0' },
                    '50%': { transform: 'scale(1.05)' },
                    '100%': { transform: 'scale(1)', opacity: '1' },
                },
                'success-pulse': {
                    '0%': { boxShadow: '0 0 0 0 rgba(16, 185, 129, 0.4)' },
                    '70%': { boxShadow: '0 0 0 10px rgba(16, 185, 129, 0)' },
                    '100%': { boxShadow: '0 0 0 0 rgba(16, 185, 129, 0)' },
                },
                // Bouncy animation for cart item add
                'bouncy': {
                    '0%': { transform: 'scale(1)' },
                    '25%': { transform: 'scale(1.2)' },
                    '50%': { transform: 'scale(0.9)' },
                    '75%': { transform: 'scale(1.05)' },
                    '100%': { transform: 'scale(1)' },
                },
                // Success glow for confirmations
                'success-glow': {
                    '0%': {
                        boxShadow: '0 0 0 0 rgba(16, 185, 129, 0.6)',
                        transform: 'scale(1)'
                    },
                    '50%': {
                        boxShadow: '0 0 20px 10px rgba(16, 185, 129, 0)',
                        transform: 'scale(1.05)'
                    },
                    '100%': {
                        boxShadow: '0 0 0 0 rgba(16, 185, 129, 0)',
                        transform: 'scale(1)'
                    },
                },
            },
            animation: {
                'accordion-down': 'accordion-down 0.2s ease-out',
                'accordion-up': 'accordion-up 0.2s ease-out',
                'fade-in': 'fade-in 0.2s ease-out',
                'slide-in-left': 'slide-in-left 0.3s ease-out',
                'slide-in-right': 'slide-in-right 0.3s ease-out',
                'slide-in-up': 'slide-in-up 0.3s ease-out',
                'slide-in-down': 'slide-in-down 0.3s ease-out',
                'float': 'float 3s ease-in-out infinite',
                'pulse-subtle': 'pulse-subtle 2s ease-in-out infinite',
                'bounce-in': 'bounce-in 0.4s ease-out',
                'success-pulse': 'success-pulse 1s ease-out',
                'bouncy': 'bouncy 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55)',
                'success-glow': 'success-glow 0.6s ease-out',
            },
        },
    },

    plugins: [forms, animate],
};
