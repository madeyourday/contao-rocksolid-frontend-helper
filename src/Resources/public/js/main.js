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
	var triggerEvent = function(element, eventName) {
		var evt = document.createEvent('HTMLEvents');
		evt.initEvent(eventName, true, true);
		element.dispatchEvent(evt);
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
	var insertElementAt = function(element, reference, before) {
		if (before) {
			reference.parentNode.insertBefore(element, reference);
		}
		else if (reference.nextSibling) {
			reference.parentNode.insertBefore(element, reference.nextSibling);
		}
		else {
			reference.parentNode.appendChild(element);
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
	var buildDropElements = function(container, containerData) {
		var elements = [];
		Array.prototype.forEach.call(container.querySelectorAll('*[data-frontend-helper]'), function(element) {
			var data = getNodeData(element);
			if (data.parent === containerData.container) {
				elements.push({
					element: element,
					data: data,
				});
			}
		});
		return elements;
	};
	var destroyDropElements = function(dropElements) {
		dropElements.length = 0;
	};
	var initDropArea = function(element, data) {
		var dropping = 0;
		var dropIndicator;
		var dropElements = [];
		var validateDragData = function(event) {
			if (
				!event.dataTransfer
				|| !event.dataTransfer.types
				|| event.dataTransfer.types.indexOf('text/rsfh-' + data.table) === -1
			) {
				return false;
			}
			return true;
		};
		var getDropElement = function(event) {
			var currentDropElement;
			dropElements.forEach(function(dropElement) {
				var clientRect = getBoundingClientRect(dropElement.element);
				if (!currentDropElement || (clientRect.width && clientRect.top < event.clientY && clientRect.left < event.clientX)) {
					currentDropElement = {
						element: dropElement.element,
						data: dropElement.data,
						clientRect: clientRect,
						position: clientRect.top + (clientRect.height / 2) < event.clientY ? 'after' : 'before',
					};
				}
			});
			return currentDropElement;
		};
		addEvent(element, 'dragenter', function(event) {
			if (validateDragData(event)) {
				if (!dropping) {
					dropElements = buildDropElements(element, data);
					dropIndicator = document.createElement('div');
					dropIndicator.className = 'rsfh-drop-indicator';
					document.body.appendChild(dropIndicator);
				}
				dropping++;
			}
		});
		addEvent(element, 'dragleave', function(event) {
			if (dropping) {
				dropping--;
				if (!dropping) {
					destroyDropElements(dropElements);
					dropIndicator.parentNode.removeChild(dropIndicator);
				}
			}
		});
		addEvent(element, 'dragover', function(event) {
			if (!validateDragData(event)) {
				return;
			}
			event.preventDefault();
			if (event.dataTransfer.effectAllowed === 'copy') {
				event.dataTransfer.dropEffect = 'copy';
			}
			else {
				event.dataTransfer.dropEffect = 'move';
			}
			var dropElement = getDropElement(event);
			var clientRect = dropElement.clientRect;
			dropIndicator.style.top = clientRect.top
				+ (dropElement.position === 'before' ? 0 : clientRect.height)
				+ window.pageYOffset + 'px';
			dropIndicator.style.left = clientRect.left + window.pageXOffset + 'px';
			dropIndicator.style.width = clientRect.width + 'px';
		});
		addEvent(element, 'drop', function(event) {
			if (!validateDragData(event)) {
				return;
			}
			if (dropping) {
				dropping = 0;
			}
			var dropElement = getDropElement(event);
			var dropData = dropElement.data;
			var dragData = JSON.parse(event.dataTransfer.getData('text/rsfh-' + data.table));
			event.stopPropagation();

			destroyDropElements(dropElements);
			dropIndicator.parentNode.removeChild(dropIndicator);

			var formData = new FormData();
			formData.append('REQUEST_TOKEN', config.REQUEST_TOKEN);
			formData.append('table', data.table);
			formData.append('act', dragData.act);
			formData.append('type', dragData.type);
			formData.append('ids', (dragData.ids || []).join(','));
			formData.append('pid', dropData.id);
			formData.append('parent', data.container);
			formData.append('position', dropElement.position);

			if (dragData.act === 'cut' && currentDragElement && dropElement.element) {
				triggerEvent(currentDragElement, 'mouseout');
				insertElementAt(currentDragElement, dropElement.element, dropElement.position === 'before');
			}
			else {
				var placeholder = document.createElement('div');
				placeholder.innerHTML = '…';
				insertElementAt(placeholder, dropElement.element, dropElement.position === 'before');
			}

			fetch(config.routes.insert, {
				method: 'POST',
				credentials: 'include',
				body: formData,
			})
				.then(function(response) {
					return response.json();
				})
				.then(function(json) {
					if (placeholder) {
						if (json.table && json.id) {
							renderElement(placeholder, json.table, json.id);
						}
						else {
							setCookie('rsfh-scroll-position', Math.round(window.pageYOffset || document.documentElement.scrollTop) || 0);
							document.location.reload();
						}
					}
				})
				.catch(function(error) {
					throw error;
				});

		});
	};
	var initDragHandle = function(element, data, ids, handle) {
		handle.draggable = true;
		addEvent(handle, 'dragstart', function(event) {
			if (event.dataTransfer.addElement) {
				event.dataTransfer.addElement(element);
			}
			currentDragElement = element;
			event.dataTransfer.effectAllowed = 'move';
			event.dataTransfer.setData('text/rsfh-' + data.table, JSON.stringify({
				act: 'cut',
				ids: ids,
			}));
		});
	};
	var renderElement = function(element, table, id) {

		var formData = new FormData();
		formData.append('table', table);
		formData.append('id', id);

		element.style.setProperty('opacity', '0.25', 'important');

		fetch(config.routes.render, {
			method: 'POST',
			credentials: 'include',
			body: formData,
		})
			.then(function(response) {
				return response.text();
			})
			.then(function(html) {
				var htmlWrap = document.createElement('div');
				var nodes = [];
				htmlWrap.innerHTML = html;
				while (htmlWrap.firstChild) {
					nodes.push(htmlWrap.firstChild);
					element.parentNode.insertBefore(htmlWrap.firstChild, element);
				}
				triggerEvent(element, 'mouseout');
				element.parentNode.removeChild(element);
				nodes.forEach(function(node) {
					if (node.getAttribute && node.getAttribute('data-frontend-helper')) {
						init(node);
					}
				});
			})
			.catch(function(error) {
				throw error;
			});

	};
	var deleteElement = function(element, data) {

		element.style.setProperty('opacity', '0.25', 'important');

		var formData = new FormData();
		formData.append('table', data.table);
		formData.append('id', data.id);
		formData.append('parent', data.parent);
		formData.append('REQUEST_TOKEN', config.REQUEST_TOKEN);

		fetch(config.routes.delete, {
			method: 'POST',
			credentials: 'include',
			body: formData,
		})
			.then(function(response) {
				return response.json();
			})
			.then(function(json) {
				if (json.success) {
					triggerEvent(element, 'mouseout');
					element.parentNode.removeChild(element);
				}
				else {
					throw new Error();
				}
			})
			.catch(function(error) {
				element.style.opacity = '';
				throw error;
			});

	};

	var active = !!getCookie('rsfh-active');
	var lightbox;
	var lightboxIsPopup;
	var renderOnClose;
	var lightboxScrollPosition;
	var config = {};
	var currentDragElement;

	var buildContentElementList = function(elements) {

		var elementsByGroup = {};

		Object.keys(elements).forEach(function(type) {
			elementsByGroup[elements[type].group] = elementsByGroup[elements[type].group] || {};
			elementsByGroup[elements[type].group][type] = elements[type];
		});

		var wrap = document.createElement('div');
		wrap.className = 'rsfh-element-list is-closed';

		var closeButton = document.createElement('a');
		closeButton.className = 'rsfh-element-list-close';
		closeButton.innerHTML = 'X';
		closeButton.href = '';
		addEvent(closeButton, 'click', function(event) {
			event.preventDefault();
			wrap.className += ' is-closed';
			setTimeout(function() {
				wrap.parentNode.removeChild(wrap);
			}, 300);
		});
		wrap.appendChild(closeButton);

		var wrapUl = document.createElement('ul');
		wrap.appendChild(wrapUl);

		Object.keys(elementsByGroup).forEach(function(group) {

			var groupLi = document.createElement('li');
			wrapUl.appendChild(groupLi);
			var groupLabel = document.createElement('span');
			groupLi.appendChild(groupLabel);
			groupLabel.innerText = group;
			var groupUl = document.createElement('ul');
			groupLi.appendChild(groupUl);

			Object.keys(elementsByGroup[group]).forEach(function(type) {
				var elementLi = document.createElement('li');
				groupUl.appendChild(elementLi);
				elementLi.innerText = elementsByGroup[group][type].label[0] || type;
				elementLi.draggable = true;
				addEvent(elementLi, 'dragstart', function(event) {
					event.dataTransfer.effectAllowed = 'copy';
					event.dataTransfer.setData('text/rsfh-tl_content', JSON.stringify({
						act: 'create',
						type: type,
					}));
				});
			});

		});

		document.body.appendChild(wrap);

		// Trigger reflow to apply the styles
		wrap.offsetWidth;
		wrap.className = wrap.className.replace(' is-closed', '');

	};

	var initContentElementList = function() {
		fetch(config.routes.elements + '?table=tl_content', {
			credentials: 'include',
		})
			.then(function(response) {
				return response.json();
			})
			.then(function(json) {
				buildContentElementList(json);
			})
			.catch(function(error) {
				throw error;
			});
	};

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

			if (targetLink.href.match(/[&?]act=delete(?:&|$)/) && data.table && data.id && data.parent) {
				event.preventDefault();
				deleteElement(element, data);
				return;
			}

			if (
				!config.lightbox
				|| hasClass(event.target, 'rsfh-activate')
				|| hasClass(event.target, 'rsfh-preview')
			) {
				return;
			}

			lightboxIsPopup = !!targetLink.href.match(/[&?]popup=1(?:&|$)/);

			if (data.renderLive) {
				renderOnClose = {
					element: element,
					table: data.table,
					id: data.id,
				};
			}
			else {
				renderOnClose = undefined;
			}

			// Disable lightbox if users try to open links in a new tab
			if (event.ctrlKey || event.shiftKey || event.metaKey || event.which === 2) {
				if (lightboxIsPopup) {
					targetLink.href = targetLink.href.replace(/([&?])popup=1(?:&|$)/, '$1');
					setTimeout(function() {
						targetLink.href += '&popup=1';
					}, 100);
				}
				return;
			}

			lightboxScrollPosition = Math.round(window.pageYOffset || document.documentElement.scrollTop) || 0;

			if (!lightboxIsPopup) {
				document.documentElement.style.marginTop = -lightboxScrollPosition + 'px';
				document.documentElement.style.height = (window.innerHeight || document.documentElement.clientHeight) + 'px';
				document.documentElement.style.overflow = 'hidden';
				window.scrollTo(0, 0);
			}

			lightbox = lightbox || document.createElement('div');
			lightbox.innerHTML = '';
			lightbox.className = 'rsfh-lightbox is-closed';

			if (lightboxIsPopup) {
				lightbox.className += ' is-popup';
			}

			var firstLoadEvent = true;
			var iframe = document.createElement('iframe');
			iframe.id = iframe.name = 'rsfh-lightbox-iframe';
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

			// Trigger reflow to apply the styles
			lightbox.offsetWidth;
			lightbox.className = lightbox.className.replace(' is-closed', '');

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

		if (data.container && data.table) {
			initDropArea(element, data);
		}

		if (data.id && data.table && data.parent) {

			var dragHandle = document.createElement('div');
			dragHandle.className = 'rsfh-drag-handle';
			toolbar.appendChild(dragHandle);

			var ids = [data.id];

			Array.prototype.forEach.call(element.querySelectorAll('*[data-frontend-helper]'), function(element) {
				var childData = getNodeData(element);
				if (childData.id && childData.table === data.table && childData.parent === data.parent) {
					ids.push(childData.id);
				}
			});

			initDragHandle(element, data, ids, dragHandle);

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
			if (getCookie('FE_PREVIEW')) {
				addClass(previewLink, 'rsfh-preview-active');
			}
			previewLink.innerHTML = previewLink.title = getCookie('FE_PREVIEW') ?
				data.labels.previewHide :
				data.labels.previewShow;
			addEvent(previewLink, 'click', function () {
				setCookie('FE_PREVIEW', getCookie('FE_PREVIEW') ? null : '1');
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

			var elementsLink = document.createElement('a');
			elementsLink.href = document.location.href;
			elementsLink.className = 'rsfh-elements';
			elementsLink.innerHTML = elementsLink.title = getLabel('contentElements');
			addEvent(elementsLink, 'click', function(event) {
				initContentElementList();
				event.stopPropagation();
				event.preventDefault();
			});
			mainNavContents.insertBefore(
				elementsLink,
				mainNavContents.querySelector('.rsfh-backend')
				|| mainNavContents.childNodes[0]
			);
			mainNavContents.insertBefore(
				document.createElement('hr'),
				elementsLink
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
				if (overlay.parentNode === document.body) {
					document.body.removeChild(overlay);
				}
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
		if (lightbox && lightboxIsPopup) {
			lightbox.className += ' is-closed';
			setTimeout(clean, 300);
		}
		else {
			clean();
		}
		if (!withoutReload) {
			if (renderOnClose) {
				renderElement(renderOnClose.element, renderOnClose.table, renderOnClose.id);
			}
			else {
				setCookie('rsfh-scroll-position', lightboxScrollPosition);
				document.location.reload();
			}
		}
		function clean() {
			if (lightbox) {
				lightbox.innerHTML = '';
			}
			if (!lightboxIsPopup) {
				document.documentElement.style.marginTop = '';
				document.documentElement.style.height = '';
				document.documentElement.style.overflow = '';
				window.scrollTo(0, lightboxScrollPosition);
			}
			if (withoutReload) {
				lightbox.parentNode.removeChild(lightbox);
			}
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
