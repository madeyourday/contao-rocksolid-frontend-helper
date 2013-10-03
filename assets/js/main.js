/*
 * Copyright MADE/YOUR/DAY OG <mail@madeyourday.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function(window, document) {
document.addEventListener('DOMContentLoaded', function() {

	var setCookie = function(key, value){
		if (value === null) {
			document.cookie = key+'=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT';
		}
		else {
			document.cookie = key+'='+encodeURIComponent(value ? value : '')+'; path=/';
		}
	};
	var getCookie = function(key){
		var value = document.cookie.match('(?:^|;)\\s*' + key + '=([^;]*)');
		return (value) ? decodeURIComponent(value[1]) : null;
	};
	var getBoundingClientRect = function(element) {
		var boundingClientRect = element.getBoundingClientRect();
		if (element === document.body) {
			return {
				top: window.pageYOffset * -1,
				left: window.pageXOffset * -1,
				width: boundingClientRect.width,
				height: boundingClientRect.height
			};
		}
		var computedStyle = window.getComputedStyle(element);
		if (
			computedStyle.borderTopWidth !== '0px' ||
			computedStyle.borderRightWidth !== '0px' ||
			computedStyle.borderBottomWidth !== '0px' ||
			computedStyle.borderLeftWidth !== '0px' ||
			(
				computedStyle.backgroundColor !== 'transparent' &&
				computedStyle.backgroundColor !== 'rgba(0, 0, 0, 0)'
			) ||
			(
				'boxShadow' in computedStyle &&
				computedStyle.boxShadow !== 'none'
			) ||
			computedStyle.backgroundImage !== 'none' ||
			computedStyle.overflow !== 'visible'
		) {
			// the element has a visible box, so the bounding box can be used
			return {
				top: Math.max(0, boundingClientRect.top),
				left: Math.max(0, boundingClientRect.left),
				width: boundingClientRect.width + Math.min(0, boundingClientRect.left),
				height: boundingClientRect.height + Math.min(0, boundingClientRect.top)
			};
		}
		var tops = [];
		var rights = [];
		var bottoms = [];
		var lefts = [];
		var currentRect;
		var child;
		var wrapElement;
		for (var i = 0; i < element.childNodes.length; i++) {
			child = element.childNodes[i];
			if (child.nodeType === Node.ELEMENT_NODE) {
				currentRect = getBoundingClientRect(child);
			}
			else if (
				child.nodeType === Node.TEXT_NODE &&
				('' + child.nodeValue).trim()
			) {
				wrapElement = document.createElement('span');
				element.insertBefore(wrapElement, child);
				wrapElement.appendChild(child);
				currentRect = wrapElement.getBoundingClientRect();
				element.insertBefore(child, wrapElement);
				element.removeChild(wrapElement);
			}
			else {
				continue;
			}
			if (currentRect.width > 1 && currentRect.height > 1) {
				tops.push(currentRect.top);
				rights.push(currentRect.left + currentRect.width);
				bottoms.push(currentRect.top + currentRect.height);
				lefts.push(currentRect.left);
			}
		}
		if (tops.length) {
			boundingClientRect = {
				top: Math.max(0, Math.min.apply(null, tops)),
				left: Math.max(0, Math.min.apply(null, lefts))
			};
			boundingClientRect.width = Math.max.apply(null, rights) - boundingClientRect.left;
			boundingClientRect.height = Math.max.apply(null, bottoms) - boundingClientRect.top;
		}
		return boundingClientRect;
	};

	var active = !!getCookie('rsfh-active');

	var init = function(element) {

		element.frontendHelperEnabled = true;

		var timeout, isOver;
		var data = JSON.parse(element.getAttribute('data-frontend-helper'));

		if (! data.toolbar || (
			! data.editURL &&
			! data.articleURL &&
			! data.beModuleURL &&
			! data.feModuleURL &&
			! data.template &&
			! data.activateLabel
		)) {
			return;
		}

		var toolbar = document.createElement('div');
		toolbar.className = 'rsfh-toolbar' + (data.type ? ' rsfh-type-' + data.type : '');
		var overlay = document.createElement('div');
		overlay.className = 'rsfh-overlay';

		if (data.pageURL) {
			var pageLink = document.createElement('a');
			pageLink.href = data.pageURL;
			pageLink.target = '_top';
			pageLink.className = 'rsfh-page';
			pageLink.innerHTML = pageLink.title = data.pageLabel;
			toolbar.appendChild(pageLink);
		}

		if (data.layoutURL) {
			var layoutLink = document.createElement('a');
			layoutLink.href = data.layoutURL;
			layoutLink.target = '_top';
			layoutLink.className = 'rsfh-layout';
			layoutLink.innerHTML = layoutLink.title = data.layoutLabel;
			toolbar.appendChild(layoutLink);
		}

		if (data.editURL) {
			var editLink = document.createElement('a');
			editLink.href = data.editURL;
			editLink.target = '_top';
			editLink.className = 'rsfh-edit';
			editLink.innerHTML = editLink.title = data.editLabel;
			toolbar.appendChild(editLink);
		}

		if (data.articleURL) {
			var articleLink = document.createElement('a');
			articleLink.href = data.articleURL;
			articleLink.target = '_top';
			articleLink.className = 'rsfh-article';
			articleLink.innerHTML = articleLink.title = data.articleLabel;
			toolbar.appendChild(articleLink);
		}

		if (data.feModuleURL) {
			var feModuleLink = document.createElement('a');
			feModuleLink.href = data.feModuleURL;
			feModuleLink.target = '_top';
			feModuleLink.className = 'rsfh-fe-module';
			feModuleLink.innerHTML = feModuleLink.title = data.feModuleLabel;
			toolbar.appendChild(feModuleLink);
		}

		if (data.beModuleURL) {
			var beModuleLink = document.createElement('a');
			beModuleLink.href = data.beModuleURL;
			beModuleLink.target = '_top';
			beModuleLink.className = 'rsfh-be-module';
			if (data.beModuleIcon) {
				beModuleLink.style.backgroundImage = 'url("' + data.beModuleIcon + '")';
			}
			beModuleLink.innerHTML = beModuleLink.title = data.beModuleLabel;
			toolbar.appendChild(beModuleLink);
		}

		if (data.template) {

			var infoHtml = '<div>';
			var infoTemplates = {};
			infoTemplates[data.template] = data.templatePath;
			Array.prototype.forEach.call(element.querySelectorAll('*[data-frontend-helper]'), function(element) {
				var data = JSON.parse(element.getAttribute('data-frontend-helper'));
				infoTemplates[data.template] = data.templatePath;
			});
			if (data.column) {
				infoHtml += '<div><b>' +
					data.columnLabel.split('&').join('&amp;').split('<').join('&lt;') + ':</b> ' +
					data.column.split('&').join('&amp;').split('<').join('&lt;') + '</div>';
			}
			for (var template in infoTemplates) {
				infoHtml += '<div><b>' + template + ':</b> ' + infoTemplates[template] + '</div>';
			}
			infoHtml += '</div>';

			var info = document.createElement('div');
			info.className = 'rsfh-info';
			info.innerHTML = infoHtml;
			toolbar.appendChild(info);

		}

		if (element === document.body) {

			var previewLink = document.createElement('a');
			previewLink.href = document.location.href;
			previewLink.className = 'rsfh-preview';
			if (getCookie('FE_PREVIEW')) {
				previewLink.className += ' rsfh-preview-active';
			}
			previewLink.innerHTML = previewLink.title = getCookie('FE_PREVIEW') ?
				data.previewHideLabel :
				data.previewShowLabel;
			previewLink.addEventListener('click', function () {
				setCookie('FE_PREVIEW', getCookie('FE_PREVIEW') ? null : '1');
			}, false);
			toolbar.appendChild(previewLink);

			var activateLink = document.createElement('a');
			activateLink.href = document.location.href;
			activateLink.className = 'rsfh-activate';
			if (getCookie('rsfh-active')) {
				activateLink.className += ' rsfh-activate-active';
			}
			activateLink.innerHTML = activateLink.title = active ?
				data.deactivateLabel :
				data.activateLabel;
			activateLink.addEventListener('click', function (event) {
				setCookie('rsfh-active', active ? null : '1');
				active = !active;
				this.innerHTML = this.title = active ?
					data.deactivateLabel :
					data.activateLabel;
				if (active) {
					activateLink.className += ' rsfh-activate-active';
				}
				else {
					activateLink.className = activateLink.className.split('rsfh-activate-active').join('');
				}
				event.preventDefault();
			}, false);
			toolbar.appendChild(activateLink);

		}

		var over = function(event, fromToolbar) {
			clearTimeout(timeout);
			if (! active && element !== document.body) {
				return;
			}
			if (! isOver) {
				isOver = true;
				document.body.appendChild(toolbar);
			}
			var boundingClientRect = getBoundingClientRect(element);
			overlay.style.top = boundingClientRect.top + window.pageYOffset + 'px';
			overlay.style.left = boundingClientRect.left + window.pageXOffset + 'px';
			overlay.style.width = boundingClientRect.width + 'px';
			overlay.style.height = boundingClientRect.height + 'px';
			if (fromToolbar && element !== document.body) {
				document.body.appendChild(overlay);
			}
			else {
				event.currentToolbars = event.currentToolbars || [];
				event.currentToolbars.reverse();
				event.currentToolbars.push(toolbar);
				event.currentToolbars.reverse();
				event.currentToolbars.forEach(function(toolbar1, index1) {
					var bounding1 = toolbar1.getBoundingClientRect();
					event.currentToolbars.forEach(function(toolbar2, index2) {
						var bounding2 = toolbar2.getBoundingClientRect();
						if (
							index2 > index1 &&
							bounding2.left < bounding1.right &&
							bounding2.right > bounding1.left &&
							bounding2.top < bounding1.bottom &&
							bounding2.bottom > bounding1.top
						) {
							toolbar2.style.left = bounding1.right + 5 + 'px';
							toolbar2.className += ' rsfh-toolbar-minor';
						}
					});
				});
				toolbar.className = toolbar.className.split('rsfh-toolbar-minor').join('');
				toolbar.style.top = boundingClientRect.top + window.pageYOffset + 'px';
				toolbar.style.left = boundingClientRect.left + window.pageXOffset + 'px';
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
			}, 400);
			if (fromToolbar) {
				if (overlay.parentNode === document.body) {
					document.body.removeChild(overlay);
				}
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

	Array.prototype.forEach.call(document.querySelectorAll('*[data-frontend-helper]'), function(element) {
		init(element);
	});

	window.addEventListener('mouseover', function(event) {
		for (var node = event.target; node && node.getAttribute; node = node.parentNode) {
			if (! node.frontendHelperEnabled && node.getAttribute('data-frontend-helper')) {
				init(node, true);
			}
		}
	}, false);

}, false);
})(window, document);
