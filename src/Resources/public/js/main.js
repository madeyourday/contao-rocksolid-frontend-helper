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
	var addEvent = function(element, events, func){
		events = events.split(' ');
		for (var i = 0; i < events.length; i++) {
			if (element.addEventListener) {
				element.addEventListener(events[i], func, false);
			}
			else {
				element.attachEvent('on'+events[i], func);
			}
		}
	};
	var addClass = function(element, className) {
		if (!hasClass(element, className)) {
			if (element.classList && element.classList.add) {
				element.classList.add(className);
			}
			else {
				element.className += ' ' + className;
			}
		}
	};
	var removeClass = function(element, className) {
		if (hasClass(element, className)) {
			if (element.classList && element.classList.remove) {
				element.classList.remove(className);
			}
			else {
				element.className = element.className.replace(new RegExp('(?:^|\\s+)' + className + '(?:$|\\s+)'), ' ');
			}
		}
	};
	var hasClass = function(element, className) {
		if (element.classList && element.classList.contains) {
			return element.classList.contains(className);
		}
		else {
			return !!element.className.match('(?:^|\\s)' + className + '(?:$|\\s)');
		}
	};
	var getBoundingClientRect = function(element) {
		var boundingClientRect = element.getBoundingClientRect();
		var nodeName = element.nodeName.toLowerCase();
		if (element === document.body) {
			return {
				top: window.pageYOffset * -1,
				left: window.pageXOffset * -1,
				width: boundingClientRect.width,
				height: boundingClientRect.height
			};
		}
		if (nodeName === 'style' || nodeName === 'script') {
			return {
				top: boundingClientRect.top,
				left: boundingClientRect.left,
				width: 0,
				height: 0
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
				top: boundingClientRect.top,
				left: boundingClientRect.left,
				width: boundingClientRect.width,
				height: boundingClientRect.height
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
				// the element has a text content, so the bounding box can be used
				return {
					top: boundingClientRect.top,
					left: boundingClientRect.left,
					width: boundingClientRect.width,
					height: boundingClientRect.height
				};
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
				top: Math.min.apply(null, tops),
				left: Math.min.apply(null, lefts)
			};
			boundingClientRect.width = Math.max.apply(null, rights) - boundingClientRect.left;
			boundingClientRect.height = Math.max.apply(null, bottoms) - boundingClientRect.top;
		}
		return boundingClientRect;
	};
	var getNodeDepth = function(element) {
		var depth = 0;
		while (element.parentNode) {
			depth++;
			element = element.parentNode;
		}
		return depth;
	};
	var getNodeData = function(element) {
		var data;
		try {
			data = JSON.parse(element.getAttribute('data-frontend-helper'));
		}
		catch(e) {}
		return (typeof data === 'object' && data) || {};
	};
	var getLabel = function(key) {
		var data = getNodeData(document.body);
		if (!data || !data.labels || !data.labels[key]) {
			return;
		}
		return data.labels[key];
	};
	var postToIframe = function(url, data, callback) {

		var iframe = document.createElement('iframe');
		var form = document.createElement('form');
		form.method = 'post';
		form.action = url;
		form.target = iframe.name = 'iframe_post_target_'+(new Date());

		for (var key in data) {
			if (data.hasOwnProperty(key)) {
				var hiddenField = document.createElement('input');
				hiddenField.type = 'hidden';
				hiddenField.name = key;
				hiddenField.value = data[key];
				form.appendChild(hiddenField);
			}
		}

		document.body.appendChild(iframe);
		document.body.appendChild(form);
		form.submit();

		iframe.addEventListener('load', function (event) {
			callback && callback(event);
			document.body.removeChild(iframe);
			document.body.removeChild(form);
		});

	}

	var active = !!getCookie('rsfh-active');
	var lightbox;
	var lightboxScrollPosition;
	var config = {};

	var init = function(element) {

		element.frontendHelperEnabled = true;

		var timeout, timeout2, isOver, boundingClientRect;
		var data = getNodeData(element);

		if (! data.toolbar || (
			! data.links &&
			! data.template &&
			! data.labels &&
			! data.config
		)) {
			return;
		}

		var toolbar = document.createElement('div');
		toolbar.className = 'rsfh-toolbar' + (data.type ? ' rsfh-type-' + data.type : '');
		toolbar.linkedElement = element;
		var overlay = document.createElement('div');
		overlay.className = 'rsfh-overlay';
		addEvent(toolbar, 'click', function (event) {

			for (
				var targetLink, currentTarget = event.target;
				currentTarget.parentNode;
				currentTarget = currentTarget.parentNode
			) {
				if (currentTarget.nodeName.toLowerCase() === 'a') {
					targetLink = currentTarget;
					break;
				}
			}

			if (!targetLink) {
				return;
			}

			if (targetLink.getAttribute('data-confirm')) {
				if (!window.confirm(targetLink.getAttribute('data-confirm'))) {
					event.preventDefault();
					return;
				}
			}

			if (
				!config.lightbox
				|| hasClass(event.target, 'rsfh-activate')
				|| hasClass(event.target, 'rsfh-preview')
			) {
				return;
			}

			// Disable lightbox if users try to open links in a new tab
			if (event.ctrlKey || event.shiftKey || event.metaKey || event.which === 2) {
				return;
			}

			lightboxScrollPosition = Math.round(window.pageYOffset || document.documentElement.scrollTop) || 0;

			document.documentElement.style.marginTop = -lightboxScrollPosition + 'px';
			document.documentElement.style.height = (window.innerHeight || document.documentElement.clientHeight) + 'px';
			document.documentElement.style.overflow = 'hidden';
			window.scrollTo(0, 0);

			lightbox = lightbox || document.createElement('div');
			lightbox.className = 'rsfh-lightbox';

			var firstLoadEvent = true;
			var iframe = document.createElement('iframe');
			iframe.id = iframe.name = 'rsfh-lightbox-iframe';
			addEvent(iframe, 'load', function(event) {
				if (firstLoadEvent) {
					firstLoadEvent = false;
					return;
				}
				if (iframe.contentWindow.location.href === 'about:blank') {
					closeLightbox(true);
				}
			});
			lightbox.appendChild(iframe);

			var lightboxCloseButton = document.createElement('a');
			lightboxCloseButton.className = 'rsfh-lightbox-close';
			lightboxCloseButton.innerHTML = 'X';
			lightboxCloseButton.href = '';
			addEvent(lightboxCloseButton, 'click', function(event) {
				closeLightbox();
				event.preventDefault();
			});
			lightbox.appendChild(lightboxCloseButton);

			var lightboxCancelButton = document.createElement('a');
			lightboxCancelButton.className = 'rsfh-lightbox-cancel';
			lightboxCancelButton.innerHTML = getLabel('cancel');
			lightboxCancelButton.href = '';
			addEvent(lightboxCancelButton, 'click', function(event) {
				closeLightbox(true);
				event.preventDefault();
			});
			lightbox.appendChild(lightboxCancelButton);

			document.body.appendChild(lightbox);
			targetLink.target = 'rsfh-lightbox-iframe';

		});

		var mainNav, mainNavContents;
		if (element === document.body) {
			addClass(toolbar, 'rsfh-main-toolbar');
			mainNav = document.createElement('div');
			mainNav.className = 'rsfh-main-nav';
			toolbar.appendChild(mainNav);
			mainNavContents = document.createElement('div');
			mainNav.appendChild(mainNavContents);
			config = data.config || {};
		}

		var key, link;
		data.links = data.links || {};
		for (key in data.links) {
			if (!data.links.hasOwnProperty(key)) {
				continue;
			}
			link = document.createElement('a');
			link.href = data.links[key].url;
			link.target = '_top';
			link.className = 'rsfh-' + key;
			link.innerHTML = link.title = data.links[key].label;
			if (data.links[key].icon) {
				link.style.backgroundImage = 'url("' + data.links[key].icon + '")';
			}
			if (data.links[key].confirm) {
				link.setAttribute('data-confirm', data.links[key].confirm);
			}
			if (element === document.body) {
				mainNavContents.appendChild(link);
			}
			else {
				toolbar.appendChild(link);
			}
		}

		if (data.template) {

			var infoHtml = '<div>';
			var infoTemplates = {};
			infoTemplates[data.template] = {
				path: data.templatePath,
				url: data.templateURL,
				label: data.templateLabel
			};
			Array.prototype.forEach.call(element.querySelectorAll('*[data-frontend-helper]'), function(element) {
				var data = getNodeData(element);
				if (data.template) {
					infoTemplates[data.template] = {
						path: data.templatePath,
						url: data.templateURL,
						label: data.templateLabel
					};
				}
			});
			if (data.column) {
				infoHtml += '<div class="rsfh-info-column"><b>' +
					data.columnLabel.split('&').join('&amp;').split('<').join('&lt;') + ':</b> ' +
					data.column.split('&').join('&amp;').split('<').join('&lt;') + '</div>';
			}
			infoHtml += '<div class="rsfh-templates-label">Templates:</div>';
			for (var template in infoTemplates) {
				infoHtml += '<div><b>' + template + ':</b> ';
				if (infoTemplates[template].url) {
					infoHtml += '<a href="' + infoTemplates[template].url + '" title="' + infoTemplates[template].label.split('"').join('&quot;') + '">';
				}
				infoHtml += infoTemplates[template].path;
				if (infoTemplates[template].url) {
					infoHtml += '</a>';
				}
				infoHtml += '</div>';
			}
			infoHtml += '</div>';

			var info = document.createElement('div');
			info.className = 'rsfh-info';
			info.innerHTML = infoHtml;
			toolbar.appendChild(info);

		}

		if (element === document.body) {

			var activateLink = document.createElement('a');
			activateLink.href = document.location.href;
			activateLink.className = 'rsfh-activate';
			if (getCookie('rsfh-active')) {
				addClass(activateLink, 'rsfh-activate-active');
			}
			activateLink.innerHTML = activateLink.title = active ?
				data.labels.deactivate :
				data.labels.activate;
			addEvent(activateLink, 'click', function (event) {
				setCookie('rsfh-active', active ? null : '1');
				active = !active;
				this.innerHTML = this.title = active ?
					data.labels.deactivate :
					data.labels.activate;
				if (active) {
					addClass(activateLink, 'rsfh-activate-active');
				}
				else {
					removeClass(activateLink, 'rsfh-activate-active');
				}
				event.preventDefault();
			});
			toolbar.appendChild(activateLink);

			var previewLink = document.createElement('a');
			previewLink.href = document.location.href;
			previewLink.className = 'rsfh-preview';
			if (data.config.beSwitch.data.unpublished === 'hide') {
				addClass(previewLink, 'rsfh-preview-active');
			}
			previewLink.innerHTML = previewLink.title = data.config.beSwitch.label;
			addEvent(previewLink, 'click', function (event) {
				postToIframe(data.config.beSwitch.url, data.config.beSwitch.data, function() {
					document.location.reload();
				});
				event && event.preventDefault && event.preventDefault();
			});
			mainNavContents.insertBefore(
				previewLink,
				(mainNavContents.querySelector('.rsfh-article') && mainNavContents.querySelector('.rsfh-article').nextSibling) ||
				(mainNavContents.querySelector('.rsfh-page') && mainNavContents.querySelector('.rsfh-page').nextSibling) ||
				mainNavContents.childNodes[0]
			);

			mainNavContents.insertBefore(
				document.createElement('hr'),
				previewLink.nextSibling
			);
			if (
				mainNavContents.querySelector('.rsfh-backend') &&
				mainNavContents.querySelector('.rsfh-backend').previousSibling.nodeName.toLowerCase() !== 'hr'
			) {
				mainNavContents.insertBefore(
					document.createElement('hr'),
					mainNavContents.querySelector('.rsfh-backend')
				);
			}

		}

		var over = function(event, fromToolbar) {
			clearTimeout(timeout);
			timeout = null;
			if (fromToolbar) {
				clearTimeout(timeout2);
				timeout2 = null;
			}
			if (! active && element !== document.body) {
				return;
			}
			if (fromToolbar && element !== document.body && overlay.parentNode !== document.body) {
				boundingClientRect = getBoundingClientRect(element);
				overlay.style.top = boundingClientRect.top + window.pageYOffset + 'px';
				overlay.style.left = boundingClientRect.left + window.pageXOffset + 'px';
				overlay.style.width = boundingClientRect.width + 'px';
				overlay.style.height = boundingClientRect.height + 'px';
				document.body.appendChild(overlay);
			}
			if (! isOver) {
				isOver = true;
				document.body.appendChild(toolbar);
				boundingClientRect = null;
			}
			if (!fromToolbar) {
				removeClass(toolbar, 'rsfh-toolbar-minor');
				if (element !== document.body) {
					boundingClientRect = boundingClientRect || getBoundingClientRect(element);
					toolbar.style.top = Math.max(0, boundingClientRect.top - toolbar.offsetHeight + 2) + window.pageYOffset + 'px';
					toolbar.style.left = Math.max(0, boundingClientRect.left) + window.pageXOffset + 'px';
				}
				event.currentToolbars = event.currentToolbars || [];
				var insertPos = 0;
				var elementDepth = getNodeDepth(toolbar.linkedElement);
				event.currentToolbars.forEach(function(toolbar1, index1) {
					if (elementDepth < getNodeDepth(toolbar1.linkedElement)) {
						return false;
					}
					insertPos++;
				});
				event.currentToolbars.splice(insertPos, 0, toolbar);
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
							addClass(toolbar2, 'rsfh-toolbar-minor');
						}
					});
				});
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
				clearTimeout(timeout2);
				timeout2 = setTimeout(function() {
					if (overlay.parentNode === document.body) {
						document.body.removeChild(overlay);
					}
				}, 10);
			}
		};
		var scroll = function(event) {
			if (element !== document.body) {
				boundingClientRect = null;
			}
			if (isOver && !timeout) {
				over(event);
			}
		};

		addEvent(toolbar, 'mouseover', function (event) {
			over(event, true);
		});
		addEvent(toolbar, 'mouseout', function(event) {
			out(event, true);
		});
		addEvent(element, 'mouseover', function (event) {
			over(event);
		});
		addEvent(element, 'mouseout', function(event) {
			out(event);
		});
		addEvent(window, 'scroll', function(event) {
			scroll(event);
		});

	};

	var closeLightbox = function(withoutReload) {
		if (lightbox) {
			lightbox.innerHTML = '';
		}
		document.documentElement.style.marginTop = '';
		document.documentElement.style.height = '';
		document.documentElement.style.overflow = '';
		window.scrollTo(0, lightboxScrollPosition);
		if (!withoutReload) {
			setCookie('rsfh-scroll-position', lightboxScrollPosition);
			document.location.reload();
		}
		else {
			lightbox.parentNode.removeChild(lightbox);
		}
	};

	if (getCookie('rsfh-scroll-position')) {
		(function() {
			var interval;
			var scrollPos = parseInt(getCookie('rsfh-scroll-position'), 10);
			var scroll = function() {
				window.scrollTo(0, scrollPos);
			};
			setCookie('rsfh-scroll-position', null);
			scroll();
			interval = setInterval(scroll, 10);
			addEvent(window, 'load', function() {
				clearInterval(interval);
				scroll();
				setTimeout(scroll, 10);
				setTimeout(scroll, 100);
			});
		})();
	}

	Array.prototype.forEach.call(document.querySelectorAll('*[data-frontend-helper]'), function(element) {
		init(element);
	});

	Array.prototype.forEach.call(document.querySelectorAll('.rsfh-dummy[data-frontend-helper]'), function(element) {
		element.parentNode.removeChild(element);
	});

	addEvent(window, 'mouseover', function(event) {
		for (var node = event.target; node && node.getAttribute; node = node.parentNode) {
			if (! node.frontendHelperEnabled && node.getAttribute('data-frontend-helper')) {
				init(node, true);
			}
		}
	});

	window.rsfhCloseLightbox = closeLightbox;

}, false);
})(window, document);
