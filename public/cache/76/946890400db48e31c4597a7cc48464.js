/**
 * jscolor - JavaScript Color Picker
 *
 * @link    http://jscolor.com
 * @license For open source use: GPLv3
 *          For commercial use: JSColor Commercial License
 * @author  Jan Odvarko
 * @version 2.0.4
 *
 * See usage examples at http://jscolor.com/examples/
 */


/* "use strict"; */


if (!window.jscolor) { window.jscolor = (function () {


var jsc = {


	register : function () {
		jsc.attachDOMReadyEvent(jsc.init);
		jsc.attachEvent(document, 'mousedown', jsc.onDocumentMouseDown);
		jsc.attachEvent(document, 'touchstart', jsc.onDocumentTouchStart);
		jsc.attachEvent(window, 'resize', jsc.onWindowResize);
	},


	init : function () {
		if (jsc.jscolor.lookupClass) {
			jsc.jscolor.installByClassName(jsc.jscolor.lookupClass);
		}
	},


	tryInstallOnElements : function (elms, className) {
		var matchClass = new RegExp('(^|\\s)(' + className + ')(\\s*(\\{[^}]*\\})|\\s|$)', 'i');

		for (var i = 0; i < elms.length; i += 1) {
			if (elms[i].type !== undefined && elms[i].type.toLowerCase() == 'color') {
				if (jsc.isColorAttrSupported) {
					// skip inputs of type 'color' if supported by the browser
					continue;
				}
			}
			var m;
			if (!elms[i].jscolor && elms[i].className && (m = elms[i].className.match(matchClass))) {
				var targetElm = elms[i];
				var optsStr = null;

				var dataOptions = jsc.getDataAttr(targetElm, 'jscolor');
				if (dataOptions !== null) {
					optsStr = dataOptions;
				} else if (m[4]) {
					optsStr = m[4];
				}

				var opts = {};
				if (optsStr) {
					try {
						opts = (new Function ('return (' + optsStr + ')'))();
					} catch(eParseError) {
						jsc.warn('Error parsing jscolor options: ' + eParseError + ':\n' + optsStr);
					}
				}
				targetElm.jscolor = new jsc.jscolor(targetElm, opts);
			}
		}
	},


	isColorAttrSupported : (function () {
		var elm = document.createElement('input');
		if (elm.setAttribute) {
			elm.setAttribute('type', 'color');
			if (elm.type.toLowerCase() == 'color') {
				return true;
			}
		}
		return false;
	})(),


	isCanvasSupported : (function () {
		var elm = document.createElement('canvas');
		return !!(elm.getContext && elm.getContext('2d'));
	})(),


	fetchElement : function (mixed) {
		return typeof mixed === 'string' ? document.getElementById(mixed) : mixed;
	},


	isElementType : function (elm, type) {
		return elm.nodeName.toLowerCase() === type.toLowerCase();
	},


	getDataAttr : function (el, name) {
		var attrName = 'data-' + name;
		var attrValue = el.getAttribute(attrName);
		if (attrValue !== null) {
			return attrValue;
		}
		return null;
	},


	attachEvent : function (el, evnt, func) {
		if (el.addEventListener) {
			el.addEventListener(evnt, func, false);
		} else if (el.attachEvent) {
			el.attachEvent('on' + evnt, func);
		}
	},


	detachEvent : function (el, evnt, func) {
		if (el.removeEventListener) {
			el.removeEventListener(evnt, func, false);
		} else if (el.detachEvent) {
			el.detachEvent('on' + evnt, func);
		}
	},


	_attachedGroupEvents : {},


	attachGroupEvent : function (groupName, el, evnt, func) {
		if (!jsc._attachedGroupEvents.hasOwnProperty(groupName)) {
			jsc._attachedGroupEvents[groupName] = [];
		}
		jsc._attachedGroupEvents[groupName].push([el, evnt, func]);
		jsc.attachEvent(el, evnt, func);
	},


	detachGroupEvents : function (groupName) {
		if (jsc._attachedGroupEvents.hasOwnProperty(groupName)) {
			for (var i = 0; i < jsc._attachedGroupEvents[groupName].length; i += 1) {
				var evt = jsc._attachedGroupEvents[groupName][i];
				jsc.detachEvent(evt[0], evt[1], evt[2]);
			}
			delete jsc._attachedGroupEvents[groupName];
		}
	},


	attachDOMReadyEvent : function (func) {
		var fired = false;
		var fireOnce = function () {
			if (!fired) {
				fired = true;
				func();
			}
		};

		if (document.readyState === 'complete') {
			setTimeout(fireOnce, 1); // async
			return;
		}

		if (document.addEventListener) {
			document.addEventListener('DOMContentLoaded', fireOnce, false);

			// Fallback
			window.addEventListener('load', fireOnce, false);

		} else if (document.attachEvent) {
			// IE
			document.attachEvent('onreadystatechange', function () {
				if (document.readyState === 'complete') {
					document.detachEvent('onreadystatechange', arguments.callee);
					fireOnce();
				}
			})

			// Fallback
			window.attachEvent('onload', fireOnce);

			// IE7/8
			if (document.documentElement.doScroll && window == window.top) {
				var tryScroll = function () {
					if (!document.body) { return; }
					try {
						document.documentElement.doScroll('left');
						fireOnce();
					} catch (e) {
						setTimeout(tryScroll, 1);
					}
				};
				tryScroll();
			}
		}
	},


	warn : function (msg) {
		if (window.console && window.console.warn) {
			window.console.warn(msg);
		}
	},


	preventDefault : function (e) {
		if (e.preventDefault) { e.preventDefault(); }
		e.returnValue = false;
	},


	captureTarget : function (target) {
		// IE
		if (target.setCapture) {
			jsc._capturedTarget = target;
			jsc._capturedTarget.setCapture();
		}
	},


	releaseTarget : function () {
		// IE
		if (jsc._capturedTarget) {
			jsc._capturedTarget.releaseCapture();
			jsc._capturedTarget = null;
		}
	},


	fireEvent : function (el, evnt) {
		if (!el) {
			return;
		}
		if (document.createEvent) {
			var ev = document.createEvent('HTMLEvents');
			ev.initEvent(evnt, true, true);
			el.dispatchEvent(ev);
		} else if (document.createEventObject) {
			var ev = document.createEventObject();
			el.fireEvent('on' + evnt, ev);
		} else if (el['on' + evnt]) { // alternatively use the traditional event model
			el['on' + evnt]();
		}
	},


	classNameToList : function (className) {
		return className.replace(/^\s+|\s+$/g, '').split(/\s+/);
	},


	// The className parameter (str) can only contain a single class name
	hasClass : function (elm, className) {
		if (!className) {
			return false;
		}
		return -1 != (' ' + elm.className.replace(/\s+/g, ' ') + ' ').indexOf(' ' + className + ' ');
	},


	// The className parameter (str) can contain multiple class names separated by whitespace
	setClass : function (elm, className) {
		var classList = jsc.classNameToList(className);
		for (var i = 0; i < classList.length; i += 1) {
			if (!jsc.hasClass(elm, classList[i])) {
				elm.className += (elm.className ? ' ' : '') + classList[i];
			}
		}
	},


	// The className parameter (str) can contain multiple class names separated by whitespace
	unsetClass : function (elm, className) {
		var classList = jsc.classNameToList(className);
		for (var i = 0; i < classList.length; i += 1) {
			var repl = new RegExp(
				'^\\s*' + classList[i] + '\\s*|' +
				'\\s*' + classList[i] + '\\s*$|' +
				'\\s+' + classList[i] + '(\\s+)',
				'g'
			);
			elm.className = elm.className.replace(repl, '$1');
		}
	},


	getStyle : function (elm) {
		return window.getComputedStyle ? window.getComputedStyle(elm) : elm.currentStyle;
	},


	setStyle : (function () {
		var helper = document.createElement('div');
		var getSupportedProp = function (names) {
			for (var i = 0; i < names.length; i += 1) {
				if (names[i] in helper.style) {
					return names[i];
				}
			}
		};
		var props = {
			borderRadius: getSupportedProp(['borderRadius', 'MozBorderRadius', 'webkitBorderRadius']),
			boxShadow: getSupportedProp(['boxShadow', 'MozBoxShadow', 'webkitBoxShadow'])
		};
		return function (elm, prop, value) {
			switch (prop.toLowerCase()) {
			case 'opacity':
				var alphaOpacity = Math.round(parseFloat(value) * 100);
				elm.style.opacity = value;
				elm.style.filter = 'alpha(opacity=' + alphaOpacity + ')';
				break;
			default:
				elm.style[props[prop]] = value;
				break;
			}
		};
	})(),


	setBorderRadius : function (elm, value) {
		jsc.setStyle(elm, 'borderRadius', value || '0');
	},


	setBoxShadow : function (elm, value) {
		jsc.setStyle(elm, 'boxShadow', value || 'none');
	},


	getElementPos : function (e, relativeToViewport) {
		var x=0, y=0;
		var rect = e.getBoundingClientRect();
		x = rect.left;
		y = rect.top;
		if (!relativeToViewport) {
			var viewPos = jsc.getViewPos();
			x += viewPos[0];
			y += viewPos[1];
		}
		return [x, y];
	},


	getElementSize : function (e) {
		return [e.offsetWidth, e.offsetHeight];
	},


	// get pointer's X/Y coordinates relative to viewport
	getAbsPointerPos : function (e) {
		if (!e) { e = window.event; }
		var x = 0, y = 0;
		if (typeof e.changedTouches !== 'undefined' && e.changedTouches.length) {
			// touch devices
			x = e.changedTouches[0].clientX;
			y = e.changedTouches[0].clientY;
		} else if (typeof e.clientX === 'number') {
			x = e.clientX;
			y = e.clientY;
		}
		return { x: x, y: y };
	},


	// get pointer's X/Y coordinates relative to target element
	getRelPointerPos : function (e) {
		if (!e) { e = window.event; }
		var target = e.target || e.srcElement;
		var targetRect = target.getBoundingClientRect();

		var x = 0, y = 0;

		var clientX = 0, clientY = 0;
		if (typeof e.changedTouches !== 'undefined' && e.changedTouches.length) {
			// touch devices
			clientX = e.changedTouches[0].clientX;
			clientY = e.changedTouches[0].clientY;
		} else if (typeof e.clientX === 'number') {
			clientX = e.clientX;
			clientY = e.clientY;
		}

		x = clientX - targetRect.left;
		y = clientY - targetRect.top;
		return { x: x, y: y };
	},


	getViewPos : function () {
		var doc = document.documentElement;
		return [
			(window.pageXOffset || doc.scrollLeft) - (doc.clientLeft || 0),
			(window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0)
		];
	},


	getViewSize : function () {
		var doc = document.documentElement;
		return [
			(window.innerWidth || doc.clientWidth),
			(window.innerHeight || doc.clientHeight),
		];
	},


	redrawPosition : function () {

		if (jsc.picker && jsc.picker.owner) {
			var thisObj = jsc.picker.owner;

			var tp, vp;

			if (thisObj.fixed) {
				// Fixed elements are positioned relative to viewport,
				// therefore we can ignore the scroll offset
				tp = jsc.getElementPos(thisObj.targetElement, true); // target pos
				vp = [0, 0]; // view pos
			} else {
				tp = jsc.getElementPos(thisObj.targetElement); // target pos
				vp = jsc.getViewPos(); // view pos
			}

			var ts = jsc.getElementSize(thisObj.targetElement); // target size
			var vs = jsc.getViewSize(); // view size
			var ps = jsc.getPickerOuterDims(thisObj); // picker size
			var a, b, c;
			switch (thisObj.position.toLowerCase()) {
				case 'left': a=1; b=0; c=-1; break;
				case 'right':a=1; b=0; c=1; break;
				case 'top':  a=0; b=1; c=-1; break;
				default:     a=0; b=1; c=1; break;
			}
			var l = (ts[b]+ps[b])/2;

			// compute picker position
			if (!thisObj.smartPosition) {
				var pp = [
					tp[a],
					tp[b]+ts[b]-l+l*c
				];
			} else {
				var pp = [
					-vp[a]+tp[a]+ps[a] > vs[a] ?
						(-vp[a]+tp[a]+ts[a]/2 > vs[a]/2 && tp[a]+ts[a]-ps[a] >= 0 ? tp[a]+ts[a]-ps[a] : tp[a]) :
						tp[a],
					-vp[b]+tp[b]+ts[b]+ps[b]-l+l*c > vs[b] ?
						(-vp[b]+tp[b]+ts[b]/2 > vs[b]/2 && tp[b]+ts[b]-l-l*c >= 0 ? tp[b]+ts[b]-l-l*c : tp[b]+ts[b]-l+l*c) :
						(tp[b]+ts[b]-l+l*c >= 0 ? tp[b]+ts[b]-l+l*c : tp[b]+ts[b]-l-l*c)
				];
			}

			var x = pp[a];
			var y = pp[b];
			var positionValue = thisObj.fixed ? 'fixed' : 'absolute';
			var contractShadow =
				(pp[0] + ps[0] > tp[0] || pp[0] < tp[0] + ts[0]) &&
				(pp[1] + ps[1] < tp[1] + ts[1]);

			jsc._drawPosition(thisObj, x, y, positionValue, contractShadow);
		}
	},


	_drawPosition : function (thisObj, x, y, positionValue, contractShadow) {
		var vShadow = contractShadow ? 0 : thisObj.shadowBlur; // px

		jsc.picker.wrap.style.position = positionValue;
		jsc.picker.wrap.style.left = x + 'px';
		jsc.picker.wrap.style.top = y + 'px';

		jsc.setBoxShadow(
			jsc.picker.boxS,
			thisObj.shadow ?
				new jsc.BoxShadow(0, vShadow, thisObj.shadowBlur, 0, thisObj.shadowColor) :
				null);
	},


	getPickerDims : function (thisObj) {
		var displaySlider = !!jsc.getSliderComponent(thisObj);
		var dims = [
			2 * thisObj.insetWidth + 2 * thisObj.padding + thisObj.width +
				(displaySlider ? 2 * thisObj.insetWidth + jsc.getPadToSliderPadding(thisObj) + thisObj.sliderSize : 0),
			2 * thisObj.insetWidth + 2 * thisObj.padding + thisObj.height +
				(thisObj.closable ? 2 * thisObj.insetWidth + thisObj.padding + thisObj.buttonHeight : 0)
		];
		return dims;
	},


	getPickerOuterDims : function (thisObj) {
		var dims = jsc.getPickerDims(thisObj);
		return [
			dims[0] + 2 * thisObj.borderWidth,
			dims[1] + 2 * thisObj.borderWidth
		];
	},


	getPadToSliderPadding : function (thisObj) {
		return Math.max(thisObj.padding, 1.5 * (2 * thisObj.pointerBorderWidth + thisObj.pointerThickness));
	},


	getPadYComponent : function (thisObj) {
		switch (thisObj.mode.charAt(1).toLowerCase()) {
			case 'v': return 'v'; break;
		}
		return 's';
	},


	getSliderComponent : function (thisObj) {
		if (thisObj.mode.length > 2) {
			switch (thisObj.mode.charAt(2).toLowerCase()) {
				case 's': return 's'; break;
				case 'v': return 'v'; break;
			}
		}
		return null;
	},


	onDocumentMouseDown : function (e) {
		if (!e) { e = window.event; }
		var target = e.target || e.srcElement;

		if (target._jscLinkedInstance) {
			if (target._jscLinkedInstance.showOnClick) {
				target._jscLinkedInstance.show();
			}
		} else if (target._jscControlName) {
			jsc.onControlPointerStart(e, target, target._jscControlName, 'mouse');
		} else {
			// Mouse is outside the picker controls -> hide the color picker!
			if (jsc.picker && jsc.picker.owner) {
				jsc.picker.owner.hide();
			}
		}
	},


	onDocumentTouchStart : function (e) {
		if (!e) { e = window.event; }
		var target = e.target || e.srcElement;

		if (target._jscLinkedInstance) {
			if (target._jscLinkedInstance.showOnClick) {
				target._jscLinkedInstance.show();
			}
		} else if (target._jscControlName) {
			jsc.onControlPointerStart(e, target, target._jscControlName, 'touch');
		} else {
			if (jsc.picker && jsc.picker.owner) {
				jsc.picker.owner.hide();
			}
		}
	},


	onWindowResize : function (e) {
		jsc.redrawPosition();
	},


	onParentScroll : function (e) {
		// hide the picker when one of the parent elements is scrolled
		if (jsc.picker && jsc.picker.owner) {
			jsc.picker.owner.hide();
		}
	},


	_pointerMoveEvent : {
		mouse: 'mousemove',
		touch: 'touchmove'
	},
	_pointerEndEvent : {
		mouse: 'mouseup',
		touch: 'touchend'
	},


	_pointerOrigin : null,
	_capturedTarget : null,


	onControlPointerStart : function (e, target, controlName, pointerType) {
		var thisObj = target._jscInstance;

		jsc.preventDefault(e);
		jsc.captureTarget(target);

		var registerDragEvents = function (doc, offset) {
			jsc.attachGroupEvent('drag', doc, jsc._pointerMoveEvent[pointerType],
				jsc.onDocumentPointerMove(e, target, controlName, pointerType, offset));
			jsc.attachGroupEvent('drag', doc, jsc._pointerEndEvent[pointerType],
				jsc.onDocumentPointerEnd(e, target, controlName, pointerType));
		};

		registerDragEvents(document, [0, 0]);

		if (window.parent && window.frameElement) {
			var rect = window.frameElement.getBoundingClientRect();
			var ofs = [-rect.left, -rect.top];
			registerDragEvents(window.parent.window.document, ofs);
		}

		var abs = jsc.getAbsPointerPos(e);
		var rel = jsc.getRelPointerPos(e);
		jsc._pointerOrigin = {
			x: abs.x - rel.x,
			y: abs.y - rel.y
		};

		switch (controlName) {
		case 'pad':
			// if the slider is at the bottom, move it up
			switch (jsc.getSliderComponent(thisObj)) {
			case 's': if (thisObj.hsv[1] === 0) { thisObj.fromHSV(null, 100, null); }; break;
			case 'v': if (thisObj.hsv[2] === 0) { thisObj.fromHSV(null, null, 100); }; break;
			}
			jsc.setPad(thisObj, e, 0, 0);
			break;

		case 'sld':
			jsc.setSld(thisObj, e, 0);
			break;
		}

		jsc.dispatchFineChange(thisObj);
	},


	onDocumentPointerMove : function (e, target, controlName, pointerType, offset) {
		return function (e) {
			var thisObj = target._jscInstance;
			switch (controlName) {
			case 'pad':
				if (!e) { e = window.event; }
				jsc.setPad(thisObj, e, offset[0], offset[1]);
				jsc.dispatchFineChange(thisObj);
				break;

			case 'sld':
				if (!e) { e = window.event; }
				jsc.setSld(thisObj, e, offset[1]);
				jsc.dispatchFineChange(thisObj);
				break;
			}
		}
	},


	onDocumentPointerEnd : function (e, target, controlName, pointerType) {
		return function (e) {
			var thisObj = target._jscInstance;
			jsc.detachGroupEvents('drag');
			jsc.releaseTarget();
			// Always dispatch changes after detaching outstanding mouse handlers,
			// in case some user interaction will occur in user's onchange callback
			// that would intrude with current mouse events
			jsc.dispatchChange(thisObj);
		};
	},


	dispatchChange : function (thisObj) {
		if (thisObj.valueElement) {
			if (jsc.isElementType(thisObj.valueElement, 'input')) {
				jsc.fireEvent(thisObj.valueElement, 'change');
			}
		}
	},


	dispatchFineChange : function (thisObj) {
		if (thisObj.onFineChange) {
			var callback;
			if (typeof thisObj.onFineChange === 'string') {
				callback = new Function (thisObj.onFineChange);
			} else {
				callback = thisObj.onFineChange;
			}
			callback.call(thisObj);
		}
	},


	setPad : function (thisObj, e, ofsX, ofsY) {
		var pointerAbs = jsc.getAbsPointerPos(e);
		var x = ofsX + pointerAbs.x - jsc._pointerOrigin.x - thisObj.padding - thisObj.insetWidth;
		var y = ofsY + pointerAbs.y - jsc._pointerOrigin.y - thisObj.padding - thisObj.insetWidth;

		var xVal = x * (360 / (thisObj.width - 1));
		var yVal = 100 - (y * (100 / (thisObj.height - 1)));

		switch (jsc.getPadYComponent(thisObj)) {
		case 's': thisObj.fromHSV(xVal, yVal, null, jsc.leaveSld); break;
		case 'v': thisObj.fromHSV(xVal, null, yVal, jsc.leaveSld); break;
		}
	},


	setSld : function (thisObj, e, ofsY) {
		var pointerAbs = jsc.getAbsPointerPos(e);
		var y = ofsY + pointerAbs.y - jsc._pointerOrigin.y - thisObj.padding - thisObj.insetWidth;

		var yVal = 100 - (y * (100 / (thisObj.height - 1)));

		switch (jsc.getSliderComponent(thisObj)) {
		case 's': thisObj.fromHSV(null, yVal, null, jsc.leavePad); break;
		case 'v': thisObj.fromHSV(null, null, yVal, jsc.leavePad); break;
		}
	},


	_vmlNS : 'jsc_vml_',
	_vmlCSS : 'jsc_vml_css_',
	_vmlReady : false,


	initVML : function () {
		if (!jsc._vmlReady) {
			// init VML namespace
			var doc = document;
			if (!doc.namespaces[jsc._vmlNS]) {
				doc.namespaces.add(jsc._vmlNS, 'urn:schemas-microsoft-com:vml');
			}
			if (!doc.styleSheets[jsc._vmlCSS]) {
				var tags = ['shape', 'shapetype', 'group', 'background', 'path', 'formulas', 'handles', 'fill', 'stroke', 'shadow', 'textbox', 'textpath', 'imagedata', 'line', 'polyline', 'curve', 'rect', 'roundrect', 'oval', 'arc', 'image'];
				var ss = doc.createStyleSheet();
				ss.owningElement.id = jsc._vmlCSS;
				for (var i = 0; i < tags.length; i += 1) {
					ss.addRule(jsc._vmlNS + '\\:' + tags[i], 'behavior:url(#default#VML);');
				}
			}
			jsc._vmlReady = true;
		}
	},


	createPalette : function () {

		var paletteObj = {
			elm: null,
			draw: null
		};

		if (jsc.isCanvasSupported) {
			// Canvas implementation for modern browsers

			var canvas = document.createElement('canvas');
			var ctx = canvas.getContext('2d');

			var drawFunc = function (width, height, type) {
				canvas.width = width;
				canvas.height = height;

				ctx.clearRect(0, 0, canvas.width, canvas.height);

				var hGrad = ctx.createLinearGradient(0, 0, canvas.width, 0);
				hGrad.addColorStop(0 / 6, '#F00');
				hGrad.addColorStop(1 / 6, '#FF0');
				hGrad.addColorStop(2 / 6, '#0F0');
				hGrad.addColorStop(3 / 6, '#0FF');
				hGrad.addColorStop(4 / 6, '#00F');
				hGrad.addColorStop(5 / 6, '#F0F');
				hGrad.addColorStop(6 / 6, '#F00');

				ctx.fillStyle = hGrad;
				ctx.fillRect(0, 0, canvas.width, canvas.height);

				var vGrad = ctx.createLinearGradient(0, 0, 0, canvas.height);
				switch (type.toLowerCase()) {
				case 's':
					vGrad.addColorStop(0, 'rgba(255,255,255,0)');
					vGrad.addColorStop(1, 'rgba(255,255,255,1)');
					break;
				case 'v':
					vGrad.addColorStop(0, 'rgba(0,0,0,0)');
					vGrad.addColorStop(1, 'rgba(0,0,0,1)');
					break;
				}
				ctx.fillStyle = vGrad;
				ctx.fillRect(0, 0, canvas.width, canvas.height);
			};

			paletteObj.elm = canvas;
			paletteObj.draw = drawFunc;

		} else {
			// VML fallback for IE 7 and 8

			jsc.initVML();

			var vmlContainer = document.createElement('div');
			vmlContainer.style.position = 'relative';
			vmlContainer.style.overflow = 'hidden';

			var hGrad = document.createElement(jsc._vmlNS + ':fill');
			hGrad.type = 'gradient';
			hGrad.method = 'linear';
			hGrad.angle = '90';
			hGrad.colors = '16.67% #F0F, 33.33% #00F, 50% #0FF, 66.67% #0F0, 83.33% #FF0'

			var hRect = document.createElement(jsc._vmlNS + ':rect');
			hRect.style.position = 'absolute';
			hRect.style.left = -1 + 'px';
			hRect.style.top = -1 + 'px';
			hRect.stroked = false;
			hRect.appendChild(hGrad);
			vmlContainer.appendChild(hRect);

			var vGrad = document.createElement(jsc._vmlNS + ':fill');
			vGrad.type = 'gradient';
			vGrad.method = 'linear';
			vGrad.angle = '180';
			vGrad.opacity = '0';

			var vRect = document.createElement(jsc._vmlNS + ':rect');
			vRect.style.position = 'absolute';
			vRect.style.left = -1 + 'px';
			vRect.style.top = -1 + 'px';
			vRect.stroked = false;
			vRect.appendChild(vGrad);
			vmlContainer.appendChild(vRect);

			var drawFunc = function (width, height, type) {
				vmlContainer.style.width = width + 'px';
				vmlContainer.style.height = height + 'px';

				hRect.style.width =
				vRect.style.width =
					(width + 1) + 'px';
				hRect.style.height =
				vRect.style.height =
					(height + 1) + 'px';

				// Colors must be specified during every redraw, otherwise IE won't display
				// a full gradient during a subsequential redraw
				hGrad.color = '#F00';
				hGrad.color2 = '#F00';

				switch (type.toLowerCase()) {
				case 's':
					vGrad.color = vGrad.color2 = '#FFF';
					break;
				case 'v':
					vGrad.color = vGrad.color2 = '#000';
					break;
				}
			};
			
			paletteObj.elm = vmlContainer;
			paletteObj.draw = drawFunc;
		}

		return paletteObj;
	},


	createSliderGradient : function () {

		var sliderObj = {
			elm: null,
			draw: null
		};

		if (jsc.isCanvasSupported) {
			// Canvas implementation for modern browsers

			var canvas = document.createElement('canvas');
			var ctx = canvas.getContext('2d');

			var drawFunc = function (width, height, color1, color2) {
				canvas.width = width;
				canvas.height = height;

				ctx.clearRect(0, 0, canvas.width, canvas.height);

				var grad = ctx.createLinearGradient(0, 0, 0, canvas.height);
				grad.addColorStop(0, color1);
				grad.addColorStop(1, color2);

				ctx.fillStyle = grad;
				ctx.fillRect(0, 0, canvas.width, canvas.height);
			};

			sliderObj.elm = canvas;
			sliderObj.draw = drawFunc;

		} else {
			// VML fallback for IE 7 and 8

			jsc.initVML();

			var vmlContainer = document.createElement('div');
			vmlContainer.style.position = 'relative';
			vmlContainer.style.overflow = 'hidden';

			var grad = document.createElement(jsc._vmlNS + ':fill');
			grad.type = 'gradient';
			grad.method = 'linear';
			grad.angle = '180';

			var rect = document.createElement(jsc._vmlNS + ':rect');
			rect.style.position = 'absolute';
			rect.style.left = -1 + 'px';
			rect.style.top = -1 + 'px';
			rect.stroked = false;
			rect.appendChild(grad);
			vmlContainer.appendChild(rect);

			var drawFunc = function (width, height, color1, color2) {
				vmlContainer.style.width = width + 'px';
				vmlContainer.style.height = height + 'px';

				rect.style.width = (width + 1) + 'px';
				rect.style.height = (height + 1) + 'px';

				grad.color = color1;
				grad.color2 = color2;
			};
			
			sliderObj.elm = vmlContainer;
			sliderObj.draw = drawFunc;
		}

		return sliderObj;
	},


	leaveValue : 1<<0,
	leaveStyle : 1<<1,
	leavePad : 1<<2,
	leaveSld : 1<<3,


	BoxShadow : (function () {
		var BoxShadow = function (hShadow, vShadow, blur, spread, color, inset) {
			this.hShadow = hShadow;
			this.vShadow = vShadow;
			this.blur = blur;
			this.spread = spread;
			this.color = color;
			this.inset = !!inset;
		};

		BoxShadow.prototype.toString = function () {
			var vals = [
				Math.round(this.hShadow) + 'px',
				Math.round(this.vShadow) + 'px',
				Math.round(this.blur) + 'px',
				Math.round(this.spread) + 'px',
				this.color
			];
			if (this.inset) {
				vals.push('inset');
			}
			return vals.join(' ');
		};

		return BoxShadow;
	})(),


	//
	// Usage:
	// var myColor = new jscolor(<targetElement> [, <options>])
	//

	jscolor : function (targetElement, options) {

		// General options
		//
		this.value = null; // initial HEX color. To change it later, use methods fromString(), fromHSV() and fromRGB()
		this.valueElement = targetElement; // element that will be used to display and input the color code
		this.styleElement = targetElement; // element that will preview the picked color using CSS backgroundColor
		this.required = true; // whether the associated text <input> can be left empty
		this.refine = true; // whether to refine the entered color code (e.g. uppercase it and remove whitespace)
		this.hash = false; // whether to prefix the HEX color code with # symbol
		this.uppercase = true; // whether to uppercase the color code
		this.onFineChange = null; // called instantly every time the color changes (value can be either a function or a string with javascript code)
		this.activeClass = 'jscolor-active'; // class to be set to the target element when a picker window is open on it
		this.minS = 0; // min allowed saturation (0 - 100)
		this.maxS = 100; // max allowed saturation (0 - 100)
		this.minV = 0; // min allowed value (brightness) (0 - 100)
		this.maxV = 100; // max allowed value (brightness) (0 - 100)

		// Accessing the picked color
		//
		this.hsv = [0, 0, 100]; // read-only  [0-360, 0-100, 0-100]
		this.rgb = [255, 255, 255]; // read-only  [0-255, 0-255, 0-255]

		// Color Picker options
		//
		this.width = 181; // width of color palette (in px)
		this.height = 101; // height of color palette (in px)
		this.showOnClick = true; // whether to display the color picker when user clicks on its target element
		this.mode = 'HSV'; // HSV | HVS | HS | HV - layout of the color picker controls
		this.position = 'bottom'; // left | right | top | bottom - position relative to the target element
		this.smartPosition = true; // automatically change picker position when there is not enough space for it
		this.sliderSize = 16; // px
		this.crossSize = 8; // px
		this.closable = false; // whether to display the Close button
		this.closeText = 'Close';
		this.buttonColor = '#000000'; // CSS color
		this.buttonHeight = 18; // px
		this.padding = 12; // px
		this.backgroundColor = '#FFFFFF'; // CSS color
		this.borderWidth = 1; // px
		this.borderColor = '#BBBBBB'; // CSS color
		this.borderRadius = 8; // px
		this.insetWidth = 1; // px
		this.insetColor = '#BBBBBB'; // CSS color
		this.shadow = true; // whether to display shadow
		this.shadowBlur = 15; // px
		this.shadowColor = 'rgba(0,0,0,0.2)'; // CSS color
		this.pointerColor = '#4C4C4C'; // px
		this.pointerBorderColor = '#FFFFFF'; // px
        this.pointerBorderWidth = 1; // px
        this.pointerThickness = 2; // px
		this.zIndex = 1000;
		this.container = null; // where to append the color picker (BODY element by default)


		for (var opt in options) {
			if (options.hasOwnProperty(opt)) {
				this[opt] = options[opt];
			}
		}


		this.hide = function () {
			if (isPickerOwner()) {
				detachPicker();
			}
		};


		this.show = function () {
			drawPicker();
		};


		this.redraw = function () {
			if (isPickerOwner()) {
				drawPicker();
			}
		};


		this.importColor = function () {
			if (!this.valueElement) {
				this.exportColor();
			} else {
				if (jsc.isElementType(this.valueElement, 'input')) {
					if (!this.refine) {
						if (!this.fromString(this.valueElement.value, jsc.leaveValue)) {
							if (this.styleElement) {
								this.styleElement.style.backgroundImage = this.styleElement._jscOrigStyle.backgroundImage;
								this.styleElement.style.backgroundColor = this.styleElement._jscOrigStyle.backgroundColor;
								this.styleElement.style.color = this.styleElement._jscOrigStyle.color;
							}
							this.exportColor(jsc.leaveValue | jsc.leaveStyle);
						}
					} else if (!this.required && /^\s*$/.test(this.valueElement.value)) {
						this.valueElement.value = '';
						if (this.styleElement) {
							this.styleElement.style.backgroundImage = this.styleElement._jscOrigStyle.backgroundImage;
							this.styleElement.style.backgroundColor = this.styleElement._jscOrigStyle.backgroundColor;
							this.styleElement.style.color = this.styleElement._jscOrigStyle.color;
						}
						this.exportColor(jsc.leaveValue | jsc.leaveStyle);

					} else if (this.fromString(this.valueElement.value)) {
						// managed to import color successfully from the value -> OK, don't do anything
					} else {
						this.exportColor();
					}
				} else {
					// not an input element -> doesn't have any value
					this.exportColor();
				}
			}
		};


		this.exportColor = function (flags) {
			if (!(flags & jsc.leaveValue) && this.valueElement) {
				var value = this.toString();
				if (this.uppercase) { value = value.toUpperCase(); }
				if (this.hash) { value = '#' + value; }

				if (jsc.isElementType(this.valueElement, 'input')) {
					this.valueElement.value = value;
				} else {
					this.valueElement.innerHTML = value;
				}
			}
			if (!(flags & jsc.leaveStyle)) {
				if (this.styleElement) {
					this.styleElement.style.backgroundImage = 'none';
					this.styleElement.style.backgroundColor = '#' + this.toString();
					this.styleElement.style.color = this.isLight() ? '#000' : '#FFF';
				}
			}
			if (!(flags & jsc.leavePad) && isPickerOwner()) {
				redrawPad();
			}
			if (!(flags & jsc.leaveSld) && isPickerOwner()) {
				redrawSld();
			}
		};


		// h: 0-360
		// s: 0-100
		// v: 0-100
		//
		this.fromHSV = function (h, s, v, flags) { // null = don't change
			if (h !== null) {
				if (isNaN(h)) { return false; }
				h = Math.max(0, Math.min(360, h));
			}
			if (s !== null) {
				if (isNaN(s)) { return false; }
				s = Math.max(0, Math.min(100, this.maxS, s), this.minS);
			}
			if (v !== null) {
				if (isNaN(v)) { return false; }
				v = Math.max(0, Math.min(100, this.maxV, v), this.minV);
			}

			this.rgb = HSV_RGB(
				h===null ? this.hsv[0] : (this.hsv[0]=h),
				s===null ? this.hsv[1] : (this.hsv[1]=s),
				v===null ? this.hsv[2] : (this.hsv[2]=v)
			);

			this.exportColor(flags);
		};


		// r: 0-255
		// g: 0-255
		// b: 0-255
		//
		this.fromRGB = function (r, g, b, flags) { // null = don't change
			if (r !== null) {
				if (isNaN(r)) { return false; }
				r = Math.max(0, Math.min(255, r));
			}
			if (g !== null) {
				if (isNaN(g)) { return false; }
				g = Math.max(0, Math.min(255, g));
			}
			if (b !== null) {
				if (isNaN(b)) { return false; }
				b = Math.max(0, Math.min(255, b));
			}

			var hsv = RGB_HSV(
				r===null ? this.rgb[0] : r,
				g===null ? this.rgb[1] : g,
				b===null ? this.rgb[2] : b
			);
			if (hsv[0] !== null) {
				this.hsv[0] = Math.max(0, Math.min(360, hsv[0]));
			}
			if (hsv[2] !== 0) {
				this.hsv[1] = hsv[1]===null ? null : Math.max(0, this.minS, Math.min(100, this.maxS, hsv[1]));
			}
			this.hsv[2] = hsv[2]===null ? null : Math.max(0, this.minV, Math.min(100, this.maxV, hsv[2]));

			// update RGB according to final HSV, as some values might be trimmed
			var rgb = HSV_RGB(this.hsv[0], this.hsv[1], this.hsv[2]);
			this.rgb[0] = rgb[0];
			this.rgb[1] = rgb[1];
			this.rgb[2] = rgb[2];

			this.exportColor(flags);
		};


		this.fromString = function (str, flags) {
			var m;
			if (m = str.match(/^\W*([0-9A-F]{3}([0-9A-F]{3})?)\W*$/i)) {
				// HEX notation
				//

				if (m[1].length === 6) {
					// 6-char notation
					this.fromRGB(
						parseInt(m[1].substr(0,2),16),
						parseInt(m[1].substr(2,2),16),
						parseInt(m[1].substr(4,2),16),
						flags
					);
				} else {
					// 3-char notation
					this.fromRGB(
						parseInt(m[1].charAt(0) + m[1].charAt(0),16),
						parseInt(m[1].charAt(1) + m[1].charAt(1),16),
						parseInt(m[1].charAt(2) + m[1].charAt(2),16),
						flags
					);
				}
				return true;

			} else if (m = str.match(/^\W*rgba?\(([^)]*)\)\W*$/i)) {
				var params = m[1].split(',');
				var re = /^\s*(\d*)(\.\d+)?\s*$/;
				var mR, mG, mB;
				if (
					params.length >= 3 &&
					(mR = params[0].match(re)) &&
					(mG = params[1].match(re)) &&
					(mB = params[2].match(re))
				) {
					var r = parseFloat((mR[1] || '0') + (mR[2] || ''));
					var g = parseFloat((mG[1] || '0') + (mG[2] || ''));
					var b = parseFloat((mB[1] || '0') + (mB[2] || ''));
					this.fromRGB(r, g, b, flags);
					return true;
				}
			}
			return false;
		};


		this.toString = function () {
			return (
				(0x100 | Math.round(this.rgb[0])).toString(16).substr(1) +
				(0x100 | Math.round(this.rgb[1])).toString(16).substr(1) +
				(0x100 | Math.round(this.rgb[2])).toString(16).substr(1)
			);
		};


		this.toHEXString = function () {
			return '#' + this.toString().toUpperCase();
		};


		this.toRGBString = function () {
			return ('rgb(' +
				Math.round(this.rgb[0]) + ',' +
				Math.round(this.rgb[1]) + ',' +
				Math.round(this.rgb[2]) + ')'
			);
		};


		this.isLight = function () {
			return (
				0.213 * this.rgb[0] +
				0.715 * this.rgb[1] +
				0.072 * this.rgb[2] >
				255 / 2
			);
		};


		this._processParentElementsInDOM = function () {
			if (this._linkedElementsProcessed) { return; }
			this._linkedElementsProcessed = true;

			var elm = this.targetElement;
			do {
				// If the target element or one of its parent nodes has fixed position,
				// then use fixed positioning instead
				//
				// Note: In Firefox, getComputedStyle returns null in a hidden iframe,
				// that's why we need to check if the returned style object is non-empty
				var currStyle = jsc.getStyle(elm);
				if (currStyle && currStyle.position.toLowerCase() === 'fixed') {
					this.fixed = true;
				}

				if (elm !== this.targetElement) {
					// Ensure to attach onParentScroll only once to each parent element
					// (multiple targetElements can share the same parent nodes)
					//
					// Note: It's not just offsetParents that can be scrollable,
					// that's why we loop through all parent nodes
					if (!elm._jscEventsAttached) {
						jsc.attachEvent(elm, 'scroll', jsc.onParentScroll);
						elm._jscEventsAttached = true;
					}
				}
			} while ((elm = elm.parentNode) && !jsc.isElementType(elm, 'body'));
		};


		// r: 0-255
		// g: 0-255
		// b: 0-255
		//
		// returns: [ 0-360, 0-100, 0-100 ]
		//
		function RGB_HSV (r, g, b) {
			r /= 255;
			g /= 255;
			b /= 255;
			var n = Math.min(Math.min(r,g),b);
			var v = Math.max(Math.max(r,g),b);
			var m = v - n;
			if (m === 0) { return [ null, 0, 100 * v ]; }
			var h = r===n ? 3+(b-g)/m : (g===n ? 5+(r-b)/m : 1+(g-r)/m);
			return [
				60 * (h===6?0:h),
				100 * (m/v),
				100 * v
			];
		}


		// h: 0-360
		// s: 0-100
		// v: 0-100
		//
		// returns: [ 0-255, 0-255, 0-255 ]
		//
		function HSV_RGB (h, s, v) {
			var u = 255 * (v / 100);

			if (h === null) {
				return [ u, u, u ];
			}

			h /= 60;
			s /= 100;

			var i = Math.floor(h);
			var f = i%2 ? h-i : 1-(h-i);
			var m = u * (1 - s);
			var n = u * (1 - s * f);
			switch (i) {
				case 6:
				case 0: return [u,n,m];
				case 1: return [n,u,m];
				case 2: return [m,u,n];
				case 3: return [m,n,u];
				case 4: return [n,m,u];
				case 5: return [u,m,n];
			}
		}


		function detachPicker () {
			jsc.unsetClass(THIS.targetElement, THIS.activeClass);
			jsc.picker.wrap.parentNode.removeChild(jsc.picker.wrap);
			delete jsc.picker.owner;
		}


		function drawPicker () {

			// At this point, when drawing the picker, we know what the parent elements are
			// and we can do all related DOM operations, such as registering events on them
			// or checking their positioning
			THIS._processParentElementsInDOM();

			if (!jsc.picker) {
				jsc.picker = {
					owner: null,
					wrap : document.createElement('div'),
					box : document.createElement('div'),
					boxS : document.createElement('div'), // shadow area
					boxB : document.createElement('div'), // border
					pad : document.createElement('div'),
					padB : document.createElement('div'), // border
					padM : document.createElement('div'), // mouse/touch area
					padPal : jsc.createPalette(),
					cross : document.createElement('div'),
					crossBY : document.createElement('div'), // border Y
					crossBX : document.createElement('div'), // border X
					crossLY : document.createElement('div'), // line Y
					crossLX : document.createElement('div'), // line X
					sld : document.createElement('div'),
					sldB : document.createElement('div'), // border
					sldM : document.createElement('div'), // mouse/touch area
					sldGrad : jsc.createSliderGradient(),
					sldPtrS : document.createElement('div'), // slider pointer spacer
					sldPtrIB : document.createElement('div'), // slider pointer inner border
					sldPtrMB : document.createElement('div'), // slider pointer middle border
					sldPtrOB : document.createElement('div'), // slider pointer outer border
					btn : document.createElement('div'),
					btnT : document.createElement('span') // text
				};

				jsc.picker.pad.appendChild(jsc.picker.padPal.elm);
				jsc.picker.padB.appendChild(jsc.picker.pad);
				jsc.picker.cross.appendChild(jsc.picker.crossBY);
				jsc.picker.cross.appendChild(jsc.picker.crossBX);
				jsc.picker.cross.appendChild(jsc.picker.crossLY);
				jsc.picker.cross.appendChild(jsc.picker.crossLX);
				jsc.picker.padB.appendChild(jsc.picker.cross);
				jsc.picker.box.appendChild(jsc.picker.padB);
				jsc.picker.box.appendChild(jsc.picker.padM);

				jsc.picker.sld.appendChild(jsc.picker.sldGrad.elm);
				jsc.picker.sldB.appendChild(jsc.picker.sld);
				jsc.picker.sldB.appendChild(jsc.picker.sldPtrOB);
				jsc.picker.sldPtrOB.appendChild(jsc.picker.sldPtrMB);
				jsc.picker.sldPtrMB.appendChild(jsc.picker.sldPtrIB);
				jsc.picker.sldPtrIB.appendChild(jsc.picker.sldPtrS);
				jsc.picker.box.appendChild(jsc.picker.sldB);
				jsc.picker.box.appendChild(jsc.picker.sldM);

				jsc.picker.btn.appendChild(jsc.picker.btnT);
				jsc.picker.box.appendChild(jsc.picker.btn);

				jsc.picker.boxB.appendChild(jsc.picker.box);
				jsc.picker.wrap.appendChild(jsc.picker.boxS);
				jsc.picker.wrap.appendChild(jsc.picker.boxB);
			}

			var p = jsc.picker;

			var displaySlider = !!jsc.getSliderComponent(THIS);
			var dims = jsc.getPickerDims(THIS);
			var crossOuterSize = (2 * THIS.pointerBorderWidth + THIS.pointerThickness + 2 * THIS.crossSize);
			var padToSliderPadding = jsc.getPadToSliderPadding(THIS);
			var borderRadius = Math.min(
				THIS.borderRadius,
				Math.round(THIS.padding * Math.PI)); // px
			var padCursor = 'crosshair';

			// wrap
			p.wrap.style.clear = 'both';
			p.wrap.style.width = (dims[0] + 2 * THIS.borderWidth) + 'px';
			p.wrap.style.height = (dims[1] + 2 * THIS.borderWidth) + 'px';
			p.wrap.style.zIndex = THIS.zIndex;

			// picker
			p.box.style.width = dims[0] + 'px';
			p.box.style.height = dims[1] + 'px';

			p.boxS.style.position = 'absolute';
			p.boxS.style.left = '0';
			p.boxS.style.top = '0';
			p.boxS.style.width = '100%';
			p.boxS.style.height = '100%';
			jsc.setBorderRadius(p.boxS, borderRadius + 'px');

			// picker border
			p.boxB.style.position = 'relative';
			p.boxB.style.border = THIS.borderWidth + 'px solid';
			p.boxB.style.borderColor = THIS.borderColor;
			p.boxB.style.background = THIS.backgroundColor;
			jsc.setBorderRadius(p.boxB, borderRadius + 'px');

			// IE hack:
			// If the element is transparent, IE will trigger the event on the elements under it,
			// e.g. on Canvas or on elements with border
			p.padM.style.background =
			p.sldM.style.background =
				'#FFF';
			jsc.setStyle(p.padM, 'opacity', '0');
			jsc.setStyle(p.sldM, 'opacity', '0');

			// pad
			p.pad.style.position = 'relative';
			p.pad.style.width = THIS.width + 'px';
			p.pad.style.height = THIS.height + 'px';

			// pad palettes (HSV and HVS)
			p.padPal.draw(THIS.width, THIS.height, jsc.getPadYComponent(THIS));

			// pad border
			p.padB.style.position = 'absolute';
			p.padB.style.left = THIS.padding + 'px';
			p.padB.style.top = THIS.padding + 'px';
			p.padB.style.border = THIS.insetWidth + 'px solid';
			p.padB.style.borderColor = THIS.insetColor;

			// pad mouse area
			p.padM._jscInstance = THIS;
			p.padM._jscControlName = 'pad';
			p.padM.style.position = 'absolute';
			p.padM.style.left = '0';
			p.padM.style.top = '0';
			p.padM.style.width = (THIS.padding + 2 * THIS.insetWidth + THIS.width + padToSliderPadding / 2) + 'px';
			p.padM.style.height = dims[1] + 'px';
			p.padM.style.cursor = padCursor;

			// pad cross
			p.cross.style.position = 'absolute';
			p.cross.style.left =
			p.cross.style.top =
				'0';
			p.cross.style.width =
			p.cross.style.height =
				crossOuterSize + 'px';

			// pad cross border Y and X
			p.crossBY.style.position =
			p.crossBX.style.position =
				'absolute';
			p.crossBY.style.background =
			p.crossBX.style.background =
				THIS.pointerBorderColor;
			p.crossBY.style.width =
			p.crossBX.style.height =
				(2 * THIS.pointerBorderWidth + THIS.pointerThickness) + 'px';
			p.crossBY.style.height =
			p.crossBX.style.width =
				crossOuterSize + 'px';
			p.crossBY.style.left =
			p.crossBX.style.top =
				(Math.floor(crossOuterSize / 2) - Math.floor(THIS.pointerThickness / 2) - THIS.pointerBorderWidth) + 'px';
			p.crossBY.style.top =
			p.crossBX.style.left =
				'0';

			// pad cross line Y and X
			p.crossLY.style.position =
			p.crossLX.style.position =
				'absolute';
			p.crossLY.style.background =
			p.crossLX.style.background =
				THIS.pointerColor;
			p.crossLY.style.height =
			p.crossLX.style.width =
				(crossOuterSize - 2 * THIS.pointerBorderWidth) + 'px';
			p.crossLY.style.width =
			p.crossLX.style.height =
				THIS.pointerThickness + 'px';
			p.crossLY.style.left =
			p.crossLX.style.top =
				(Math.floor(crossOuterSize / 2) - Math.floor(THIS.pointerThickness / 2)) + 'px';
			p.crossLY.style.top =
			p.crossLX.style.left =
				THIS.pointerBorderWidth + 'px';

			// slider
			p.sld.style.overflow = 'hidden';
			p.sld.style.width = THIS.sliderSize + 'px';
			p.sld.style.height = THIS.height + 'px';

			// slider gradient
			p.sldGrad.draw(THIS.sliderSize, THIS.height, '#000', '#000');

			// slider border
			p.sldB.style.display = displaySlider ? 'block' : 'none';
			p.sldB.style.position = 'absolute';
			p.sldB.style.right = THIS.padding + 'px';
			p.sldB.style.top = THIS.padding + 'px';
			p.sldB.style.border = THIS.insetWidth + 'px solid';
			p.sldB.style.borderColor = THIS.insetColor;

			// slider mouse area
			p.sldM._jscInstance = THIS;
			p.sldM._jscControlName = 'sld';
			p.sldM.style.display = displaySlider ? 'block' : 'none';
			p.sldM.style.position = 'absolute';
			p.sldM.style.right = '0';
			p.sldM.style.top = '0';
			p.sldM.style.width = (THIS.sliderSize + padToSliderPadding / 2 + THIS.padding + 2 * THIS.insetWidth) + 'px';
			p.sldM.style.height = dims[1] + 'px';
			p.sldM.style.cursor = 'default';

			// slider pointer inner and outer border
			p.sldPtrIB.style.border =
			p.sldPtrOB.style.border =
				THIS.pointerBorderWidth + 'px solid ' + THIS.pointerBorderColor;

			// slider pointer outer border
			p.sldPtrOB.style.position = 'absolute';
			p.sldPtrOB.style.left = -(2 * THIS.pointerBorderWidth + THIS.pointerThickness) + 'px';
			p.sldPtrOB.style.top = '0';

			// slider pointer middle border
			p.sldPtrMB.style.border = THIS.pointerThickness + 'px solid ' + THIS.pointerColor;

			// slider pointer spacer
			p.sldPtrS.style.width = THIS.sliderSize + 'px';
			p.sldPtrS.style.height = sliderPtrSpace + 'px';

			// the Close button
			function setBtnBorder () {
				var insetColors = THIS.insetColor.split(/\s+/);
				var outsetColor = insetColors.length < 2 ? insetColors[0] : insetColors[1] + ' ' + insetColors[0] + ' ' + insetColors[0] + ' ' + insetColors[1];
				p.btn.style.borderColor = outsetColor;
			}
			p.btn.style.display = THIS.closable ? 'block' : 'none';
			p.btn.style.position = 'absolute';
			p.btn.style.left = THIS.padding + 'px';
			p.btn.style.bottom = THIS.padding + 'px';
			p.btn.style.padding = '0 15px';
			p.btn.style.height = THIS.buttonHeight + 'px';
			p.btn.style.border = THIS.insetWidth + 'px solid';
			setBtnBorder();
			p.btn.style.color = THIS.buttonColor;
			p.btn.style.font = '12px sans-serif';
			p.btn.style.textAlign = 'center';
			try {
				p.btn.style.cursor = 'pointer';
			} catch(eOldIE) {
				p.btn.style.cursor = 'hand';
			}
			p.btn.onmousedown = function () {
				THIS.hide();
			};
			p.btnT.style.lineHeight = THIS.buttonHeight + 'px';
			p.btnT.innerHTML = '';
			p.btnT.appendChild(document.createTextNode(THIS.closeText));

			// place pointers
			redrawPad();
			redrawSld();

			// If we are changing the owner without first closing the picker,
			// make sure to first deal with the old owner
			if (jsc.picker.owner && jsc.picker.owner !== THIS) {
				jsc.unsetClass(jsc.picker.owner.targetElement, THIS.activeClass);
			}

			// Set the new picker owner
			jsc.picker.owner = THIS;

			// The redrawPosition() method needs picker.owner to be set, that's why we call it here,
			// after setting the owner
			if (jsc.isElementType(container, 'body')) {
				jsc.redrawPosition();
			} else {
				jsc._drawPosition(THIS, 0, 0, 'relative', false);
			}

			if (p.wrap.parentNode != container) {
				container.appendChild(p.wrap);
			}

			jsc.setClass(THIS.targetElement, THIS.activeClass);
		}


		function redrawPad () {
			// redraw the pad pointer
			switch (jsc.getPadYComponent(THIS)) {
			case 's': var yComponent = 1; break;
			case 'v': var yComponent = 2; break;
			}
			var x = Math.round((THIS.hsv[0] / 360) * (THIS.width - 1));
			var y = Math.round((1 - THIS.hsv[yComponent] / 100) * (THIS.height - 1));
			var crossOuterSize = (2 * THIS.pointerBorderWidth + THIS.pointerThickness + 2 * THIS.crossSize);
			var ofs = -Math.floor(crossOuterSize / 2);
			jsc.picker.cross.style.left = (x + ofs) + 'px';
			jsc.picker.cross.style.top = (y + ofs) + 'px';

			// redraw the slider
			switch (jsc.getSliderComponent(THIS)) {
			case 's':
				var rgb1 = HSV_RGB(THIS.hsv[0], 100, THIS.hsv[2]);
				var rgb2 = HSV_RGB(THIS.hsv[0], 0, THIS.hsv[2]);
				var color1 = 'rgb(' +
					Math.round(rgb1[0]) + ',' +
					Math.round(rgb1[1]) + ',' +
					Math.round(rgb1[2]) + ')';
				var color2 = 'rgb(' +
					Math.round(rgb2[0]) + ',' +
					Math.round(rgb2[1]) + ',' +
					Math.round(rgb2[2]) + ')';
				jsc.picker.sldGrad.draw(THIS.sliderSize, THIS.height, color1, color2);
				break;
			case 'v':
				var rgb = HSV_RGB(THIS.hsv[0], THIS.hsv[1], 100);
				var color1 = 'rgb(' +
					Math.round(rgb[0]) + ',' +
					Math.round(rgb[1]) + ',' +
					Math.round(rgb[2]) + ')';
				var color2 = '#000';
				jsc.picker.sldGrad.draw(THIS.sliderSize, THIS.height, color1, color2);
				break;
			}
		}


		function redrawSld () {
			var sldComponent = jsc.getSliderComponent(THIS);
			if (sldComponent) {
				// redraw the slider pointer
				switch (sldComponent) {
				case 's': var yComponent = 1; break;
				case 'v': var yComponent = 2; break;
				}
				var y = Math.round((1 - THIS.hsv[yComponent] / 100) * (THIS.height - 1));
				jsc.picker.sldPtrOB.style.top = (y - (2 * THIS.pointerBorderWidth + THIS.pointerThickness) - Math.floor(sliderPtrSpace / 2)) + 'px';
			}
		}


		function isPickerOwner () {
			return jsc.picker && jsc.picker.owner === THIS;
		}


		function blurValue () {
			THIS.importColor();
		}


		// Find the target element
		if (typeof targetElement === 'string') {
			var id = targetElement;
			var elm = document.getElementById(id);
			if (elm) {
				this.targetElement = elm;
			} else {
				jsc.warn('Could not find target element with ID \'' + id + '\'');
			}
		} else if (targetElement) {
			this.targetElement = targetElement;
		} else {
			jsc.warn('Invalid target element: \'' + targetElement + '\'');
		}

		if (this.targetElement._jscLinkedInstance) {
			jsc.warn('Cannot link jscolor twice to the same element. Skipping.');
			return;
		}
		this.targetElement._jscLinkedInstance = this;

		// Find the value element
		this.valueElement = jsc.fetchElement(this.valueElement);
		// Find the style element
		this.styleElement = jsc.fetchElement(this.styleElement);

		var THIS = this;
		var container =
			this.container ?
			jsc.fetchElement(this.container) :
			document.getElementsByTagName('body')[0];
		var sliderPtrSpace = 3; // px

		// For BUTTON elements it's important to stop them from sending the form when clicked
		// (e.g. in Safari)
		if (jsc.isElementType(this.targetElement, 'button')) {
			if (this.targetElement.onclick) {
				var origCallback = this.targetElement.onclick;
				this.targetElement.onclick = function (evt) {
					origCallback.call(this, evt);
					return false;
				};
			} else {
				this.targetElement.onclick = function () { return false; };
			}
		}

		/*
		var elm = this.targetElement;
		do {
			// If the target element or one of its offsetParents has fixed position,
			// then use fixed positioning instead
			//
			// Note: In Firefox, getComputedStyle returns null in a hidden iframe,
			// that's why we need to check if the returned style object is non-empty
			var currStyle = jsc.getStyle(elm);
			if (currStyle && currStyle.position.toLowerCase() === 'fixed') {
				this.fixed = true;
			}

			if (elm !== this.targetElement) {
				// attach onParentScroll so that we can recompute the picker position
				// when one of the offsetParents is scrolled
				if (!elm._jscEventsAttached) {
					jsc.attachEvent(elm, 'scroll', jsc.onParentScroll);
					elm._jscEventsAttached = true;
				}
			}
		} while ((elm = elm.offsetParent) && !jsc.isElementType(elm, 'body'));
		*/

		// valueElement
		if (this.valueElement) {
			if (jsc.isElementType(this.valueElement, 'input')) {
				var updateField = function () {
					THIS.fromString(THIS.valueElement.value, jsc.leaveValue);
					jsc.dispatchFineChange(THIS);
				};
				jsc.attachEvent(this.valueElement, 'keyup', updateField);
				jsc.attachEvent(this.valueElement, 'input', updateField);
				jsc.attachEvent(this.valueElement, 'blur', blurValue);
				this.valueElement.setAttribute('autocomplete', 'off');
			}
		}

		// styleElement
		if (this.styleElement) {
			this.styleElement._jscOrigStyle = {
				backgroundImage : this.styleElement.style.backgroundImage,
				backgroundColor : this.styleElement.style.backgroundColor,
				color : this.styleElement.style.color
			};
		}

		if (this.value) {
			// Try to set the color from the .value option and if unsuccessful,
			// export the current color
			this.fromString(this.value) || this.exportColor();
		} else {
			this.importColor();
		}
	}

};


//================================
// Public properties and methods
//================================


// By default, search for all elements with class="jscolor" and install a color picker on them.
//
// You can change what class name will be looked for by setting the property jscolor.lookupClass
// anywhere in your HTML document. To completely disable the automatic lookup, set it to null.
//
jsc.jscolor.lookupClass = 'jscolor';


jsc.jscolor.installByClassName = function (className) {
	var inputElms = document.getElementsByTagName('input');
	var buttonElms = document.getElementsByTagName('button');

	jsc.tryInstallOnElements(inputElms, className);
	jsc.tryInstallOnElements(buttonElms, className);
};


jsc.register();


return jsc.jscolor;


})(); }
$(document).ready(function() {
    shopForm();
});
$(document).on('change', '.logo-language-js', function() {
    var lang_id = $(this).val();
    shopImages('logo', 0, lang_id);
});
$(document).on('change', '.banner-language-js', function() {
    var lang_id = $(this).val();
    var slide_screen = $(".prefDimensions-js").val();
    shopImages('banner', slide_screen, lang_id);
});
$(document).on('change','.prefDimensions-js',function(){
	var slide_screen = $(this).val();
	var lang_id = $(".banner-language-js").val();
	shopImages('banner', slide_screen, lang_id);
});
$(document).on('change', '.bg-language-js', function() {
    var lang_id = $(this).val();
    shopImages('bg', 0, lang_id);
});
$(document).on('change', '.collection-language-js', function() {
    var lang_id = $(this).val();
    var scollection_id = document.frmCollectionMedia.scollection_id.value;
    shopCollectionImages(scollection_id, lang_id);
});
(function() {
    var runningAjaxReq = false;
    var dv = '#shopFormBlock';
    var dvt = '#shopFormChildBlock';

    checkRunningAjax = function() {
        if (runningAjaxReq == true) {
            //console.log(runningAjaxMsg);
            return;
        }
        runningAjaxReq = true;
    };

    goToCategoryBannerSrchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmCategoryBannerSrchPaging;
        $(frm.page).val(page);
        searchCategoryBanners(frm);
    };

    categoryBanners = function() {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'searchCategoryBanners'), '', function(t) {
            $(dv).html(t);
        });
    };

    addCategoryBanner = function(prodCatId) {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('Seller', 'addCategoryBanner', [prodCatId]), '', function(t) {
                $.facebox(t, 'faceboxWidth');
            });
        });
    };

    /* categoryBannerLangForm = function( prodCatId, langId ){
    	$.facebox(function() {
    		fcom.ajax(fcom.makeUrl('Seller', 'categoryBannerLangForm',[prodCatId, langId]), '', function(t) {
    			$.facebox(t,'faceboxWidth');
    		});
    	});
    } */

    searchCategoryBanners = function(frm) {
        /*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
        var data = fcom.frmData(frm);
        /*]*/
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'searchCategoryBanners'), data, function(res) {
            $(dv).html(res);
        });
    };

    reloadCategoryBannerList = function() {
        searchCategoryBanners(document.frmCategoryBannerSrchPaging);
    };

    removeCategoryBanner = function(prodCatId, lang_id) {
        var agree = confirm(langLbl.confirmRemove);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'removeCategoryBanner', [prodCatId, lang_id]), '', function(t) {
            reloadCategoryBannerList();
            addCategoryBanner(prodCatId);
        });
    };

    shopForm = function() {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'shopForm'), '', function(t) {
            $(dv).html(t);
            jscolor.installByClassName("jscolor");
        });
    };

    setupShop = function(frm) {
        if (!$(frm).validate()) return;
        checkRunningAjax();
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupShop'), data, function(t) {
            runningAjaxReq = false;
            if (t.langId > 0) {
                shopLangForm(t.shopId, t.langId);
                return;
            }

            shopForm();
            return;
        });
    };

    shopLangForm = function(shopId, langId) {
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('Seller', 'shopLangForm', [shopId, langId]), '', function(t) {
            $(dv).html(t);
            fcom.setEditorLayout(langId);
            var frm = $(dv + ' form')[0];
            var validator = $(frm).validation({
                errordisplay: 3
            });
            $(frm).submit(function(e) {
                e.preventDefault();
                if (validator.validate() == false) {
                    return;
                }
                var data = fcom.frmData(frm);
                fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupShopLang'), data, function(t) {
                    runningAjaxReq = false;
                    $.mbsmessage.close();
                    if (t.langId > 0 && t.shopId > 0) {
                        shopLangForm(t.shopId, t.langId);
                        return;
                    }
                    returnAddressForm();
                });
            });
        });
    };

    setupShopLang = function(frm) {
        if (!$(frm).validate()) return;
        checkRunningAjax();
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupShopLang'), data, function(t) {
            runningAjaxReq = false;
            $.mbsmessage.close();
            if (t.langId > 0 && t.shopId > 0) {
                shopLangForm(t.shopId, t.langId);
                return;
            }
            shopForm();
        });
    };

    shopMediaForm = function(el) {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'shopMediaForm'), '', function(t) {
            $(dv).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
            shopImages('logo');
            shopImages('banner',1);
            shopImages('bg');
        });
    };

    shopImages = function(imageType, slide_screen, lang_id) {
        fcom.ajax(fcom.makeUrl('Seller', 'shopImages', [imageType, lang_id, slide_screen]), '', function(t) {
            if (imageType == 'logo') {
                $('#logo-image-listing').html(t);
            } else if (imageType == 'banner') {
                $('#banner-image-listing').html(t);
            } else {
                $('#bg-image-listing').html(t);
            }
        });
    };

    shopTemplates = function(el) {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'shopTemplate'), '', function(t) {
            $(dv).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
        });
    };
    themeColor = function(el) {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'shopThemeColor'), '', function(t) {
            $(dv).html(t);
            jscolor.installByClassName("jscolor");

        });
    };

    setTemplate = function(ltemplateId) {
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setTemplate', [ltemplateId]), '', function(t) {
            shopTemplates();
        });
    };

    /* getCountryStates = function(countryId,stateId,dv){
    	fcom.ajax(fcom.makeUrl('Seller','getStates',[countryId,stateId]),'',function(res){
    		$(dv).empty();
    		$(dv).append(res);
    	});
    }; */

    setUpThemeColor = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('seller', 'setupThemeColor'), data, function(t) {
            $.mbsmessage.close();
        });
    };

    removeShopImage = function(BannerId, langId, imageType, slide_screen) {
        var agree = confirm(langLbl.confirmRemove);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'removeShopImage', [BannerId, langId, imageType, slide_screen]), '', function(t) {
            shopImages(imageType, slide_screen, langId);
        });
    };

    deleteShopCollection = function(scollection_id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        fcom.ajax(fcom.makeUrl('Seller', 'deleteShopCollection', [scollection_id]), '', function(res) {
            searchShopCollections();
        });
    };

    shopCollections = function(el) {
        $(dv).html(fcom.getLoader());
        // console.log($(el).parent());
        fcom.ajax(fcom.makeUrl('Seller', 'shopCollections'), '', function(t) {
            $(dv).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
            searchShopCollections();
        });
    };

    searchShopCollections = function(el) {
        $(dvt).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'searchShopCollections'), '', function(t) {
            $(dvt).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
        });
    };

    shopCollectionProducts = function(el) {
        $(dv).html(fcom.getLoader());
        // console.log($(el).parent());
        fcom.ajax(fcom.makeUrl('Seller', 'shopCollection'), '', function(t) {
            $(dv).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
            getShopCollectionGeneralForm();
        });
    };

    getShopCollectionGeneralForm = function(scollection_id) {
        fcom.ajax(fcom.makeUrl('Seller', 'shopCollectionGeneralForm', [scollection_id]), '', function(t) {
            $(dvt).html(t);
        });
    };

    setupShopCollection = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('seller', 'setupShopCollection'), data, function(t) {
            $.mbsmessage.close();
            if (t.langId > 0) {
                editShopCollectionLangForm(t.collection_id, t.langId);
                return;
            }

        });
    };

    setupShopCollectionlangForm = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('seller', 'setupShopCollectionLang'), data, function(t) {
            $.mbsmessage.close();
            if (t.langId > 0) {
                editShopCollectionLangForm(t.scollection_id, t.langId);
            }
            if (t.openCollectionLinkForm) {
                sellerCollectionProducts(t.scollection_id);
                return;
            }
        });

    };

    editShopCollectionLangForm = function(scollection_id, langId) {
        if (typeof(scollection_id) == "undefined" || scollection_id < 0) {
            return false;
        }
        if (typeof(langId) == "undefined" || langId < 0) {
            return false;
        }
        fcom.ajax(fcom.makeUrl('seller', 'shopCollectionLangForm', [scollection_id, langId]), '', function(t) {
            $(dvt).html(t);
        });
    };

    sellerCollectionProducts = function(scollection_id) {
        $(dvt).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'sellerCollectionProductLinkFrm', [scollection_id]), '', function(t) {
            $(dvt).html(t);
            bindAutoComplete();
        });
    };

    setUpSellerCollectionProductLinks = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpSellerCollectionProductLinks'), data, function(t) {
            $.mbsmessage.close();
        });
    };

    resetDefaultCurrentTemplate = function() {
        var agree = confirm(langLbl.confirmReset);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'resetDefaultThemeColor'), '', function(t) {
            $.mbsmessage.close();
            themeColor();
        });
    };

    returnAddressForm = function() {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'returnAddressForm'), '', function(t) {
            $(dv).html(t);
        });
    };

    setReturnAddress = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setReturnAddress'), data, function(t) {
            returnAddressLangForm(t.langId);
        });
    };

    returnAddressLangForm = function(langId) {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'returnAddressLangForm', [langId]), '', function(t) {
            $(dv).html(t);
        });
    };

    setReturnAddressLang = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setReturnAddressLang'), data, function(t) {
            if (t.langId) {
                returnAddressLangForm(t.langId);
            } else {
                returnAddressForm();
            }
        });
    };

    collectionMediaForm = function(el, scollection_id) {
        $(dvt).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'shopCollectionMediaForm', [scollection_id]), '', function(t) {
            $(dvt).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
            shopCollectionImages(scollection_id);
        });
    };

    shopCollectionImages = function(scollection_id, lang_id) {
        fcom.ajax(fcom.makeUrl('Seller', 'shopCollectionImages', [scollection_id, lang_id]), '', function(t) {
            $('#imageListing').html(t);
        });
    };

    removeCollectionImage = function(scollection_id, langId) {
        var agree = confirm(langLbl.confirmRemove);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'removeCollectionImage', [scollection_id, langId]), '', function(t) {
            shopCollectionImages(scollection_id, langId);
        });
    };

    toggleShopCollectionStatus = function(e, obj) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            e.preventDefault();
            return;
        }
        var scollection_id = parseInt(obj.value);
        if (scollection_id < 1) {
            return false;
        }
        data = 'scollection_id=' + scollection_id;
        fcom.ajax(fcom.makeUrl('Seller', 'changeShopCollectionStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                $.mbsmessage(ans.msg, true, 'alert--success');
            } else {
                $.mbsmessage(ans.msg, true, 'alert--danger');
            }
        });
    };

    toggleBulkCollectionStatues = function(status) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            return false;
        }
        $("#frmCollectionsListing input[name='collection_status']").val(status);
        $("#frmCollectionsListing").submit();
    };

    deleteSelectedCollection = function() {
        if (!confirm(langLbl.confirmDelete)) {
            return false;
        }
        $("#frmCollectionsListing").attr("action", fcom.makeUrl('Seller', 'deleteSelectedCollections')).submit();
    };

})();

function bindAutoComplete() {
    $("input[name='scp_selprod_id']").autocomplete({

        'source': function(request, response) {
            $.ajax({
                url: fcom.makeUrl('seller', 'autoCompleteProducts'),
                data: {
                    keyword: request,
                    fIsAjax: 1
                },
                dataType: 'json',
                type: 'post',
                success: function(json) {
                    response($.map(json, function(item) {

                        return {
                            label: item['name'] + '[' + item['product_identifier'] + ']',
                            value: item['id']
                        };
                    }));
                },
            });
        },
        'select': function(item) {
            $('input[name=\'scp_selprod_id\']').val('');
            $('#selprod-products' + item['value']).remove();
            $('#selprod-products ul ').append('<li id="selprod-products' + item['value'] + '"><i class="remove_link remove_param fa fa-remove"></i> ' + item['label'] + '<input type="hidden" name="product_ids[]" value="' + item['value'] + '" /></li>');
        }
    });
}
$(document).on('click', '.shopFile-Js', function() {
    var node = this;
    $('#form-upload').remove();
    var frmName = $(node).attr('data-frm');
    var fileType = $(node).attr('data-file_type');
    if ('frmShopLogo' == frmName) {
        var lang_id = document.frmShopLogo.lang_id.value;
        var imageType = 'logo';
    } else if ('frmShopBanner' == frmName) {
        var lang_id = document.frmShopBanner.lang_id.value;
        var slide_screen = document.frmShopBanner.slide_screen.value;
        var imageType = 'banner';
    } else {
        var lang_id = document.frmBackgroundImage.lang_id.value;
        var imageType = 'bg';
    }
    var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
    frm = frm.concat('<input type="file" name="file" />');
    frm = frm.concat('<input type="hidden" name="lang_id" value="' + lang_id + '">');
    frm = frm.concat('<input type="hidden" name="slide_screen" value="' + slide_screen + '">');
    frm = frm.concat('<input type="hidden" name="file_type" value="' + fileType + '"></form>');
    $('body').prepend(frm);
    $('#form-upload input[name=\'file\']').trigger('click');
    if (typeof timer != 'undefined') {
        clearInterval(timer);
    }
    timer = setInterval(function() {
        if ($('#form-upload input[name=\'file\']').val() != '') {
            clearInterval(timer);
            $val = $(node).val();
            $.ajax({
                url: fcom.makeUrl('Seller', 'uploadShopImages'),
                type: 'post',
                dataType: 'json',
                data: new FormData($('#form-upload')[0]),
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(node).val('Loading');
                },
                complete: function() {
                    $(node).val($val);
                },
                success: function(ans) {
                    $.mbsmessage.close();
                    $.systemMessage.close();
                    $('.text-danger').remove();
                    $('#input-field' + fileType).html(ans.msg);
                    if (ans.status == true) {
                        $.mbsmessage(ans.msg, true, 'alert--success');
                        $('#input-field' + fileType).removeClass('text-danger');
                        $('#input-field' + fileType).addClass('text-success');
                        $('#form-upload').remove();
                        shopImages(imageType, slide_screen, lang_id);
                    } else {
                        $.mbsmessage(ans.msg, true, 'alert--danger');
                        $('#input-field' + fileType).removeClass('text-success');
                        $('#input-field' + fileType).addClass('text-danger');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    }, 500);
});

$(document).on('click', '.catFile-Js', function() {
    var node = this;
    $('#form-upload').remove();
    var prodcat_id = document.frmCategoryMedia.prodcat_id.value;
    var lang_id = document.frmCategoryMedia.lang_id.value;
    var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
    frm = frm.concat('<input type="file" name="file" />');
    frm = frm.concat('<input type="hidden" name="prodcat_id" value="' + prodcat_id + '">');
    frm = frm.concat('<input type="hidden" name="lang_id" value="' + lang_id + '">');
    frm = frm.concat('</form>');
    $('body').prepend(frm);
    $('#form-upload input[name=\'file\']').trigger('click');
    if (typeof timer != 'undefined') {
        clearInterval(timer);
    }
    timer = setInterval(function() {
        if ($('#form-upload input[name=\'file\']').val() != '') {
            clearInterval(timer);
            $val = $(node).val();
            $.ajax({
                url: fcom.makeUrl('Seller', 'setupCategoryBanner'),
                type: 'post',
                dataType: 'json',
                data: new FormData($('#form-upload')[0]),
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(node).val('loading..');
                },
                complete: function() {
                    $(node).val($val);
                },
                success: function(ans) {
                    $.mbsmessage.close();
                    $.systemMessage.close();
                    //$.mbsmessage(ans.msg, true, 'alert--success');
                    var dv = '#mediaResponse';
                    $('.text-danger').remove();
                    if (ans.status == true) {
                        $.systemMessage(ans.msg, 'alert--success');
                        $(dv).removeClass('text-danger');
                        $(dv).addClass('text-success');
                        reloadCategoryBannerList();
                        addCategoryBanner(prodcat_id);
                    } else {
                        $.systemMessage(ans.msg, 'alert--danger');
                        $(dv).removeClass('text-success');
                        $(dv).addClass('text-danger');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    }, 500);

});

$(document).on('click', '.shopCollection-Js', function() {
    var node = this;
    $('#form-upload').remove();
    var scollection_id = document.frmCollectionMedia.scollection_id.value;
    var lang_id = document.frmCollectionMedia.lang_id.value;
    var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
    frm = frm.concat('<input type="file" name="file" />');
    frm = frm.concat('<input type="hidden" name="scollection_id" value="' + scollection_id + '">');
    frm = frm.concat('<input type="hidden" name="lang_id" value="' + lang_id + '">');
    frm = frm.concat('</form>');
    $('body').prepend(frm);
    $('#form-upload input[name=\'file\']').trigger('click');
    if (typeof timer != 'undefined') {
        clearInterval(timer);
    }
    timer = setInterval(function() {
        if ($('#form-upload input[name=\'file\']').val() != '') {
            clearInterval(timer);
            $val = $(node).val();
            $.ajax({
                url: fcom.makeUrl('Seller', 'uploadCollectionImage'),
                type: 'post',
                dataType: 'json',
                data: new FormData($('#form-upload')[0]),
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(node).val('loading..');
                },
                complete: function() {
                    $(node).val($val);
                },
                success: function(ans) {
                    $.mbsmessage.close();
                    $.systemMessage.close();
                    //$.mbsmessage(ans.msg, true, 'alert--success');
                    var dv = '#mediaResponse';
                    $('.text-danger').remove();
                    if (ans.status == true) {
                        $.systemMessage(ans.msg, 'alert--success');
                        $(dv).removeClass('text-danger');
                        $(dv).addClass('text-success');
                        shopCollectionImages(scollection_id, lang_id);
                    } else {
                        $.systemMessage(ans.msg, 'alert--danger');
                        $(dv).removeClass('text-success');
                        $(dv).addClass('text-danger');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    }, 500);

});
