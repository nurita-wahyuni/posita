import { ref, computed, onMounted } from 'vue'

const THEME_KEY = 'posita-theme'

// Reactive theme state (singleton)
const currentTheme = ref('employee')

/**
 * Theme management composable using Tailwind dark mode
 * - Admin = dark class (Dark mode with Orange primary)
 * - Employee = no dark class (Light mode with Emerald primary)
 */
export function useTheme() {
    /**
     * Apply theme by toggling dark class
     * @param {'admin' | 'employee'} themeName
     */
    const applyTheme = (themeName) => {
        const html = document.documentElement

        if (themeName === 'admin') {
            html.classList.add('dark')
        } else {
            html.classList.remove('dark')
        }

        localStorage.setItem(THEME_KEY, themeName)
        currentTheme.value = themeName
    }

    /**
     * Toggle between admin and employee themes
     */
    const toggleTheme = () => {
        const newTheme = currentTheme.value === 'admin' ? 'employee' : 'admin'
        applyTheme(newTheme)
    }

    /**
     * Set specific theme
     * @param {'admin' | 'employee'} themeName
     */
    const setTheme = (themeName) => {
        if (['admin', 'employee'].includes(themeName)) {
            applyTheme(themeName)
        }
    }

    /**
     * Initialize theme from localStorage or URL path
     */
    const initTheme = () => {
        // Check localStorage first
        const stored = localStorage.getItem(THEME_KEY)

        // Auto-detect from URL path
        const path = window.location.pathname
        const isAdminPath = path.startsWith('/admin')

        // Priority: stored preference, then URL-based detection
        let theme = stored
        if (!theme) {
            theme = isAdminPath ? 'admin' : 'employee'
        }

        applyTheme(theme)
    }

    // Auto-initialize on mount
    onMounted(() => {
        initTheme()
    })

    return {
        theme: currentTheme,
        toggleTheme,
        setTheme,
        initTheme,
        isAdmin: computed(() => currentTheme.value === 'admin'),
        isEmployee: computed(() => currentTheme.value === 'employee'),
    }
}
