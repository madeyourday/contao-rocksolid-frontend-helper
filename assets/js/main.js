/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function(window, document) {
document.addEventListener('DOMContentLoaded', function() {

	var init = function(element) {

		element.frontendGuideEnabled = true;

		var timeout, isOver;
		var data = JSON.parse(element.getAttribute('data-frontend-guide'));

		if (! data.editURL && ! data.articleURL && ! data.beModuleURL && ! data.feModuleURL && element !== document.body) {
			return;
		}

		var toolbar = document.createElement('div');
		toolbar.className = 'rsfg-toolbar' + (data.type ? ' rsfg-type-' + data.type : '');
		var overlay = document.createElement('div');
		overlay.className = 'rsfg-overlay';

		if (data.editURL) {
			var editLink = document.createElement('a');
			editLink.href = data.editURL;
			editLink.className = 'rsfg-edit';
			editLink.innerHTML = editLink.title = data.editLabel;
			toolbar.appendChild(editLink);
		}

		if (data.articleURL) {
			var articleLink = document.createElement('a');
			articleLink.href = data.articleURL;
			articleLink.className = 'rsfg-article';
			articleLink.innerHTML = articleLink.title = data.articleLabel;
			toolbar.appendChild(articleLink);
		}

		if (data.feModuleURL) {
			var feModuleLink = document.createElement('a');
			feModuleLink.href = data.feModuleURL;
			feModuleLink.className = 'rsfg-fe-module';
			feModuleLink.innerHTML = feModuleLink.title = data.feModuleLabel;
			toolbar.appendChild(feModuleLink);
		}

		if (data.beModuleURL) {
			var beModuleLink = document.createElement('a');
			beModuleLink.href = data.beModuleURL;
			beModuleLink.className = 'rsfg-be-module rsfg-be-module-' + data.beModuleType;
			beModuleLink.innerHTML = beModuleLink.title = data.beModuleLabel;
			toolbar.appendChild(beModuleLink);
		}

		var infoHtml = '<div>';
		var infoTemplates = {};
		infoTemplates[data.template] = data.templatePath;
		Array.prototype.forEach.call(element.querySelectorAll('*[data-frontend-guide]'), function(element) {
			var data = JSON.parse(element.getAttribute('data-frontend-guide'));
			infoTemplates[data.template] = data.templatePath;
		});
		for (var template in infoTemplates) {
			infoHtml += '<div><b>' + template + ':</b> ' + infoTemplates[template] + '</div>';
		}
		infoHtml += '</div>';

		var info = document.createElement('div');
		info.className = 'rsfg-info';
		info.innerHTML = infoHtml;
		toolbar.appendChild(info);

		var over = function(event, fromToolbar) {
			clearTimeout(timeout);
			if (! isOver) {
				isOver = true;
				document.body.appendChild(toolbar);
			}
			if (fromToolbar) {
				document.body.appendChild(overlay);
			}
			else {
				var boundingClientRect = element.getBoundingClientRect();
				boundingClientRect = {
					top: boundingClientRect.top,
					left: boundingClientRect.left,
					width: boundingClientRect.width,
					height: boundingClientRect.height
				};
				event.currentToolbars = event.currentToolbars || {};
				if (event.currentToolbars[boundingClientRect.top + 'x' + boundingClientRect.left]) {
					event.currentToolbars[boundingClientRect.top + 'x' + boundingClientRect.left].forEach(function(tb) {
						tb.style.left = parseFloat(tb.style.left) + toolbar.offsetWidth + 5 + 'px';
					});
				}
				else {
					event.currentToolbars[boundingClientRect.top + 'x' + boundingClientRect.left] = [];
				}
				event.currentToolbars[boundingClientRect.top + 'x' + boundingClientRect.left].push(toolbar);
				overlay.style.top = toolbar.style.top = boundingClientRect.top + window.pageYOffset + 'px';
				overlay.style.left = toolbar.style.left = boundingClientRect.left + window.pageXOffset + 'px';
				overlay.style.width = boundingClientRect.width + 'px';
				overlay.style.height = boundingClientRect.height + 'px';
			}
		};
		var out = function(event, fromToolbar) {
			if (! isOver) {
				return;
			}
			clearTimeout(timeout);
			timeout = setTimeout(function() {
				isOver = false;
				document.body.removeChild(toolbar);
			}, 300);
			if (fromToolbar) {
				document.body.removeChild(overlay);
			}
		};

		toolbar.addEventListener('mouseover', function (event) {
			over(event, true);
		}, false);
		toolbar.addEventListener('mouseout', function(event) {
			out(event, true);
		}, false);
		element.addEventListener('mouseover', function (event) {
			over(event);
		}, false);
		element.addEventListener('mouseout', function(event) {
			out(event);
		}, false);

	};

	Array.prototype.forEach.call(document.querySelectorAll('*[data-frontend-guide]'), function(element) {
		init(element);
	});

	window.addEventListener('mouseover', function(event) {
		for (var node = event.target; node && node.getAttribute; node = node.parentNode) {
			if (! node.frontendGuideEnabled && node.getAttribute('data-frontend-guide')) {
				console.debug(node);
				init(node, true);
			}
		}
	}, false);

}, false);
})(window, document);
