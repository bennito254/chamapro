const THEME_KEY = 'data-bs-theme';

export function getTheme(): 'light' | 'dark' {
    const stored = localStorage.getItem(THEME_KEY);
    if (stored === 'dark' || stored === 'light') {
        return stored;
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export function setTheme(theme: 'light' | 'dark'): void {
    localStorage.setItem(THEME_KEY, theme);
    document.documentElement.setAttribute('data-bs-theme', theme);
}

export function toggleTheme(): 'light' | 'dark' {
    const next = getTheme() === 'dark' ? 'light' : 'dark';
    setTheme(next);
    return next;
}

export function initializeTheme(): void {
    setTheme(getTheme());
}
