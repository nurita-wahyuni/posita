/**
 * Format number to Indonesian Rupiah format
 * @param {number} value - The number to format
 * @returns {string} Formatted string like "Rp 15.000"
 */
export function formatMoney(value) {
    if (value === null || value === undefined || isNaN(value)) return 'Rp 0';
    return 'Rp ' + new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
}

/**
 * Parse formatted money string back to number
 * @param {string} value - The formatted string like "Rp 15.000"
 * @returns {number} The numeric value
 */
export function parseMoney(value) {
    if (!value) return 0;
    // Remove "Rp " prefix and dots, then parse
    const cleaned = String(value).replace(/[Rp\s.]/g, '');
    return parseInt(cleaned, 10) || 0;
}

/**
 * Vue composable for currency formatting
 */
export function useMoneyFormatter() {
    return { formatMoney, parseMoney };
}
