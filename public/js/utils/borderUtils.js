/**
 * Border Utilities
 * Header class hesaplama fonksiyonu
 */

/**
 * Border class'a göre header class hesapla
 */
export function getHeaderClass(borderClass) {
    switch (borderClass) {
        case 'border-primary':
            return 'bg-primary text-white';
        case 'border-warning':
            return 'bg-warning text-dark';
        case 'border-info':
            return 'bg-info text-white';
        case 'border-success':
            return 'bg-success text-white';
        case 'border-danger':
            return 'bg-danger text-white border-danger';
        case 'border-dark':
            return 'bg-dark text-white';
        default:
            return 'bg-secondary text-white';
    }
} 