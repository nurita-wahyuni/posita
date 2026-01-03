import { ref } from 'vue'

/**
 * Composable for triggering bouncy animations on elements
 * Use this when items are added to cart or lists
 * 
 * @example
 * const { isAnimating, triggerBounce } = useBouncyAnimation()
 * 
 * <div :class="{ 'animate-bouncy': isAnimating }">
 *   <span>{{ cartCount }}</span>
 * </div>
 * 
 * const addToCart = () => {
 *   cart.push(item)
 *   triggerBounce()
 * }
 */
export function useBouncyAnimation(duration = 400) {
    const isAnimating = ref(false)
    let timeoutId = null

    const triggerBounce = () => {
        // Clear any existing animation
        if (timeoutId) {
            clearTimeout(timeoutId)
        }

        // Reset animation by briefly removing class
        isAnimating.value = false

        // Use requestAnimationFrame for smooth restart
        requestAnimationFrame(() => {
            isAnimating.value = true

            // Auto-reset after animation completes
            timeoutId = setTimeout(() => {
                isAnimating.value = false
            }, duration)
        })
    }

    return {
        isAnimating,
        triggerBounce
    }
}

/**
 * Composable for success glow animation
 * Use for confirmation feedback
 */
export function useSuccessAnimation(duration = 600) {
    const isGlowing = ref(false)
    let timeoutId = null

    const triggerGlow = () => {
        if (timeoutId) {
            clearTimeout(timeoutId)
        }

        isGlowing.value = false

        requestAnimationFrame(() => {
            isGlowing.value = true

            timeoutId = setTimeout(() => {
                isGlowing.value = false
            }, duration)
        })
    }

    return {
        isGlowing,
        triggerGlow
    }
}
