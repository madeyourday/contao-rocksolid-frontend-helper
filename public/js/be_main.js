/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function(window, document) {
document.addEventListener('DOMContentLoaded', function() {
	const theme = new URLSearchParams(document.location.search).get('rsfh-theme');
	const select = document.querySelector('#template-studio--theme-selector select[name=theme]');
	if (theme && select && select.value !== theme) {
		select.value = theme;
		setTimeout(() => select.form.requestSubmit(), 100);
		setTimeout(() => document.location.reload(), 200);
	}

	const template = new URLSearchParams(document.location.search).get('rsfh-template');
	if (template) {
		document.querySelector(`#template-studio--tree [data-name="${template.replace(/([\\"])/g, '\\$1')}"] button`)?.click();
	}
}, false);
})(window, document);
