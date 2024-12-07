export const getTheme = () => {
	if (document.body.hasAttribute('data-theme-light')) {
		return 'light'
	} else if (document.body.hasAttribute('data-theme-dark')) {
		return 'dark'
	} else if (window.matchMedia('(prefers-color-scheme: light)').matches) {
		return 'light'
	} else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
		return 'dark'
	}
	return 'light'
}
