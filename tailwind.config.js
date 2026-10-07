import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import plugin from 'tailwindcss/plugin';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],

    theme: {
        asideScrollbars: {
            light: "light",
            gray: "gray",
            slate: "slate",
        },
        extend: {
            boxShadow: {
                sm: "0 1px 2px 0 rgba(0, 0, 0, 0.05)",
                DEFAULT:
                    "0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)",
                md: "0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)",
                lg: "0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)",
                xl: "0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)",
                t: "0 -1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)",
                orange: "0px 20px 20px -15px rgba(245,56,56,0.81) ",
                "orange-md": "0px 20px 40px -15px rgba(245,56,56,0.81) ",
                none: "none",
            },
            colors: {
                black: {
                    500: "#4F5665",
                    600: "#0B132A",
                    DEFAULT: "#000000",
                },
                orange: {
                    100: "#FFECEC",
                    500: "#F53855",
                },
                green: {
                    500: "#2FAB73",
                },
                white: {
                    300: "#F8F8F8",
                    500: "#fff",
                    DEFAULT: "#ffffff",
                },
                "gray-ls": {
                    100: "#EEEFF2",
                    400: "#AFB5C0",
                    500: "#DDDDDD",
                },
            },
            fontFamily: {
                sans: ['Rubik', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            zIndex: {
                '-1': '-1',
            },
            flexGrow: {
                5: '5',
            },
            maxHeight: {
                'screen-menu': 'calc(100vh - 3.5rem)',
                modal: 'calc(100vh - 160px)',
            },
            transitionProperty: {
                position: 'right, left, top, bottom, margin, padding',
                textColor: 'color',
            },
            keyframes: {
                'fade-out': {
                    from: { opacity: 1 },
                    to: { opacity: 0 },
                },
                'fade-in': {
                    from: { opacity: 0 },
                    to: { opacity: 1 },
                },
            },
            animation: {
                'fade-out': 'fade-out 250ms ease-in-out',
                'fade-in': 'fade-in 250ms ease-in-out',
            },
        },
    },

    plugins: [
        forms,
        plugin(function ({ matchUtilities, theme }) {
            matchUtilities(
                {
                    "aside-scrollbars": (value) => {
                        const track = value === "light" ? "100" : "900";
                        const thumb = value === "light" ? "300" : "600";
                        const color = value === "light" ? "gray" : value;

                        return {
                            scrollbarWidth: "thin",
                            scrollbarColor: `${theme(`colors.${color}.${thumb}`)} ${theme(
                                `colors.${color}.${track}`
                            )}`,
                            "&::-webkit-scrollbar": {
                                width: "8px",
                                height: "8px",
                            },
                            "&::-webkit-scrollbar-track": {
                                backgroundColor: theme(`colors.${color}.${track}`),
                            },
                            "&::-webkit-scrollbar-thumb": {
                                borderRadius: "0.25rem",
                                backgroundColor: theme(`colors.${color}.${thumb}`),
                            },
                        };
                    },
                },
                { values: theme("asideScrollbars") }
            );
        }),
    ],
};
