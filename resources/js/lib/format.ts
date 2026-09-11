export function formatMoney(minorUnits: number, currency = 'USD'): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency,
    }).format(minorUnits / 100);
}

export function formatDate(value: string | null | undefined): string {
    if (!value) return '—';

    return new Date(value).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
