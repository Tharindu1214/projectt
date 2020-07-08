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
/**
* multi-list.js is a jQuery plugin to turn a <ul> element into a multiple selectable list
*
* Author: Albert Gonzalez - http://www.albertgonzalez.coffee
* 
* Under a UNLICENSE (http://unlicense.org/)
*
*
* Init: $("<ul>").multiList();
* Remove: $("<ul>").multiList("remove");
*
* Get the selected values from the ul (the "value" attr from the li items): $("<ul>").multiList("getSelected");
* Get both the selected values and their full text field (the "value" attr from the li items and their text): $("<ul>").multiList("getSelectedFull");
*
* Get the unselected values from the ul (the "value" attr from the li items): $("<ul>").multiList("getUnselected");
* Get both the unselected values and their full text field (the "value" attr from the li items and their text): $("<ul>").multiList("getUnselectedFull");
*
* Select an element: $("<ul>").multiList("select", "<li> value");
* Unselect an element: $("<ul>").multiList("unselect", "<li> value");
*
* Select ALL the elements: $("<ul>").multiList("selectAll");
* Unelect ALL the elements: $("<ul>").multiList("unselectAll");
*
* Hide an element (accepts an ARRAY, this doesn't affect the selected/unselected property): $("<ul>").multiList("hide", "<li> value");
* Show an element (accepts an ARRAY): $("<ul>").multiList("show", "<li> value");
*
* Hide ALL the elements: $("<ul>").multiList("hideAll");
* Show ALL the elements: $("<ul>").multiList("showAll");
*
* Disable an element: $("<ul>").multiList("disable");
* Enable an element: $("<ul>").multiList("enable");
*
* Change an element name: $("<ul>").multiList("setName", "<li> value", "<li> new name");
*
* Events:
* "multiList.elementUnchecked": Triggered when unchecking an item. Params passed: "value" (the "value" attr from the li items) and "text (the full text)
* "multiList.elementChecked": Triggered when checking an item. Params passed: "value" (the "value" attr from the li items) and "text (the full text)
* 
*
*/

(function ($) {

	"use strict";

	$.fn.multiList = function(action, option, option2) {

		var response = undefined;
		var main_id = this.attr("id");
		var main_name = this.attr("data-name");

		// Event trigger aux vars
		var trigger_unchecked;
		var trigger_checked;

		if (action === undefined) {

			// Change the <ul>
			if (this[0].tagName == "UL" && $(this[0]).data("ml-setted") == undefined ) {

				// Create the container
				$("<div id='" + main_id + "_container' class='ml-container'></div>").insertAfter(this[0]);

				// Add the hidden input
			$("#" + main_id + "_container").append("<input id='" + main_id + "_input_values' name='"+ main_name +"' type='hidden' />")

				// Add the filter box
				$("#" + main_id + "_container").append("<div class='ml-input-container'>"
														+ 	"<input id='" + main_id + "_input_filter' name='" + main_id + "_input_filter' class='ml-input-filter' type='text' placeholder='Filter' onkeydown='if (event.keyCode == 13) return false;' >"
														+ "</div>");

				// Bind the filter box
				$("#" + main_id + "_input_filter").keyup(function() {

					var search = $(this).val();

					if (search == "") {
						$("." + main_id + "_list_item").show();
					} else {
						$("." + main_id + "_list_item").each(function() {
							if ( (($(this).text()).toLowerCase()).indexOf(search.toLowerCase()) > -1 ) {
								$(this).show();
							} else {
								$(this).hide();
							}
						});
					}

				});


				// Add the <ul> and change it
				this.detach().appendTo($("#" + main_id + "_container"));
				this.addClass("ml-ul");
				
				// Change the <li> 

				$.each(
					$("#" + main_id).children("li"),
					function(index, element) {

						// Add the custom class to the <li> (ml-li) and one unique for this list
						//$(element).addClass("checkbox");
						$(element).addClass("ml-li");
						$(element).addClass(main_id + "_list_item");

						// Append the checkbox inside the <li>
						$(element).html("<span class='checkbox'><input type='checkbox' name='" + main_id + "_checkbox' data-full_text='" + $(element).text() + "' id='" + main_id + "_checkbox_" + $(element).attr("value") + "'><i class='input-helper'></i></span>"
										+  $(element).text() );
						$(element).wrap("<label style='display: inherit !important;'></label>");

						// Bind the onclick event on the <li> element
						$(element).find("input").change(function() {

							var selected_array = ($("#" + main_id + "_input_values").val() != "" ? $("#" + main_id + "_input_values").val().split(",") : []);

							var position = $.inArray($(element).attr("value"), selected_array);
							if (position > -1) {
								// it's in the array
								if (!$(this).is(":checked")) {
									// only do stuff if it's in the array and not checked (must remove)
									selected_array.splice(position, 1);

									//Trigger the "element removed" event
									trigger_unchecked = true;
								}
							} else {
								//it's not in the array
								if ($(this).is(":checked")) {
									// only do stuff if it's not in the array and checked (must add)
									selected_array.push($(element).attr("value"));

									//Trigger the "element removed" added
									trigger_checked = true;
								}
							}

							$("#" + main_id + "_input_values").val(selected_array.toString());

							// Trigger events if proceed
							// (we do it BEFORE the _input_values field is setted, so the events will work
							// with the current array and not with the old one)

							if (trigger_unchecked) {
								$("#" + main_id).trigger("multiList.elementUnchecked", [$(element).attr("value"), $(this).data("full_text")]);
								trigger_unchecked = false;
							}

							if (trigger_checked) {
								$("#" + main_id).trigger("multiList.elementChecked", [$(element).attr("value"), $(this).data("full_text")]);
								trigger_checked = false;
							}

						});

					}
				);

				$(this).data("ml-setted", 1);

			}

			response = this;

		} else if (action === "remove") {
			if ($("#" + main_id + "_container").length > 0) {
				$("#" + main_id + "_container").parent().append("<ul id='" + main_id + "'></ul>");
				$("#" + main_id + "_container").remove();
			}

			response = this;

		} else if (action === "getSelected") {
			if ($("#" + main_id + "_input_values").length != 0) {
				response = ($("#" + main_id + "_input_values").val() != "" ? $("#" + main_id + "_input_values").val().split(",") : []);
			}
		} else if (action === "getSelectedFull") {
			if ($("#" + main_id + "_input_values").length != 0) {
				response = [];
				var selected_array = ($("#" + main_id + "_input_values").val() != "" ? $("#" + main_id + "_input_values").val().split(",") : []); 
				for (var i = 0; i < selected_array.length; i++) {
					response.push([selected_array[i], $("#" + main_id + "_checkbox_" + selected_array[i]).data("full_text")]);
				}
			}
		} else if (action == "getUnselected") {
			response = [];
			$.each($("#" + main_id + " li"), function(index, value) {
				if (!$(value).find("input").prop('checked') ) {
					response.push($(value).attr("value"));
				}
			});
		} else if (action == "getUnselectedFull") {
			response = [];
			$.each($("#" + main_id + " li"), function(index, value) {
				if (!$(value).find("input").prop('checked') ) {
					response.push([$(value).attr("value"), $("#" + main_id + "_checkbox_" + $(value).attr("value")).data("full_text")]);
				}
			});
		} else if (action === "select" && option !== undefined) {
			if ($("#" + main_id + "_input_values").length != 0 && !$("#" + main_id + " li[value='" + option + "']").hasClass('ml-hide') ) {
				if (!$("#" + main_id + " li[value='" + option + "']").find("input").prop("disabled")) {
					$("#" + main_id + " li[value='" + option + "']").find("input").prop('checked', true);
					$("#" + main_id + " li[value='" + option + "']").find("input").trigger("change");
				}
			}
		} else if (action === "unselect" && option !== undefined) {
			if ($("#" + main_id + "_input_values").length != 0  && !$("#" + main_id + " li[value='" + option + "']").hasClass('ml-hide')) {
				if (!$("#" + main_id + " li[value='" + option + "']").find("input").prop("disabled")) {
					$("#" + main_id + " li[value='" + option + "']").find("input").prop('checked', false);
					$("#" + main_id + " li[value='" + option + "']").find("input").trigger("change");
				}
			}
		} else if (action === "selectAll") {
			if ($("#" + main_id + "_input_values").length != 0) {
				$("#" + main_id + " li:not(.hide) input:enabled:not(:checked)").each(function(index, value) {
					$(value).prop('checked', true).trigger("change");
				});
			}
		} else if (action === "unselectAll") {
			if ($("#" + main_id + "_input_values").length != 0) {
				$("#" + main_id + " li:not(.hide) input:enabled:checked").each(function(index, value) {
					$(value).prop('checked', false).trigger("change");
				});
			}
		} else if (action === "hide") {
			if ($("#" + main_id + "_input_values").length != 0) {
				if (typeof(option) == "string") {
					$("#" + main_id + " li[value='" + option + "']").addClass('ml-hide');
				} else if (typeof(option) == "object") {
					$.each(option, function(index, value) {
						$("#" + main_id + " li[value='" + value + "']").addClass('ml-hide');
					});
				}
			}
		} else if (action === "show") {
			if ($("#" + main_id + "_input_values").length != 0) {
				if (typeof(option) == "string") {
					$("#" + main_id + " li[value='" + option + "']").removeClass('ml-hide');
				} else if (typeof(option) == "object") {
					$.each(option, function(index, value) {
						$("#" + main_id + " li[value='" + value + "']").removeClass('ml-hide');
					});
				}

				typeof(option); 
			}
		} else if (action === "hideAll") {
			if ($("#" + main_id + "_input_values").length != 0) {
				$("#" + main_id + " li").addClass('ml-hide');
			}
		} else if (action === "showAll") {
			if ($("#" + main_id + "_input_values").length != 0) {
				$("#" + main_id + " li").removeClass('ml-hide');
			}
		} else if (action === "disable") {
			if ($("#" + main_id + "_input_values").length != 0) {
				if (typeof(option) == "string") {
					$("#" + main_id + " li[value='" + option + "']").addClass('ml-disabled-element-li');
					$("#" + main_id + " li[value='" + option + "']").find("input").addClass('ml-hide');
					$("#" + main_id + " li[value='" + option + "']").find("input").attr('disabled', true);
					$("#" + main_id + " li[value='" + option + "']").find("input").attr('checked', false);
				} else if (typeof(option) == "object") {
					$.each(option, function(index, value) {
						$("#" + main_id + " li[value='" + value + "']").addClass('ml-disabled-element-li');
						$("#" + main_id + " li[value='" + value + "']").find("input").addClass('ml-hide');
						$("#" + main_id + " li[value='" + value + "']").find("input").attr('disabled', true);
					$("#" + main_id + " li[value='" + value + "']").find("input").attr('checked', false);
					});
				}
			}
		} else if (action === "enable") {
			if ($("#" + main_id + "_input_values").length != 0) {
				if (typeof(option) == "string") {
					$("#" + main_id + " li[value='" + option + "']").removeClass('ml-disabled-element-li');
					$("#" + main_id + " li[value='" + option + "']").find("input").removeClass('ml-hide');
					$("#" + main_id + " li[value='" + option + "']").find("input").attr('disabled', false);
				} else if (typeof(option) == "object") {
					$.each(option, function(index, value) {
						$("#" + main_id + " li[value='" + value + "']").removeClass('ml-disabled-element-li');
						$("#" + main_id + " li[value='" + value + "']").find("input").removeClass('ml-hide');
						$("#" + main_id + " li[value='" + value + "']").find("input").attr('disabled', false);
					});
				}

				typeof(option); 
			}
		} else if (action === "setName") {
			if ($("#" + main_id + "_input_values").length != 0 && typeof(option2) != "undefined") {
				$("#" + main_id + " li[value='" + option + "']").find("input").data("full_text", option2);
				$("#" + main_id + " li[value='" + option + "'] label span").html(option2);
			}
		}

		return response;

	}


}(jQuery));
/**
 * TableDnD plug-in for JQuery, allows you to drag and drop table rows
 * You can set up various options to control how the system will work
 * Copyright (c) Denis Howlett <denish@isocra.com>
 * Licensed like jQuery, see http://docs.jquery.com/License.
 *
 * Configuration options:
 * 
 * onDragStyle
 *     This is the style that is assigned to the row during drag. There are limitations to the styles that can be
 *     associated with a row (such as you can't assign a border--well you can, but it won't be
 *     displayed). (So instead consider using onDragClass.) The CSS style to apply is specified as
 *     a map (as used in the jQuery css(...) function).
 * onDropStyle
 *     This is the style that is assigned to the row when it is dropped. As for onDragStyle, there are limitations
 *     to what you can do. Also this replaces the original style, so again consider using onDragClass which
 *     is simply added and then removed on drop.
 * onDragClass
 *     This class is added for the duration of the drag and then removed when the row is dropped. It is more
 *     flexible than using onDragStyle since it can be inherited by the row cells and other content. The default
 *     is class is tDnD_whileDrag. So to use the default, simply customise this CSS class in your
 *     stylesheet.
 * onDrop
 *     Pass a function that will be called when the row is dropped. The function takes 2 parameters: the table
 *     and the row that was dropped. You can work out the new order of the rows by using
 *     table.rows.
 * onDragStart
 *     Pass a function that will be called when the user starts dragging. The function takes 2 parameters: the
 *     table and the row which the user has started to drag.
 * onAllowDrop
 *     Pass a function that will be called as a row is over another row. If the function returns true, allow 
 *     dropping on that row, otherwise not. The function takes 2 parameters: the dragged row and the row under
 *     the cursor. It returns a boolean: true allows the drop, false doesn't allow it.
 * scrollAmount
 *     This is the number of pixels to scroll if the user moves the mouse cursor to the top or bottom of the
 *     window. The page should automatically scroll up or down as appropriate (tested in IE6, IE7, Safari, FF2,
 *     FF3 beta
 * dragHandle
 *     This is a jQuery mach string for one or more cells in each row that is draggable. If you
 *     specify this, then you are responsible for setting cursor: move in the CSS and only these cells
 *     will have the drag behaviour. If you do not specify a dragHandle, then you get the old behaviour where
 *     the whole row is draggable.
 * 
 * Other ways to control behaviour:
 *
 * Add class="nodrop" to any rows for which you don't want to allow dropping, and class="nodrag" to any rows
 * that you don't want to be draggable.
 *
 * Inside the onDrop method you can also call $.tableDnD.serialize() this returns a string of the form
 * <tableID>[]=<rowID1>&<tableID>[]=<rowID2> so that you can send this back to the server. The table must have
 * an ID as must all the rows.
 *
 * Other methods:
 *
 * $("...").tableDnDUpdate() 
 * Will update all the matching tables, that is it will reapply the mousedown method to the rows (or handle cells).
 * This is useful if you have updated the table rows using Ajax and you want to make the table draggable again.
 * The table maintains the original configuration (so you don't have to specify it again).
 *
 * $("...").tableDnDSerialize()
 * Will serialize and return the serialized string as above, but for each of the matching tables--so it can be
 * called from anywhere and isn't dependent on the currentTable being set up correctly before calling
 *
 * Known problems:
 * - Auto-scoll has some problems with IE7  (it scrolls even when it shouldn't), work-around: set scrollAmount to 0
 * 
 * Version 0.2: 2008-02-20 First public version
 * Version 0.3: 2008-02-07 Added onDragStart option
 *                         Made the scroll amount configurable (default is 5 as before)
 * Version 0.4: 2008-03-15 Changed the noDrag/noDrop attributes to nodrag/nodrop classes
 *                         Added onAllowDrop to control dropping
 *                         Fixed a bug which meant that you couldn't set the scroll amount in both directions
 *                         Added serialize method
 * Version 0.5: 2008-05-16 Changed so that if you specify a dragHandle class it doesn't make the whole row
 *                         draggable
 *                         Improved the serialize method to use a default (and settable) regular expression.
 *                         Added tableDnDupate() and tableDnDSerialize() to be called when you are outside the table
 * Version 0.6: 2011-12-02 Added support for touch devices
 * Version 0.7  2012-04-09 Now works with jQuery 1.7 and supports touch, tidied up tabs and spaces
 */
// Determine if this is a touch device
var hasTouch = 'ontouchstart' in document.documentElement,
        startEvent = hasTouch ? 'touchstart' : 'mousedown',
        moveEvent = hasTouch ? 'touchmove' : 'mousemove',
        endEvent = hasTouch ? 'touchend' : 'mouseup';

// If we're on a touch device, then wire up the events
// see http://stackoverflow.com/a/8456194/1316086

/* below code commented to make the drag and drop display order functional on small screens also */

if (hasTouch) {
    $.each("touchstart touchmove touchend".split(" "), function(i, name) {
        jQuery.event.fixHooks[name] = jQuery.event.mouseHooks;
    });
}


jQuery.tableDnD = {
    /** Keep hold of the current table being dragged */
    currentTable : null,
    /** Keep hold of the current drag object if any */
    dragObject: null,
    /** The current mouse offset */
    mouseOffset: null,
    /** Remember the old value of Y so that we don't do too much processing */
    oldY: 0,

    /** Actually build the structure */
    build: function(options) {
        // Set up the defaults if any

        this.each(function() {
            // This is bound to each matching table, set up the defaults and override with user options
            this.tableDnDConfig = jQuery.extend({
                onDragStyle: null,
                onDropStyle: null,
                // Add in the default class for whileDragging
                onDragClass: "tDnD_whileDrag",
                onDrop: null,
                onDragStart: null,
                scrollAmount: 5,

                serializeRegexp: /[^\-]*$/, // The regular expression to use to trim row IDs
                serializeParamName: null, // If you want to specify another parameter name instead of the table ID
                dragHandle: null // If you give the name of a class here, then only Cells with this class will be draggable
            }, options || {});

            // Now make the rows draggable
            jQuery.tableDnD.makeDraggable(this);
        });

        // Don't break the chain
        return this;
    },

    /** This function makes all the rows on the table draggable apart from those marked as "NoDrag" */
    makeDraggable: function(table) {

        var config = table.tableDnDConfig;
        if (config.dragHandle) {
            // We only need to add the event to the specified cells
            var cells = jQuery(table.tableDnDConfig.dragHandle, table);
            cells.each(function() {
                // The cell is bound to "this"
                jQuery(this).bind(startEvent, function(ev) {
                    jQuery.tableDnD.initialiseDrag(jQuery(this).parents('tr')[0], table, this, ev, config);
                    return false;
                });
            })
        } else {
            // For backwards compatibility, we add the event to the whole row
            var rows = jQuery("tr", table); // get all the rows as a wrapped set
            rows.each(function() {
                // Iterate through each row, the row is bound to "this"
                var row = jQuery(this);
                if (! row.hasClass("nodrag")) {
                    row.bind(startEvent, function(ev) {
                        if (ev.target.tagName == "TD") {
                            jQuery.tableDnD.initialiseDrag(this, table, this, ev, config);
                            return false;
                        }
                    }).css("cursor", "move"); // Store the tableDnD object
                }
            });
        }
    },

    initialiseDrag: function(dragObject, table, target, evnt, config) {
        jQuery.tableDnD.dragObject = dragObject;
        jQuery.tableDnD.currentTable = table;
        jQuery.tableDnD.mouseOffset = jQuery.tableDnD.getMouseOffset(target, evnt);
        jQuery.tableDnD.originalOrder = jQuery.tableDnD.serialize();
        // Now we need to capture the mouse up and mouse move event
        // We can use bind so that we don't interfere with other event handlers
        jQuery(document)
                .bind(moveEvent, jQuery.tableDnD.mousemove)
                .bind(endEvent, jQuery.tableDnD.mouseup);
        if (config.onDragStart) {
            // Call the onDragStart method if there is one
            config.onDragStart(table, target);
        }
    },

    updateTables: function() {
        this.each(function() {
            // this is now bound to each matching table
            if (this.tableDnDConfig) {
                jQuery.tableDnD.makeDraggable(this);
            }
        })
    },

    /** Get the mouse coordinates from the event (allowing for browser differences) */
    mouseCoords: function(ev){
        if(ev.pageX || ev.pageY){
            return {x:ev.pageX, y:ev.pageY};
        }
        return {
            x:ev.clientX + document.body.scrollLeft - document.body.clientLeft,
            y:ev.clientY + document.body.scrollTop  - document.body.clientTop
        };
    },

    /** Given a target element and a mouse event, get the mouse offset from that element.
     To do this we need the element's position and the mouse position */
    getMouseOffset: function(target, ev) {
        ev = ev || window.event;

        var docPos    = this.getPosition(target);
        var mousePos  = this.mouseCoords(ev);
        return {x:mousePos.x - docPos.x, y:mousePos.y - docPos.y};
    },

    /** Get the position of an element by going up the DOM tree and adding up all the offsets */
    getPosition: function(e){
        var left = 0;
        var top  = 0;
        /** Safari fix -- thanks to Luis Chato for this! */
        if (e.offsetHeight == 0) {
            /** Safari 2 doesn't correctly grab the offsetTop of a table row
             this is detailed here:
             http://jacob.peargrove.com/blog/2006/technical/table-row-offsettop-bug-in-safari/
             the solution is likewise noted there, grab the offset of a table cell in the row - the firstChild.
             note that firefox will return a text node as a first child, so designing a more thorough
             solution may need to take that into account, for now this seems to work in firefox, safari, ie */
            e = e.firstChild; // a table cell
        }

        while (e.offsetParent){
            left += e.offsetLeft;
            top  += e.offsetTop;
            e     = e.offsetParent;
        }

        left += e.offsetLeft;
        top  += e.offsetTop;

        return {x:left, y:top};
    },

    mousemove: function(ev) {
        if (jQuery.tableDnD.dragObject == null) {
            return;
        }
        if (ev.type == 'touchmove') {
            // prevent touch device screen scrolling
            event.preventDefault();
        }

        var dragObj = jQuery(jQuery.tableDnD.dragObject);
        var config = jQuery.tableDnD.currentTable.tableDnDConfig;
        var mousePos = jQuery.tableDnD.mouseCoords(ev);
        var y = mousePos.y - jQuery.tableDnD.mouseOffset.y;
        //auto scroll the window
        var yOffset = window.pageYOffset;
        if (document.all) {
            // Windows version
            //yOffset=document.body.scrollTop;
            if (typeof document.compatMode != 'undefined' &&
                    document.compatMode != 'BackCompat') {
                yOffset = document.documentElement.scrollTop;
            }
            else if (typeof document.body != 'undefined') {
                yOffset=document.body.scrollTop;
            }
        }

        if (mousePos.y-yOffset < config.scrollAmount) {
            window.scrollBy(0, -config.scrollAmount);
        } else {
            var windowHeight = window.innerHeight ? window.innerHeight
                    : document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;
            if (windowHeight-(mousePos.y-yOffset) < config.scrollAmount) {
                window.scrollBy(0, config.scrollAmount);
            }
        }

        if (y != jQuery.tableDnD.oldY) {
            // work out if we're going up or down...
            var movingDown = y > jQuery.tableDnD.oldY;
            // update the old value
            jQuery.tableDnD.oldY = y;
            // update the style to show we're dragging
            if (config.onDragClass) {
                dragObj.addClass(config.onDragClass);
            } else {
                dragObj.css(config.onDragStyle);
            }
            // If we're over a row then move the dragged row to there so that the user sees the
            // effect dynamically
            var currentRow = jQuery.tableDnD.findDropTargetRow(dragObj, y);
            if (currentRow) {
                // TODO worry about what happens when there are multiple TBODIES
                if (movingDown && jQuery.tableDnD.dragObject != currentRow) {
                    jQuery.tableDnD.dragObject.parentNode.insertBefore(jQuery.tableDnD.dragObject, currentRow.nextSibling);
                } else if (! movingDown && jQuery.tableDnD.dragObject != currentRow) {
                    jQuery.tableDnD.dragObject.parentNode.insertBefore(jQuery.tableDnD.dragObject, currentRow);
                }
            }
        }

        return false;
    },

    /** We're only worried about the y position really, because we can only move rows up and down */
    findDropTargetRow: function(draggedRow, y) {
        var rows = jQuery.tableDnD.currentTable.rows;
        for (var i=0; i<rows.length; i++) {
            var row = rows[i];
            var rowY    = this.getPosition(row).y;
            var rowHeight = parseInt(row.offsetHeight)/2;
            if (row.offsetHeight == 0) {
                rowY = this.getPosition(row.firstChild).y;
                rowHeight = parseInt(row.firstChild.offsetHeight)/2;
            }
            // Because we always have to insert before, we need to offset the height a bit
            if ((y > rowY - rowHeight) && (y < (rowY + rowHeight))) {
                // that's the row we're over
                // If it's the same as the current row, ignore it
                if (row == draggedRow) {return null;}
                var config = jQuery.tableDnD.currentTable.tableDnDConfig;
                if (config.onAllowDrop) {
                    if (config.onAllowDrop(draggedRow, row)) {
                        return row;
                    } else {
                        return null;
                    }
                } else {
                    // If a row has nodrop class, then don't allow dropping (inspired by John Tarr and Famic)
                    var nodrop = jQuery(row).hasClass("nodrop");
                    if (! nodrop) {
                        return row;
                    } else {
                        return null;
                    }
                }
                return row;
            }
        }
        return null;
    },

    mouseup: function(e) {
        if (jQuery.tableDnD.currentTable && jQuery.tableDnD.dragObject) {
            // Unbind the event handlers
            jQuery(document)
                    .unbind(moveEvent, jQuery.tableDnD.mousemove)
                    .unbind(endEvent, jQuery.tableDnD.mouseup);
            var droppedRow = jQuery.tableDnD.dragObject;
            var config = jQuery.tableDnD.currentTable.tableDnDConfig;
            // If we have a dragObject, then we need to release it,
            // The row will already have been moved to the right place so we just reset stuff
            if (config.onDragClass) {
                jQuery(droppedRow).removeClass(config.onDragClass);
            } else {
                jQuery(droppedRow).css(config.onDropStyle);
            }
            jQuery.tableDnD.dragObject = null;
            var newOrder = jQuery.tableDnD.serialize();
            if (config.onDrop && (jQuery.tableDnD.originalOrder != newOrder)) {
                // Call the onDrop method if there is one
                config.onDrop(jQuery.tableDnD.currentTable, droppedRow);
            }
            jQuery.tableDnD.currentTable = null; // let go of the table too
        }
    },

    serialize: function() {
        if (jQuery.tableDnD.currentTable) {
            return jQuery.tableDnD.serializeTable(jQuery.tableDnD.currentTable);
        } else {
            return "Error: No Table id set, you need to set an id on your table and every row";
        }
    },

    serializeTable: function(table) {
        var result = "";
        var tableId = table.id;
        var rows = table.rows;
        for (var i=0; i<rows.length; i++) {
            if (result.length > 0) result += "&";
            var rowId = rows[i].id;
            if (rowId && rowId && table.tableDnDConfig && table.tableDnDConfig.serializeRegexp) {
                rowId = rowId.match(table.tableDnDConfig.serializeRegexp)[0];
            }

            result += tableId + '[]=' + rowId;
        }
        return result;
    },

    serializeTables: function() {
        var result = "";
        this.each(function() {
            // this is now bound to each matching table
            result += jQuery.tableDnD.serializeTable(this);
        });
        return result;
    }
};


jQuery.fn.extend(
    {
        tableDnD : jQuery.tableDnD.build,
        tableDnDUpdate : jQuery.tableDnD.updateTables,
        tableDnDSerialize: jQuery.tableDnD.serializeTables
    }
);/*
     _ _      _       _
 ___| (_) ___| | __  (_)___
/ __| | |/ __| |/ /  | / __|
\__ \ | | (__|   < _ | \__ \
|___/_|_|\___|_|\_(_)/ |___/
                   |__/

 Version: 1.6.0
  Author: Ken Wheeler
 Website: http://kenwheeler.github.io
    Docs: http://kenwheeler.github.io/slick
    Repo: http://github.com/kenwheeler/slick
  Issues: http://github.com/kenwheeler/slick/issues

 */
/* global window, document, define, jQuery, setInterval, clearInterval */
(function(factory) {
    'use strict';
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof exports !== 'undefined') {
        module.exports = factory(require('jquery'));
    } else {
        factory(jQuery);
    }

}(function($) {
    'use strict';
    var Slick = window.Slick || {};

    Slick = (function() {

        var instanceUid = 0;

        function Slick(element, settings) {

            var _ = this, dataSettings;

            _.defaults = {
                accessibility: true,
                adaptiveHeight: false,
                appendArrows: $(element),
                appendDots: $(element),
                arrows: true,
                asNavFor: null,
                prevArrow: '<button type="button" data-role="none" class="slick-prev" aria-label="Previous" tabindex="0" role="button">Previous</button>',
                nextArrow: '<button type="button" data-role="none" class="slick-next" aria-label="Next" tabindex="0" role="button">Next</button>',
                autoplay: false,
                autoplaySpeed: 3000,
                centerMode: false,
                centerPadding: '50px',
                cssEase: 'ease',
                customPaging: function(slider, i) {
                    return $('<button type="button" data-role="none" role="button" tabindex="0" />').text(i + 1);
                },
                dots: false,
                dotsClass: 'slick-dots',
                draggable: true,
                easing: 'linear',
                edgeFriction: 0.35,
                fade: false,
                focusOnSelect: false,
                infinite: true,
                initialSlide: 0,
                lazyLoad: 'ondemand',
                mobileFirst: false,
                pauseOnHover: true,
                pauseOnFocus: true,
                pauseOnDotsHover: false,
                respondTo: 'window',
                responsive: null,
                rows: 1,
                rtl: false,
                slide: '',
                slidesPerRow: 1,
                slidesToShow: 1,
                slidesToScroll: 1,
                speed: 500,
                swipe: true,
                swipeToSlide: false,
                touchMove: true,
                touchThreshold: 5,
                useCSS: true,
                useTransform: true,
                variableWidth: false,
                vertical: false,
                verticalSwiping: false,
                waitForAnimate: true,
                zIndex: 1000
            };

            _.initials = {
                animating: false,
                dragging: false,
                autoPlayTimer: null,
                currentDirection: 0,
                currentLeft: null,
                currentSlide: 0,
                direction: 1,
                $dots: null,
                listWidth: null,
                listHeight: null,
                loadIndex: 0,
                $nextArrow: null,
                $prevArrow: null,
                slideCount: null,
                slideWidth: null,
                $slideTrack: null,
                $slides: null,
                sliding: false,
                slideOffset: 0,
                swipeLeft: null,
                $list: null,
                touchObject: {},
                transformsEnabled: false,
                unslicked: false
            };

            $.extend(_, _.initials);

            _.activeBreakpoint = null;
            _.animType = null;
            _.animProp = null;
            _.breakpoints = [];
            _.breakpointSettings = [];
            _.cssTransitions = false;
            _.focussed = false;
            _.interrupted = false;
            _.hidden = 'hidden';
            _.paused = true;
            _.positionProp = null;
            _.respondTo = null;
            _.rowCount = 1;
            _.shouldClick = true;
            _.$slider = $(element);
            _.$slidesCache = null;
            _.transformType = null;
            _.transitionType = null;
            _.visibilityChange = 'visibilitychange';
            _.windowWidth = 0;
            _.windowTimer = null;

            dataSettings = $(element).data('slick') || {};

            _.options = $.extend({}, _.defaults, settings, dataSettings);

            _.currentSlide = _.options.initialSlide;

            _.originalSettings = _.options;

            if (typeof document.mozHidden !== 'undefined') {
                _.hidden = 'mozHidden';
                _.visibilityChange = 'mozvisibilitychange';
            } else if (typeof document.webkitHidden !== 'undefined') {
                _.hidden = 'webkitHidden';
                _.visibilityChange = 'webkitvisibilitychange';
            }

            _.autoPlay = $.proxy(_.autoPlay, _);
            _.autoPlayClear = $.proxy(_.autoPlayClear, _);
            _.autoPlayIterator = $.proxy(_.autoPlayIterator, _);
            _.changeSlide = $.proxy(_.changeSlide, _);
            _.clickHandler = $.proxy(_.clickHandler, _);
            _.selectHandler = $.proxy(_.selectHandler, _);
            _.setPosition = $.proxy(_.setPosition, _);
            _.swipeHandler = $.proxy(_.swipeHandler, _);
            _.dragHandler = $.proxy(_.dragHandler, _);
            _.keyHandler = $.proxy(_.keyHandler, _);

            _.instanceUid = instanceUid++;

            // A simple way to check for HTML strings
            // Strict HTML recognition (must start with <)
            // Extracted from jQuery v1.11 source
            _.htmlExpr = /^(?:\s*(<[\w\W]+>)[^>]*)$/;


            _.registerBreakpoints();
            _.init(true);

        }

        return Slick;

    }());

    Slick.prototype.activateADA = function() {
        var _ = this;

        _.$slideTrack.find('.slick-active').attr({
            'aria-hidden': 'false'
        }).find('a, input, button, select').attr({
            'tabindex': '0'
        });

    };

    Slick.prototype.addSlide = Slick.prototype.slickAdd = function(markup, index, addBefore) {

        var _ = this;

        if (typeof(index) === 'boolean') {
            addBefore = index;
            index = null;
        } else if (index < 0 || (index >= _.slideCount)) {
            return false;
        }

        _.unload();

        if (typeof(index) === 'number') {
            if (index === 0 && _.$slides.length === 0) {
                $(markup).appendTo(_.$slideTrack);
            } else if (addBefore) {
                $(markup).insertBefore(_.$slides.eq(index));
            } else {
                $(markup).insertAfter(_.$slides.eq(index));
            }
        } else {
            if (addBefore === true) {
                $(markup).prependTo(_.$slideTrack);
            } else {
                $(markup).appendTo(_.$slideTrack);
            }
        }

        _.$slides = _.$slideTrack.children(this.options.slide);

        _.$slideTrack.children(this.options.slide).detach();

        _.$slideTrack.append(_.$slides);

        _.$slides.each(function(index, element) {
            $(element).attr('data-slick-index', index);
        });

        _.$slidesCache = _.$slides;

        _.reinit();

    };

    Slick.prototype.animateHeight = function() {
        var _ = this;
        if (_.options.slidesToShow === 1 && _.options.adaptiveHeight === true && _.options.vertical === false) {
            var targetHeight = _.$slides.eq(_.currentSlide).outerHeight(true);
            _.$list.animate({
                height: targetHeight
            }, _.options.speed);
        }
    };

    Slick.prototype.animateSlide = function(targetLeft, callback) {

        var animProps = {},
            _ = this;

        _.animateHeight();

        if (_.options.rtl === true && _.options.vertical === false) {
            targetLeft = -targetLeft;
        }
        if (_.transformsEnabled === false) {
            if (_.options.vertical === false) {
                _.$slideTrack.animate({
                    left: targetLeft
                }, _.options.speed, _.options.easing, callback);
            } else {
                _.$slideTrack.animate({
                    top: targetLeft
                }, _.options.speed, _.options.easing, callback);
            }

        } else {

            if (_.cssTransitions === false) {
                if (_.options.rtl === true) {
                    _.currentLeft = -(_.currentLeft);
                }
                $({
                    animStart: _.currentLeft
                }).animate({
                    animStart: targetLeft
                }, {
                    duration: _.options.speed,
                    easing: _.options.easing,
                    step: function(now) {
                        now = Math.ceil(now);
                        if (_.options.vertical === false) {
                            animProps[_.animType] = 'translate(' +
                                now + 'px, 0px)';
                            _.$slideTrack.css(animProps);
                        } else {
                            animProps[_.animType] = 'translate(0px,' +
                                now + 'px)';
                            _.$slideTrack.css(animProps);
                        }
                    },
                    complete: function() {
                        if (callback) {
                            callback.call();
                        }
                    }
                });

            } else {

                _.applyTransition();
                targetLeft = Math.ceil(targetLeft);

                if (_.options.vertical === false) {
                    animProps[_.animType] = 'translate3d(' + targetLeft + 'px, 0px, 0px)';
                } else {
                    animProps[_.animType] = 'translate3d(0px,' + targetLeft + 'px, 0px)';
                }
                _.$slideTrack.css(animProps);

                if (callback) {
                    setTimeout(function() {

                        _.disableTransition();

                        callback.call();
                    }, _.options.speed);
                }

            }

        }

    };

    Slick.prototype.getNavTarget = function() {

        var _ = this,
            asNavFor = _.options.asNavFor;

        if ( asNavFor && asNavFor !== null ) {
            asNavFor = $(asNavFor).not(_.$slider);
        }

        return asNavFor;

    };

    Slick.prototype.asNavFor = function(index) {

        var _ = this,
            asNavFor = _.getNavTarget();

        if ( asNavFor !== null && typeof asNavFor === 'object' ) {
            asNavFor.each(function() {
                var target = $(this).slick('getSlick');
                if(!target.unslicked) {
                    target.slideHandler(index, true);
                }
            });
        }

    };

    Slick.prototype.applyTransition = function(slide) {

        var _ = this,
            transition = {};

        if (_.options.fade === false) {
            transition[_.transitionType] = _.transformType + ' ' + _.options.speed + 'ms ' + _.options.cssEase;
        } else {
            transition[_.transitionType] = 'opacity ' + _.options.speed + 'ms ' + _.options.cssEase;
        }

        if (_.options.fade === false) {
            _.$slideTrack.css(transition);
        } else {
            _.$slides.eq(slide).css(transition);
        }

    };

    Slick.prototype.autoPlay = function() {

        var _ = this;

        _.autoPlayClear();

        if ( _.slideCount > _.options.slidesToShow ) {
            _.autoPlayTimer = setInterval( _.autoPlayIterator, _.options.autoplaySpeed );
        }

    };

    Slick.prototype.autoPlayClear = function() {

        var _ = this;

        if (_.autoPlayTimer) {
            clearInterval(_.autoPlayTimer);
        }

    };

    Slick.prototype.autoPlayIterator = function() {

        var _ = this,
            slideTo = _.currentSlide + _.options.slidesToScroll;

        if ( !_.paused && !_.interrupted && !_.focussed ) {

            if ( _.options.infinite === false ) {

                if ( _.direction === 1 && ( _.currentSlide + 1 ) === ( _.slideCount - 1 )) {
                    _.direction = 0;
                }

                else if ( _.direction === 0 ) {

                    slideTo = _.currentSlide - _.options.slidesToScroll;

                    if ( _.currentSlide - 1 === 0 ) {
                        _.direction = 1;
                    }

                }

            }

            _.slideHandler( slideTo );

        }

    };

    Slick.prototype.buildArrows = function() {

        var _ = this;

        if (_.options.arrows === true ) {

            _.$prevArrow = $(_.options.prevArrow).addClass('slick-arrow');
            _.$nextArrow = $(_.options.nextArrow).addClass('slick-arrow');

            if( _.slideCount > _.options.slidesToShow ) {

                _.$prevArrow.removeClass('slick-hidden').removeAttr('aria-hidden tabindex');
                _.$nextArrow.removeClass('slick-hidden').removeAttr('aria-hidden tabindex');

                if (_.htmlExpr.test(_.options.prevArrow)) {
                    _.$prevArrow.prependTo(_.options.appendArrows);
                }

                if (_.htmlExpr.test(_.options.nextArrow)) {
                    _.$nextArrow.appendTo(_.options.appendArrows);
                }

                if (_.options.infinite !== true) {
                    _.$prevArrow
                        .addClass('slick-disabled')
                        .attr('aria-disabled', 'true');
                }

            } else {

                _.$prevArrow.add( _.$nextArrow )

                    .addClass('slick-hidden')
                    .attr({
                        'aria-disabled': 'true',
                        'tabindex': '-1'
                    });

            }

        }

    };

    Slick.prototype.buildDots = function() {

        var _ = this,
            i, dot;

        if (_.options.dots === true && _.slideCount > _.options.slidesToShow) {

            _.$slider.addClass('slick-dotted');

            dot = $('<ul />').addClass(_.options.dotsClass);

            for (i = 0; i <= _.getDotCount(); i += 1) {
                dot.append($('<li />').append(_.options.customPaging.call(this, _, i)));
            }

            _.$dots = dot.appendTo(_.options.appendDots);

            _.$dots.find('li').first().addClass('slick-active').attr('aria-hidden', 'false');

        }

    };

    Slick.prototype.buildOut = function() {

        var _ = this;

        _.$slides =
            _.$slider
                .children( _.options.slide + ':not(.slick-cloned)')
                .addClass('slick-slide');

        _.slideCount = _.$slides.length;

        _.$slides.each(function(index, element) {
            $(element)
                .attr('data-slick-index', index)
                .data('originalStyling', $(element).attr('style') || '');
        });

        _.$slider.addClass('slick-slider');

        _.$slideTrack = (_.slideCount === 0) ?
            $('<div class="slick-track"/>').appendTo(_.$slider) :
            _.$slides.wrapAll('<div class="slick-track"/>').parent();

        _.$list = _.$slideTrack.wrap(
            '<div aria-live="polite" class="slick-list"/>').parent();
        _.$slideTrack.css('opacity', 0);

        if (_.options.centerMode === true || _.options.swipeToSlide === true) {
            _.options.slidesToScroll = 1;
        }

        $('img[data-lazy]', _.$slider).not('[src]').addClass('slick-loading');

        _.setupInfinite();

        _.buildArrows();

        _.buildDots();

        _.updateDots();


        _.setSlideClasses(typeof _.currentSlide === 'number' ? _.currentSlide : 0);

        if (_.options.draggable === true) {
            _.$list.addClass('draggable');
        }

    };

    Slick.prototype.buildRows = function() {

        var _ = this, a, b, c, newSlides, numOfSlides, originalSlides,slidesPerSection;

        newSlides = document.createDocumentFragment();
        originalSlides = _.$slider.children();

        if(_.options.rows > 1) {

            slidesPerSection = _.options.slidesPerRow * _.options.rows;
            numOfSlides = Math.ceil(
                originalSlides.length / slidesPerSection
            );

            for(a = 0; a < numOfSlides; a++){
                var slide = document.createElement('div');
                for(b = 0; b < _.options.rows; b++) {
                    var row = document.createElement('div');
                    for(c = 0; c < _.options.slidesPerRow; c++) {
                        var target = (a * slidesPerSection + ((b * _.options.slidesPerRow) + c));
                        if (originalSlides.get(target)) {
                            row.appendChild(originalSlides.get(target));
                        }
                    }
                    slide.appendChild(row);
                }
                newSlides.appendChild(slide);
            }

            _.$slider.empty().append(newSlides);
            _.$slider.children().children().children()
                .css({
                    'width':(100 / _.options.slidesPerRow) + '%',
                    'display': 'inline-block'
                });

        }

    };

    Slick.prototype.checkResponsive = function(initial, forceUpdate) {

        var _ = this,
            breakpoint, targetBreakpoint, respondToWidth, triggerBreakpoint = false;
        var sliderWidth = _.$slider.width();
        var windowWidth = window.innerWidth || $(window).width();

        if (_.respondTo === 'window') {
            respondToWidth = windowWidth;
        } else if (_.respondTo === 'slider') {
            respondToWidth = sliderWidth;
        } else if (_.respondTo === 'min') {
            respondToWidth = Math.min(windowWidth, sliderWidth);
        }

        if ( _.options.responsive &&
            _.options.responsive.length &&
            _.options.responsive !== null) {

            targetBreakpoint = null;

            for (breakpoint in _.breakpoints) {
                if (_.breakpoints.hasOwnProperty(breakpoint)) {
                    if (_.originalSettings.mobileFirst === false) {
                        if (respondToWidth < _.breakpoints[breakpoint]) {
                            targetBreakpoint = _.breakpoints[breakpoint];
                        }
                    } else {
                        if (respondToWidth > _.breakpoints[breakpoint]) {
                            targetBreakpoint = _.breakpoints[breakpoint];
                        }
                    }
                }
            }

            if (targetBreakpoint !== null) {
                if (_.activeBreakpoint !== null) {
                    if (targetBreakpoint !== _.activeBreakpoint || forceUpdate) {
                        _.activeBreakpoint =
                            targetBreakpoint;
                        if (_.breakpointSettings[targetBreakpoint] === 'unslick') {
                            _.unslick(targetBreakpoint);
                        } else {
                            _.options = $.extend({}, _.originalSettings,
                                _.breakpointSettings[
                                    targetBreakpoint]);
                            if (initial === true) {
                                _.currentSlide = _.options.initialSlide;
                            }
                            _.refresh(initial);
                        }
                        triggerBreakpoint = targetBreakpoint;
                    }
                } else {
                    _.activeBreakpoint = targetBreakpoint;
                    if (_.breakpointSettings[targetBreakpoint] === 'unslick') {
                        _.unslick(targetBreakpoint);
                    } else {
                        _.options = $.extend({}, _.originalSettings,
                            _.breakpointSettings[
                                targetBreakpoint]);
                        if (initial === true) {
                            _.currentSlide = _.options.initialSlide;
                        }
                        _.refresh(initial);
                    }
                    triggerBreakpoint = targetBreakpoint;
                }
            } else {
                if (_.activeBreakpoint !== null) {
                    _.activeBreakpoint = null;
                    _.options = _.originalSettings;
                    if (initial === true) {
                        _.currentSlide = _.options.initialSlide;
                    }
                    _.refresh(initial);
                    triggerBreakpoint = targetBreakpoint;
                }
            }

            // only trigger breakpoints during an actual break. not on initialize.
            if( !initial && triggerBreakpoint !== false ) {
                _.$slider.trigger('breakpoint', [_, triggerBreakpoint]);
            }
        }

    };

    Slick.prototype.changeSlide = function(event, dontAnimate) {

        var _ = this,
            $target = $(event.currentTarget),
            indexOffset, slideOffset, unevenOffset;

        // If target is a link, prevent default action.
        if($target.is('a')) {
            event.preventDefault();
        }

        // If target is not the <li> element (ie: a child), find the <li>.
        if(!$target.is('li')) {
            $target = $target.closest('li');
        }

        unevenOffset = (_.slideCount % _.options.slidesToScroll !== 0);
        indexOffset = unevenOffset ? 0 : (_.slideCount - _.currentSlide) % _.options.slidesToScroll;

        switch (event.data.message) {

            case 'previous':
                slideOffset = indexOffset === 0 ? _.options.slidesToScroll : _.options.slidesToShow - indexOffset;
                if (_.slideCount > _.options.slidesToShow) {
                    _.slideHandler(_.currentSlide - slideOffset, false, dontAnimate);
                }
                break;

            case 'next':
                slideOffset = indexOffset === 0 ? _.options.slidesToScroll : indexOffset;
                if (_.slideCount > _.options.slidesToShow) {
                    _.slideHandler(_.currentSlide + slideOffset, false, dontAnimate);
                }
                break;

            case 'index':
                var index = event.data.index === 0 ? 0 :
                    event.data.index || $target.index() * _.options.slidesToScroll;

                _.slideHandler(_.checkNavigable(index), false, dontAnimate);
                $target.children().trigger('focus');
                break;

            default:
                return;
        }

    };

    Slick.prototype.checkNavigable = function(index) {

        var _ = this,
            navigables, prevNavigable;

        navigables = _.getNavigableIndexes();
        prevNavigable = 0;
        if (index > navigables[navigables.length - 1]) {
            index = navigables[navigables.length - 1];
        } else {
            for (var n in navigables) {
                if (index < navigables[n]) {
                    index = prevNavigable;
                    break;
                }
                prevNavigable = navigables[n];
            }
        }

        return index;
    };

    Slick.prototype.cleanUpEvents = function() {

        var _ = this;

        if (_.options.dots && _.$dots !== null) {

            $('li', _.$dots)
                .off('click.slick', _.changeSlide)
                .off('mouseenter.slick', $.proxy(_.interrupt, _, true))
                .off('mouseleave.slick', $.proxy(_.interrupt, _, false));

        }

        _.$slider.off('focus.slick blur.slick');

        if (_.options.arrows === true && _.slideCount > _.options.slidesToShow) {
            _.$prevArrow && _.$prevArrow.off('click.slick', _.changeSlide);
            _.$nextArrow && _.$nextArrow.off('click.slick', _.changeSlide);
        }

        _.$list.off('touchstart.slick mousedown.slick', _.swipeHandler);
        _.$list.off('touchmove.slick mousemove.slick', _.swipeHandler);
        _.$list.off('touchend.slick mouseup.slick', _.swipeHandler);
        _.$list.off('touchcancel.slick mouseleave.slick', _.swipeHandler);

        _.$list.off('click.slick', _.clickHandler);

        $(document).off(_.visibilityChange, _.visibility);

        _.cleanUpSlideEvents();

        if (_.options.accessibility === true) {
            _.$list.off('keydown.slick', _.keyHandler);
        }

        if (_.options.focusOnSelect === true) {
            $(_.$slideTrack).children().off('click.slick', _.selectHandler);
        }

        $(window).off('orientationchange.slick.slick-' + _.instanceUid, _.orientationChange);

        $(window).off('resize.slick.slick-' + _.instanceUid, _.resize);

        $('[draggable!=true]', _.$slideTrack).off('dragstart', _.preventDefault);

        $(window).off('load.slick.slick-' + _.instanceUid, _.setPosition);
        $(document).off('ready.slick.slick-' + _.instanceUid, _.setPosition);

    };

    Slick.prototype.cleanUpSlideEvents = function() {

        var _ = this;

        _.$list.off('mouseenter.slick', $.proxy(_.interrupt, _, true));
        _.$list.off('mouseleave.slick', $.proxy(_.interrupt, _, false));

    };

    Slick.prototype.cleanUpRows = function() {

        var _ = this, originalSlides;

        if(_.options.rows > 1) {
            originalSlides = _.$slides.children().children();
            originalSlides.removeAttr('style');
            _.$slider.empty().append(originalSlides);
        }

    };

    Slick.prototype.clickHandler = function(event) {

        var _ = this;

        if (_.shouldClick === false) {
            event.stopImmediatePropagation();
            event.stopPropagation();
            event.preventDefault();
        }

    };

    Slick.prototype.destroy = function(refresh) {

        var _ = this;

        _.autoPlayClear();

        _.touchObject = {};

        _.cleanUpEvents();

        $('.slick-cloned', _.$slider).detach();

        if (_.$dots) {
            _.$dots.remove();
        }


        if ( _.$prevArrow && _.$prevArrow.length ) {

            _.$prevArrow
                .removeClass('slick-disabled slick-arrow slick-hidden')
                .removeAttr('aria-hidden aria-disabled tabindex')
                .css('display','');

            if ( _.htmlExpr.test( _.options.prevArrow )) {
                _.$prevArrow.remove();
            }
        }

        if ( _.$nextArrow && _.$nextArrow.length ) {

            _.$nextArrow
                .removeClass('slick-disabled slick-arrow slick-hidden')
                .removeAttr('aria-hidden aria-disabled tabindex')
                .css('display','');

            if ( _.htmlExpr.test( _.options.nextArrow )) {
                _.$nextArrow.remove();
            }

        }


        if (_.$slides) {

            _.$slides
                .removeClass('slick-slide slick-active slick-center slick-visible slick-current')
                .removeAttr('aria-hidden')
                .removeAttr('data-slick-index')
                .each(function(){
                    $(this).attr('style', $(this).data('originalStyling'));
                });

            _.$slideTrack.children(this.options.slide).detach();

            _.$slideTrack.detach();

            _.$list.detach();

            _.$slider.append(_.$slides);
        }

        _.cleanUpRows();

        _.$slider.removeClass('slick-slider');
        _.$slider.removeClass('slick-initialized');
        _.$slider.removeClass('slick-dotted');

        _.unslicked = true;

        if(!refresh) {
            _.$slider.trigger('destroy', [_]);
        }

    };

    Slick.prototype.disableTransition = function(slide) {

        var _ = this,
            transition = {};

        transition[_.transitionType] = '';

        if (_.options.fade === false) {
            _.$slideTrack.css(transition);
        } else {
            _.$slides.eq(slide).css(transition);
        }

    };

    Slick.prototype.fadeSlide = function(slideIndex, callback) {

        var _ = this;

        if (_.cssTransitions === false) {

            _.$slides.eq(slideIndex).css({
                zIndex: _.options.zIndex
            });

            _.$slides.eq(slideIndex).animate({
                opacity: 1
            }, _.options.speed, _.options.easing, callback);

        } else {

            _.applyTransition(slideIndex);

            _.$slides.eq(slideIndex).css({
                opacity: 1,
                zIndex: _.options.zIndex
            });

            if (callback) {
                setTimeout(function() {

                    _.disableTransition(slideIndex);

                    callback.call();
                }, _.options.speed);
            }

        }

    };

    Slick.prototype.fadeSlideOut = function(slideIndex) {

        var _ = this;

        if (_.cssTransitions === false) {

            _.$slides.eq(slideIndex).animate({
                opacity: 0,
                zIndex: _.options.zIndex - 2
            }, _.options.speed, _.options.easing);

        } else {

            _.applyTransition(slideIndex);

            _.$slides.eq(slideIndex).css({
                opacity: 0,
                zIndex: _.options.zIndex - 2
            });

        }

    };

    Slick.prototype.filterSlides = Slick.prototype.slickFilter = function(filter) {

        var _ = this;

        if (filter !== null) {

            _.$slidesCache = _.$slides;

            _.unload();

            _.$slideTrack.children(this.options.slide).detach();

            _.$slidesCache.filter(filter).appendTo(_.$slideTrack);

            _.reinit();

        }

    };

    Slick.prototype.focusHandler = function() {

        var _ = this;

        _.$slider
            .off('focus.slick blur.slick')
            .on('focus.slick blur.slick',
                '*:not(.slick-arrow)', function(event) {

            event.stopImmediatePropagation();
            var $sf = $(this);

            setTimeout(function() {

                if( _.options.pauseOnFocus ) {
                    _.focussed = $sf.is(':focus');
                    _.autoPlay();
                }

            }, 0);

        });
    };

    Slick.prototype.getCurrent = Slick.prototype.slickCurrentSlide = function() {

        var _ = this;
        return _.currentSlide;

    };

    Slick.prototype.getDotCount = function() {

        var _ = this;

        var breakPoint = 0;
        var counter = 0;
        var pagerQty = 0;

        if (_.options.infinite === true) {
            while (breakPoint < _.slideCount) {
                ++pagerQty;
                breakPoint = counter + _.options.slidesToScroll;
                counter += _.options.slidesToScroll <= _.options.slidesToShow ? _.options.slidesToScroll : _.options.slidesToShow;
            }
        } else if (_.options.centerMode === true) {
            pagerQty = _.slideCount;
        } else if(!_.options.asNavFor) {
            pagerQty = 1 + Math.ceil((_.slideCount - _.options.slidesToShow) / _.options.slidesToScroll);
        }else {
            while (breakPoint < _.slideCount) {
                ++pagerQty;
                breakPoint = counter + _.options.slidesToScroll;
                counter += _.options.slidesToScroll <= _.options.slidesToShow ? _.options.slidesToScroll : _.options.slidesToShow;
            }
        }

        return pagerQty - 1;

    };

    Slick.prototype.getLeft = function(slideIndex) {

        var _ = this,
            targetLeft,
            verticalHeight,
            verticalOffset = 0,
            targetSlide;

        _.slideOffset = 0;
        verticalHeight = _.$slides.first().outerHeight(true);

        if (_.options.infinite === true) {
            if (_.slideCount > _.options.slidesToShow) {
                _.slideOffset = (_.slideWidth * _.options.slidesToShow) * -1;
                verticalOffset = (verticalHeight * _.options.slidesToShow) * -1;
            }
            if (_.slideCount % _.options.slidesToScroll !== 0) {
                if (slideIndex + _.options.slidesToScroll > _.slideCount && _.slideCount > _.options.slidesToShow) {
                    if (slideIndex > _.slideCount) {
                        _.slideOffset = ((_.options.slidesToShow - (slideIndex - _.slideCount)) * _.slideWidth) * -1;
                        verticalOffset = ((_.options.slidesToShow - (slideIndex - _.slideCount)) * verticalHeight) * -1;
                    } else {
                        _.slideOffset = ((_.slideCount % _.options.slidesToScroll) * _.slideWidth) * -1;
                        verticalOffset = ((_.slideCount % _.options.slidesToScroll) * verticalHeight) * -1;
                    }
                }
            }
        } else {
            if (slideIndex + _.options.slidesToShow > _.slideCount) {
                _.slideOffset = ((slideIndex + _.options.slidesToShow) - _.slideCount) * _.slideWidth;
                verticalOffset = ((slideIndex + _.options.slidesToShow) - _.slideCount) * verticalHeight;
            }
        }

        if (_.slideCount <= _.options.slidesToShow) {
            _.slideOffset = 0;
            verticalOffset = 0;
        }

        if (_.options.centerMode === true && _.options.infinite === true) {
            _.slideOffset += _.slideWidth * Math.floor(_.options.slidesToShow / 2) - _.slideWidth;
        } else if (_.options.centerMode === true) {
            _.slideOffset = 0;
            _.slideOffset += _.slideWidth * Math.floor(_.options.slidesToShow / 2);
        }

        if (_.options.vertical === false) {
            targetLeft = ((slideIndex * _.slideWidth) * -1) + _.slideOffset;
        } else {
            targetLeft = ((slideIndex * verticalHeight) * -1) + verticalOffset;
        }

        if (_.options.variableWidth === true) {

            if (_.slideCount <= _.options.slidesToShow || _.options.infinite === false) {
                targetSlide = _.$slideTrack.children('.slick-slide').eq(slideIndex);
            } else {
                targetSlide = _.$slideTrack.children('.slick-slide').eq(slideIndex + _.options.slidesToShow);
            }

            if (_.options.rtl === true) {
                if (targetSlide[0]) {
                    targetLeft = (_.$slideTrack.width() - targetSlide[0].offsetLeft - targetSlide.width()) * -1;
                } else {
                    targetLeft =  0;
                }
            } else {
                targetLeft = targetSlide[0] ? targetSlide[0].offsetLeft * -1 : 0;
            }

            if (_.options.centerMode === true) {
                if (_.slideCount <= _.options.slidesToShow || _.options.infinite === false) {
                    targetSlide = _.$slideTrack.children('.slick-slide').eq(slideIndex);
                } else {
                    targetSlide = _.$slideTrack.children('.slick-slide').eq(slideIndex + _.options.slidesToShow + 1);
                }

                if (_.options.rtl === true) {
                    if (targetSlide[0]) {
                        targetLeft = (_.$slideTrack.width() - targetSlide[0].offsetLeft - targetSlide.width()) * -1;
                    } else {
                        targetLeft =  0;
                    }
                } else {
                    targetLeft = targetSlide[0] ? targetSlide[0].offsetLeft * -1 : 0;
                }

                targetLeft += (_.$list.width() - targetSlide.outerWidth()) / 2;
            }
        }

        return targetLeft;

    };

    Slick.prototype.getOption = Slick.prototype.slickGetOption = function(option) {

        var _ = this;

        return _.options[option];

    };

    Slick.prototype.getNavigableIndexes = function() {

        var _ = this,
            breakPoint = 0,
            counter = 0,
            indexes = [],
            max;

        if (_.options.infinite === false) {
            max = _.slideCount;
        } else {
            breakPoint = _.options.slidesToScroll * -1;
            counter = _.options.slidesToScroll * -1;
            max = _.slideCount * 2;
        }

        while (breakPoint < max) {
            indexes.push(breakPoint);
            breakPoint = counter + _.options.slidesToScroll;
            counter += _.options.slidesToScroll <= _.options.slidesToShow ? _.options.slidesToScroll : _.options.slidesToShow;
        }

        return indexes;

    };

    Slick.prototype.getSlick = function() {

        return this;

    };

    Slick.prototype.getSlideCount = function() {

        var _ = this,
            slidesTraversed, swipedSlide, centerOffset;

        centerOffset = _.options.centerMode === true ? _.slideWidth * Math.floor(_.options.slidesToShow / 2) : 0;

        if (_.options.swipeToSlide === true) {
            _.$slideTrack.find('.slick-slide').each(function(index, slide) {
                if (slide.offsetLeft - centerOffset + ($(slide).outerWidth() / 2) > (_.swipeLeft * -1)) {
                    swipedSlide = slide;
                    return false;
                }
            });

            slidesTraversed = Math.abs($(swipedSlide).attr('data-slick-index') - _.currentSlide) || 1;

            return slidesTraversed;

        } else {
            return _.options.slidesToScroll;
        }

    };

    Slick.prototype.goTo = Slick.prototype.slickGoTo = function(slide, dontAnimate) {

        var _ = this;

        _.changeSlide({
            data: {
                message: 'index',
                index: parseInt(slide)
            }
        }, dontAnimate);

    };

    Slick.prototype.init = function(creation) {

        var _ = this;

        if (!$(_.$slider).hasClass('slick-initialized')) {

            $(_.$slider).addClass('slick-initialized');

            _.buildRows();
            _.buildOut();
            _.setProps();
            _.startLoad();
            _.loadSlider();
            _.initializeEvents();
            _.updateArrows();
            _.updateDots();
            _.checkResponsive(true);
            _.focusHandler();

        }

        if (creation) {
            _.$slider.trigger('init', [_]);
        }

        if (_.options.accessibility === true) {
            _.initADA();
        }

        if ( _.options.autoplay ) {

            _.paused = false;
            _.autoPlay();

        }

    };

    Slick.prototype.initADA = function() {
        var _ = this;
        _.$slides.add(_.$slideTrack.find('.slick-cloned')).attr({
            'aria-hidden': 'true',
            'tabindex': '-1'
        }).find('a, input, button, select').attr({
            'tabindex': '-1'
        });

        _.$slideTrack.attr('role', 'listbox');

        _.$slides.not(_.$slideTrack.find('.slick-cloned')).each(function(i) {
            $(this).attr({
                'role': 'option',
                'aria-describedby': 'slick-slide' + _.instanceUid + i + ''
            });
        });

        if (_.$dots !== null) {
            _.$dots.attr('role', 'tablist').find('li').each(function(i) {
                $(this).attr({
                    'role': 'presentation',
                    'aria-selected': 'false',
                    'aria-controls': 'navigation' + _.instanceUid + i + '',
                    'id': 'slick-slide' + _.instanceUid + i + ''
                });
            })
                .first().attr('aria-selected', 'true').end()
                .find('button').attr('role', 'button').end()
                .closest('div').attr('role', 'toolbar');
        }
        _.activateADA();

    };

    Slick.prototype.initArrowEvents = function() {

        var _ = this;

        if (_.options.arrows === true && _.slideCount > _.options.slidesToShow) {
            _.$prevArrow
               .off('click.slick')
               .on('click.slick', {
                    message: 'previous'
               }, _.changeSlide);
            _.$nextArrow
               .off('click.slick')
               .on('click.slick', {
                    message: 'next'
               }, _.changeSlide);
        }

    };

    Slick.prototype.initDotEvents = function() {

        var _ = this;

        if (_.options.dots === true && _.slideCount > _.options.slidesToShow) {
            $('li', _.$dots).on('click.slick', {
                message: 'index'
            }, _.changeSlide);
        }

        if ( _.options.dots === true && _.options.pauseOnDotsHover === true ) {

            $('li', _.$dots)
                .on('mouseenter.slick', $.proxy(_.interrupt, _, true))
                .on('mouseleave.slick', $.proxy(_.interrupt, _, false));

        }

    };

    Slick.prototype.initSlideEvents = function() {

        var _ = this;

        if ( _.options.pauseOnHover ) {

            _.$list.on('mouseenter.slick', $.proxy(_.interrupt, _, true));
            _.$list.on('mouseleave.slick', $.proxy(_.interrupt, _, false));

        }

    };

    Slick.prototype.initializeEvents = function() {

        var _ = this;

        _.initArrowEvents();

        _.initDotEvents();
        _.initSlideEvents();

        _.$list.on('touchstart.slick mousedown.slick', {
            action: 'start'
        }, _.swipeHandler);
        _.$list.on('touchmove.slick mousemove.slick', {
            action: 'move'
        }, _.swipeHandler);
        _.$list.on('touchend.slick mouseup.slick', {
            action: 'end'
        }, _.swipeHandler);
        _.$list.on('touchcancel.slick mouseleave.slick', {
            action: 'end'
        }, _.swipeHandler);

        _.$list.on('click.slick', _.clickHandler);

        $(document).on(_.visibilityChange, $.proxy(_.visibility, _));

        if (_.options.accessibility === true) {
            _.$list.on('keydown.slick', _.keyHandler);
        }

        if (_.options.focusOnSelect === true) {
            $(_.$slideTrack).children().on('click.slick', _.selectHandler);
        }

        $(window).on('orientationchange.slick.slick-' + _.instanceUid, $.proxy(_.orientationChange, _));

        $(window).on('resize.slick.slick-' + _.instanceUid, $.proxy(_.resize, _));

        $('[draggable!=true]', _.$slideTrack).on('dragstart', _.preventDefault);

        $(window).on('load.slick.slick-' + _.instanceUid, _.setPosition);
        $(document).on('ready.slick.slick-' + _.instanceUid, _.setPosition);

    };

    Slick.prototype.initUI = function() {

        var _ = this;

        if (_.options.arrows === true && _.slideCount > _.options.slidesToShow) {

            _.$prevArrow.show();
            _.$nextArrow.show();

        }

        if (_.options.dots === true && _.slideCount > _.options.slidesToShow) {

            _.$dots.show();

        }

    };

    Slick.prototype.keyHandler = function(event) {

        var _ = this;
         //Dont slide if the cursor is inside the form fields and arrow keys are pressed
        if(!event.target.tagName.match('TEXTAREA|INPUT|SELECT')) {
            if (event.keyCode === 37 && _.options.accessibility === true) {
                _.changeSlide({
                    data: {
                        message: _.options.rtl === true ? 'next' :  'previous'
                    }
                });
            } else if (event.keyCode === 39 && _.options.accessibility === true) {
                _.changeSlide({
                    data: {
                        message: _.options.rtl === true ? 'previous' : 'next'
                    }
                });
            }
        }

    };

    Slick.prototype.lazyLoad = function() {

        var _ = this,
            loadRange, cloneRange, rangeStart, rangeEnd;

        function loadImages(imagesScope) {

            $('img[data-lazy]', imagesScope).each(function() {

                var image = $(this),
                    imageSource = $(this).attr('data-lazy'),
                    imageToLoad = document.createElement('img');

                imageToLoad.onload = function() {

                    image
                        .animate({ opacity: 0 }, 100, function() {
                            image
                                .attr('src', imageSource)
                                .animate({ opacity: 1 }, 200, function() {
                                    image
                                        .removeAttr('data-lazy')
                                        .removeClass('slick-loading');
                                });
                            _.$slider.trigger('lazyLoaded', [_, image, imageSource]);
                        });

                };

                imageToLoad.onerror = function() {

                    image
                        .removeAttr( 'data-lazy' )
                        .removeClass( 'slick-loading' )
                        .addClass( 'slick-lazyload-error' );

                    _.$slider.trigger('lazyLoadError', [ _, image, imageSource ]);

                };

                imageToLoad.src = imageSource;

            });

        }

        if (_.options.centerMode === true) {
            if (_.options.infinite === true) {
                rangeStart = _.currentSlide + (_.options.slidesToShow / 2 + 1);
                rangeEnd = rangeStart + _.options.slidesToShow + 2;
            } else {
                rangeStart = Math.max(0, _.currentSlide - (_.options.slidesToShow / 2 + 1));
                rangeEnd = 2 + (_.options.slidesToShow / 2 + 1) + _.currentSlide;
            }
        } else {
            rangeStart = _.options.infinite ? _.options.slidesToShow + _.currentSlide : _.currentSlide;
            rangeEnd = Math.ceil(rangeStart + _.options.slidesToShow);
            if (_.options.fade === true) {
                if (rangeStart > 0) rangeStart--;
                if (rangeEnd <= _.slideCount) rangeEnd++;
            }
        }

        loadRange = _.$slider.find('.slick-slide').slice(rangeStart, rangeEnd);
        loadImages(loadRange);

        if (_.slideCount <= _.options.slidesToShow) {
            cloneRange = _.$slider.find('.slick-slide');
            loadImages(cloneRange);
        } else
        if (_.currentSlide >= _.slideCount - _.options.slidesToShow) {
            cloneRange = _.$slider.find('.slick-cloned').slice(0, _.options.slidesToShow);
            loadImages(cloneRange);
        } else if (_.currentSlide === 0) {
            cloneRange = _.$slider.find('.slick-cloned').slice(_.options.slidesToShow * -1);
            loadImages(cloneRange);
        }

    };

    Slick.prototype.loadSlider = function() {

        var _ = this;

        _.setPosition();

        _.$slideTrack.css({
            opacity: 1
        });

        _.$slider.removeClass('slick-loading');

        _.initUI();

        if (_.options.lazyLoad === 'progressive') {
            _.progressiveLazyLoad();
        }

    };

    Slick.prototype.next = Slick.prototype.slickNext = function() {

        var _ = this;

        _.changeSlide({
            data: {
                message: 'next'
            }
        });

    };

    Slick.prototype.orientationChange = function() {

        var _ = this;

        _.checkResponsive();
        _.setPosition();

    };

    Slick.prototype.pause = Slick.prototype.slickPause = function() {

        var _ = this;

        _.autoPlayClear();
        _.paused = true;

    };

    Slick.prototype.play = Slick.prototype.slickPlay = function() {

        var _ = this;

        _.autoPlay();
        _.options.autoplay = true;
        _.paused = false;
        _.focussed = false;
        _.interrupted = false;

    };

    Slick.prototype.postSlide = function(index) {

        var _ = this;

        if( !_.unslicked ) {

            _.$slider.trigger('afterChange', [_, index]);

            _.animating = false;

            _.setPosition();

            _.swipeLeft = null;

            if ( _.options.autoplay ) {
                _.autoPlay();
            }

            if (_.options.accessibility === true) {
                _.initADA();
            }

        }

    };

    Slick.prototype.prev = Slick.prototype.slickPrev = function() {

        var _ = this;

        _.changeSlide({
            data: {
                message: 'previous'
            }
        });

    };

    Slick.prototype.preventDefault = function(event) {

        event.preventDefault();

    };

    Slick.prototype.progressiveLazyLoad = function( tryCount ) {

        tryCount = tryCount || 1;

        var _ = this,
            $imgsToLoad = $( 'img[data-lazy]', _.$slider ),
            image,
            imageSource,
            imageToLoad;

        if ( $imgsToLoad.length ) {

            image = $imgsToLoad.first();
            imageSource = image.attr('data-lazy');
            imageToLoad = document.createElement('img');

            imageToLoad.onload = function() {

                image
                    .attr( 'src', imageSource )
                    .removeAttr('data-lazy')
                    .removeClass('slick-loading');

                if ( _.options.adaptiveHeight === true ) {
                    _.setPosition();
                }

                _.$slider.trigger('lazyLoaded', [ _, image, imageSource ]);
                _.progressiveLazyLoad();

            };

            imageToLoad.onerror = function() {

                if ( tryCount < 3 ) {

                    /**
                     * try to load the image 3 times,
                     * leave a slight delay so we don't get
                     * servers blocking the request.
                     */
                    setTimeout( function() {
                        _.progressiveLazyLoad( tryCount + 1 );
                    }, 500 );

                } else {

                    image
                        .removeAttr( 'data-lazy' )
                        .removeClass( 'slick-loading' )
                        .addClass( 'slick-lazyload-error' );

                    _.$slider.trigger('lazyLoadError', [ _, image, imageSource ]);

                    _.progressiveLazyLoad();

                }

            };

            imageToLoad.src = imageSource;

        } else {

            _.$slider.trigger('allImagesLoaded', [ _ ]);

        }

    };

    Slick.prototype.refresh = function( initializing ) {

        var _ = this, currentSlide, lastVisibleIndex;

        lastVisibleIndex = _.slideCount - _.options.slidesToShow;

        // in non-infinite sliders, we don't want to go past the
        // last visible index.
        if( !_.options.infinite && ( _.currentSlide > lastVisibleIndex )) {
            _.currentSlide = lastVisibleIndex;
        }

        // if less slides than to show, go to start.
        if ( _.slideCount <= _.options.slidesToShow ) {
            _.currentSlide = 0;

        }

        currentSlide = _.currentSlide;

        _.destroy(true);

        $.extend(_, _.initials, { currentSlide: currentSlide });

        _.init();

        if( !initializing ) {

            _.changeSlide({
                data: {
                    message: 'index',
                    index: currentSlide
                }
            }, false);

        }

    };

    Slick.prototype.registerBreakpoints = function() {

        var _ = this, breakpoint, currentBreakpoint, l,
            responsiveSettings = _.options.responsive || null;

        if ( $.type(responsiveSettings) === 'array' && responsiveSettings.length ) {

            _.respondTo = _.options.respondTo || 'window';

            for ( breakpoint in responsiveSettings ) {

                l = _.breakpoints.length-1;
                currentBreakpoint = responsiveSettings[breakpoint].breakpoint;

                if (responsiveSettings.hasOwnProperty(breakpoint)) {

                    // loop through the breakpoints and cut out any existing
                    // ones with the same breakpoint number, we don't want dupes.
                    while( l >= 0 ) {
                        if( _.breakpoints[l] && _.breakpoints[l] === currentBreakpoint ) {
                            _.breakpoints.splice(l,1);
                        }
                        l--;
                    }

                    _.breakpoints.push(currentBreakpoint);
                    _.breakpointSettings[currentBreakpoint] = responsiveSettings[breakpoint].settings;

                }

            }

            _.breakpoints.sort(function(a, b) {
                return ( _.options.mobileFirst ) ? a-b : b-a;
            });

        }

    };

    Slick.prototype.reinit = function() {

        var _ = this;

        _.$slides =
            _.$slideTrack
                .children(_.options.slide)
                .addClass('slick-slide');

        _.slideCount = _.$slides.length;

        if (_.currentSlide >= _.slideCount && _.currentSlide !== 0) {
            _.currentSlide = _.currentSlide - _.options.slidesToScroll;
        }

        if (_.slideCount <= _.options.slidesToShow) {
            _.currentSlide = 0;
        }

        _.registerBreakpoints();

        _.setProps();
        _.setupInfinite();
        _.buildArrows();
        _.updateArrows();
        _.initArrowEvents();
        _.buildDots();
        _.updateDots();
        _.initDotEvents();
        _.cleanUpSlideEvents();
        _.initSlideEvents();

        _.checkResponsive(false, true);

        if (_.options.focusOnSelect === true) {
            $(_.$slideTrack).children().on('click.slick', _.selectHandler);
        }

        _.setSlideClasses(typeof _.currentSlide === 'number' ? _.currentSlide : 0);

        _.setPosition();
        _.focusHandler();

        _.paused = !_.options.autoplay;
        _.autoPlay();

        _.$slider.trigger('reInit', [_]);

    };

    Slick.prototype.resize = function() {

        var _ = this;

        if ($(window).width() !== _.windowWidth) {
            clearTimeout(_.windowDelay);
            _.windowDelay = window.setTimeout(function() {
                _.windowWidth = $(window).width();
                _.checkResponsive();
                if( !_.unslicked ) { _.setPosition(); }
            }, 50);
        }
    };

    Slick.prototype.removeSlide = Slick.prototype.slickRemove = function(index, removeBefore, removeAll) {

        var _ = this;

        if (typeof(index) === 'boolean') {
            removeBefore = index;
            index = removeBefore === true ? 0 : _.slideCount - 1;
        } else {
            index = removeBefore === true ? --index : index;
        }

        if (_.slideCount < 1 || index < 0 || index > _.slideCount - 1) {
            return false;
        }

        _.unload();

        if (removeAll === true) {
            _.$slideTrack.children().remove();
        } else {
            _.$slideTrack.children(this.options.slide).eq(index).remove();
        }

        _.$slides = _.$slideTrack.children(this.options.slide);

        _.$slideTrack.children(this.options.slide).detach();

        _.$slideTrack.append(_.$slides);

        _.$slidesCache = _.$slides;

        _.reinit();

    };

    Slick.prototype.setCSS = function(position) {

        var _ = this,
            positionProps = {},
            x, y;

        if (_.options.rtl === true) {
            position = -position;
        }
        x = _.positionProp == 'left' ? Math.ceil(position) + 'px' : '0px';
        y = _.positionProp == 'top' ? Math.ceil(position) + 'px' : '0px';

        positionProps[_.positionProp] = position;

        if (_.transformsEnabled === false) {
            _.$slideTrack.css(positionProps);
        } else {
            positionProps = {};
            if (_.cssTransitions === false) {
                positionProps[_.animType] = 'translate(' + x + ', ' + y + ')';
                _.$slideTrack.css(positionProps);
            } else {
                positionProps[_.animType] = 'translate3d(' + x + ', ' + y + ', 0px)';
                _.$slideTrack.css(positionProps);
            }
        }

    };

    Slick.prototype.setDimensions = function() {

        var _ = this;

        if (_.options.vertical === false) {
            if (_.options.centerMode === true) {
                _.$list.css({
                    padding: ('0px ' + _.options.centerPadding)
                });
            }
        } else {
            _.$list.height(_.$slides.first().outerHeight(true) * _.options.slidesToShow);
            if (_.options.centerMode === true) {
                _.$list.css({
                    padding: (_.options.centerPadding + ' 0px')
                });
            }
        }

        _.listWidth = _.$list.width();
        _.listHeight = _.$list.height();


        if (_.options.vertical === false && _.options.variableWidth === false) {
            _.slideWidth = Math.ceil(_.listWidth / _.options.slidesToShow);
            _.$slideTrack.width(Math.ceil((_.slideWidth * _.$slideTrack.children('.slick-slide').length)));

        } else if (_.options.variableWidth === true) {
            _.$slideTrack.width(5000 * _.slideCount);
        } else {
            _.slideWidth = Math.ceil(_.listWidth);
            _.$slideTrack.height(Math.ceil((_.$slides.first().outerHeight(true) * _.$slideTrack.children('.slick-slide').length)));
        }

        var offset = _.$slides.first().outerWidth(true) - _.$slides.first().width();
        if (_.options.variableWidth === false) _.$slideTrack.children('.slick-slide').width(_.slideWidth - offset);

    };

    Slick.prototype.setFade = function() {

        var _ = this,
            targetLeft;

        _.$slides.each(function(index, element) {
            targetLeft = (_.slideWidth * index) * -1;
            if (_.options.rtl === true) {
                $(element).css({
                    position: 'relative',
                    right: targetLeft,
                    top: 0,
                    zIndex: _.options.zIndex - 2,
                    opacity: 0
                });
            } else {
                $(element).css({
                    position: 'relative',
                    left: targetLeft,
                    top: 0,
                    zIndex: _.options.zIndex - 2,
                    opacity: 0
                });
            }
        });

        _.$slides.eq(_.currentSlide).css({
            zIndex: _.options.zIndex - 1,
            opacity: 1
        });

    };

    Slick.prototype.setHeight = function() {

        var _ = this;

        if (_.options.slidesToShow === 1 && _.options.adaptiveHeight === true && _.options.vertical === false) {
            var targetHeight = _.$slides.eq(_.currentSlide).outerHeight(true);
            _.$list.css('height', targetHeight);
        }

    };

    Slick.prototype.setOption =
    Slick.prototype.slickSetOption = function() {

        /**
         * accepts arguments in format of:
         *
         *  - for changing a single option's value:
         *     .slick("setOption", option, value, refresh )
         *
         *  - for changing a set of responsive options:
         *     .slick("setOption", 'responsive', [{}, ...], refresh )
         *
         *  - for updating multiple values at once (not responsive)
         *     .slick("setOption", { 'option': value, ... }, refresh )
         */

        var _ = this, l, item, option, value, refresh = false, type;

        if( $.type( arguments[0] ) === 'object' ) {

            option =  arguments[0];
            refresh = arguments[1];
            type = 'multiple';

        } else if ( $.type( arguments[0] ) === 'string' ) {

            option =  arguments[0];
            value = arguments[1];
            refresh = arguments[2];

            if ( arguments[0] === 'responsive' && $.type( arguments[1] ) === 'array' ) {

                type = 'responsive';

            } else if ( typeof arguments[1] !== 'undefined' ) {

                type = 'single';

            }

        }

        if ( type === 'single' ) {

            _.options[option] = value;


        } else if ( type === 'multiple' ) {

            $.each( option , function( opt, val ) {

                _.options[opt] = val;

            });


        } else if ( type === 'responsive' ) {

            for ( item in value ) {

                if( $.type( _.options.responsive ) !== 'array' ) {

                    _.options.responsive = [ value[item] ];

                } else {

                    l = _.options.responsive.length-1;

                    // loop through the responsive object and splice out duplicates.
                    while( l >= 0 ) {

                        if( _.options.responsive[l].breakpoint === value[item].breakpoint ) {

                            _.options.responsive.splice(l,1);

                        }

                        l--;

                    }

                    _.options.responsive.push( value[item] );

                }

            }

        }

        if ( refresh ) {

            _.unload();
            _.reinit();

        }

    };

    Slick.prototype.setPosition = function() {

        var _ = this;

        _.setDimensions();

        _.setHeight();

        if (_.options.fade === false) {
            _.setCSS(_.getLeft(_.currentSlide));
        } else {
            _.setFade();
        }

        _.$slider.trigger('setPosition', [_]);

    };

    Slick.prototype.setProps = function() {

        var _ = this,
            bodyStyle = document.body.style;

        _.positionProp = _.options.vertical === true ? 'top' : 'left';

        if (_.positionProp === 'top') {
            _.$slider.addClass('slick-vertical');
        } else {
            _.$slider.removeClass('slick-vertical');
        }

        if (bodyStyle.WebkitTransition !== undefined ||
            bodyStyle.MozTransition !== undefined ||
            bodyStyle.msTransition !== undefined) {
            if (_.options.useCSS === true) {
                _.cssTransitions = true;
            }
        }

        if ( _.options.fade ) {
            if ( typeof _.options.zIndex === 'number' ) {
                if( _.options.zIndex < 3 ) {
                    _.options.zIndex = 3;
                }
            } else {
                _.options.zIndex = _.defaults.zIndex;
            }
        }

        if (bodyStyle.OTransform !== undefined) {
            _.animType = 'OTransform';
            _.transformType = '-o-transform';
            _.transitionType = 'OTransition';
            if (bodyStyle.perspectiveProperty === undefined && bodyStyle.webkitPerspective === undefined) _.animType = false;
        }
        if (bodyStyle.MozTransform !== undefined) {
            _.animType = 'MozTransform';
            _.transformType = '-moz-transform';
            _.transitionType = 'MozTransition';
            if (bodyStyle.perspectiveProperty === undefined && bodyStyle.MozPerspective === undefined) _.animType = false;
        }
        if (bodyStyle.webkitTransform !== undefined) {
            _.animType = 'webkitTransform';
            _.transformType = '-webkit-transform';
            _.transitionType = 'webkitTransition';
            if (bodyStyle.perspectiveProperty === undefined && bodyStyle.webkitPerspective === undefined) _.animType = false;
        }
        if (bodyStyle.msTransform !== undefined) {
            _.animType = 'msTransform';
            _.transformType = '-ms-transform';
            _.transitionType = 'msTransition';
            if (bodyStyle.msTransform === undefined) _.animType = false;
        }
        if (bodyStyle.transform !== undefined && _.animType !== false) {
            _.animType = 'transform';
            _.transformType = 'transform';
            _.transitionType = 'transition';
        }
        _.transformsEnabled = _.options.useTransform && (_.animType !== null && _.animType !== false);
    };


    Slick.prototype.setSlideClasses = function(index) {

        var _ = this,
            centerOffset, allSlides, indexOffset, remainder;

        allSlides = _.$slider
            .find('.slick-slide')
            .removeClass('slick-active slick-center slick-current')
            .attr('aria-hidden', 'true');

        _.$slides
            .eq(index)
            .addClass('slick-current');

        if (_.options.centerMode === true) {

            centerOffset = Math.floor(_.options.slidesToShow / 2);

            if (_.options.infinite === true) {

                if (index >= centerOffset && index <= (_.slideCount - 1) - centerOffset) {

                    _.$slides
                        .slice(index - centerOffset, index + centerOffset + 1)
                        .addClass('slick-active')
                        .attr('aria-hidden', 'false');

                } else {

                    indexOffset = _.options.slidesToShow + index;
                    allSlides
                        .slice(indexOffset - centerOffset + 1, indexOffset + centerOffset + 2)
                        .addClass('slick-active')
                        .attr('aria-hidden', 'false');

                }

                if (index === 0) {

                    allSlides
                        .eq(allSlides.length - 1 - _.options.slidesToShow)
                        .addClass('slick-center');

                } else if (index === _.slideCount - 1) {

                    allSlides
                        .eq(_.options.slidesToShow)
                        .addClass('slick-center');

                }

            }

            _.$slides
                .eq(index)
                .addClass('slick-center');

        } else {

            if (index >= 0 && index <= (_.slideCount - _.options.slidesToShow)) {

                _.$slides
                    .slice(index, index + _.options.slidesToShow)
                    .addClass('slick-active')
                    .attr('aria-hidden', 'false');

            } else if (allSlides.length <= _.options.slidesToShow) {

                allSlides
                    .addClass('slick-active')
                    .attr('aria-hidden', 'false');

            } else {

                remainder = _.slideCount % _.options.slidesToShow;
                indexOffset = _.options.infinite === true ? _.options.slidesToShow + index : index;

                if (_.options.slidesToShow == _.options.slidesToScroll && (_.slideCount - index) < _.options.slidesToShow) {

                    allSlides
                        .slice(indexOffset - (_.options.slidesToShow - remainder), indexOffset + remainder)
                        .addClass('slick-active')
                        .attr('aria-hidden', 'false');

                } else {

                    allSlides
                        .slice(indexOffset, indexOffset + _.options.slidesToShow)
                        .addClass('slick-active')
                        .attr('aria-hidden', 'false');

                }

            }

        }

        if (_.options.lazyLoad === 'ondemand') {
            _.lazyLoad();
        }

    };

    Slick.prototype.setupInfinite = function() {

        var _ = this,
            i, slideIndex, infiniteCount;

        if (_.options.fade === true) {
            _.options.centerMode = false;
        }

        if (_.options.infinite === true && _.options.fade === false) {

            slideIndex = null;

            if (_.slideCount > _.options.slidesToShow) {

                if (_.options.centerMode === true) {
                    infiniteCount = _.options.slidesToShow + 1;
                } else {
                    infiniteCount = _.options.slidesToShow;
                }

                for (i = _.slideCount; i > (_.slideCount -
                        infiniteCount); i -= 1) {
                    slideIndex = i - 1;
                    $(_.$slides[slideIndex]).clone(true).attr('id', '')
                        .attr('data-slick-index', slideIndex - _.slideCount)
                        .prependTo(_.$slideTrack).addClass('slick-cloned');
                }
                for (i = 0; i < infiniteCount; i += 1) {
                    slideIndex = i;
                    $(_.$slides[slideIndex]).clone(true).attr('id', '')
                        .attr('data-slick-index', slideIndex + _.slideCount)
                        .appendTo(_.$slideTrack).addClass('slick-cloned');
                }
                _.$slideTrack.find('.slick-cloned').find('[id]').each(function() {
                    $(this).attr('id', '');
                });

            }

        }

    };

    Slick.prototype.interrupt = function( toggle ) {

        var _ = this;

        if( !toggle ) {
            _.autoPlay();
        }
        _.interrupted = toggle;

    };

    Slick.prototype.selectHandler = function(event) {

        var _ = this;

        var targetElement =
            $(event.target).is('.slick-slide') ?
                $(event.target) :
                $(event.target).parents('.slick-slide');

        var index = parseInt(targetElement.attr('data-slick-index'));

        if (!index) index = 0;

        if (_.slideCount <= _.options.slidesToShow) {

            _.setSlideClasses(index);
            _.asNavFor(index);
            return;

        }

        _.slideHandler(index);

    };

    Slick.prototype.slideHandler = function(index, sync, dontAnimate) {

        var targetSlide, animSlide, oldSlide, slideLeft, targetLeft = null,
            _ = this, navTarget;

        sync = sync || false;

        if (_.animating === true && _.options.waitForAnimate === true) {
            return;
        }

        if (_.options.fade === true && _.currentSlide === index) {
            return;
        }

        if (_.slideCount <= _.options.slidesToShow) {
            return;
        }

        if (sync === false) {
            _.asNavFor(index);
        }

        targetSlide = index;
        targetLeft = _.getLeft(targetSlide);
        slideLeft = _.getLeft(_.currentSlide);

        _.currentLeft = _.swipeLeft === null ? slideLeft : _.swipeLeft;

        if (_.options.infinite === false && _.options.centerMode === false && (index < 0 || index > _.getDotCount() * _.options.slidesToScroll)) {
            if (_.options.fade === false) {
                targetSlide = _.currentSlide;
                if (dontAnimate !== true) {
                    _.animateSlide(slideLeft, function() {
                        _.postSlide(targetSlide);
                    });
                } else {
                    _.postSlide(targetSlide);
                }
            }
            return;
        } else if (_.options.infinite === false && _.options.centerMode === true && (index < 0 || index > (_.slideCount - _.options.slidesToScroll))) {
            if (_.options.fade === false) {
                targetSlide = _.currentSlide;
                if (dontAnimate !== true) {
                    _.animateSlide(slideLeft, function() {
                        _.postSlide(targetSlide);
                    });
                } else {
                    _.postSlide(targetSlide);
                }
            }
            return;
        }

        if ( _.options.autoplay ) {
            clearInterval(_.autoPlayTimer);
        }

        if (targetSlide < 0) {
            if (_.slideCount % _.options.slidesToScroll !== 0) {
                animSlide = _.slideCount - (_.slideCount % _.options.slidesToScroll);
            } else {
                animSlide = _.slideCount + targetSlide;
            }
        } else if (targetSlide >= _.slideCount) {
            if (_.slideCount % _.options.slidesToScroll !== 0) {
                animSlide = 0;
            } else {
                animSlide = targetSlide - _.slideCount;
            }
        } else {
            animSlide = targetSlide;
        }

        _.animating = true;

        _.$slider.trigger('beforeChange', [_, _.currentSlide, animSlide]);

        oldSlide = _.currentSlide;
        _.currentSlide = animSlide;

        _.setSlideClasses(_.currentSlide);

        if ( _.options.asNavFor ) {

            navTarget = _.getNavTarget();
            navTarget = navTarget.slick('getSlick');

            if ( navTarget.slideCount <= navTarget.options.slidesToShow ) {
                navTarget.setSlideClasses(_.currentSlide);
            }

        }

        _.updateDots();
        _.updateArrows();

        if (_.options.fade === true) {
            if (dontAnimate !== true) {

                _.fadeSlideOut(oldSlide);

                _.fadeSlide(animSlide, function() {
                    _.postSlide(animSlide);
                });

            } else {
                _.postSlide(animSlide);
            }
            _.animateHeight();
            return;
        }

        if (dontAnimate !== true) {
            _.animateSlide(targetLeft, function() {
                _.postSlide(animSlide);
            });
        } else {
            _.postSlide(animSlide);
        }

    };

    Slick.prototype.startLoad = function() {

        var _ = this;

        if (_.options.arrows === true && _.slideCount > _.options.slidesToShow) {

            _.$prevArrow.hide();
            _.$nextArrow.hide();

        }

        if (_.options.dots === true && _.slideCount > _.options.slidesToShow) {

            _.$dots.hide();

        }

        _.$slider.addClass('slick-loading');

    };

    Slick.prototype.swipeDirection = function() {

        var xDist, yDist, r, swipeAngle, _ = this;

        xDist = _.touchObject.startX - _.touchObject.curX;
        yDist = _.touchObject.startY - _.touchObject.curY;
        r = Math.atan2(yDist, xDist);

        swipeAngle = Math.round(r * 180 / Math.PI);
        if (swipeAngle < 0) {
            swipeAngle = 360 - Math.abs(swipeAngle);
        }

        if ((swipeAngle <= 45) && (swipeAngle >= 0)) {
            return (_.options.rtl === false ? 'left' : 'right');
        }
        if ((swipeAngle <= 360) && (swipeAngle >= 315)) {
            return (_.options.rtl === false ? 'left' : 'right');
        }
        if ((swipeAngle >= 135) && (swipeAngle <= 225)) {
            return (_.options.rtl === false ? 'right' : 'left');
        }
        if (_.options.verticalSwiping === true) {
            if ((swipeAngle >= 35) && (swipeAngle <= 135)) {
                return 'down';
            } else {
                return 'up';
            }
        }

        return 'vertical';

    };

    Slick.prototype.swipeEnd = function(event) {

        var _ = this,
            slideCount,
            direction;

        _.dragging = false;
        _.interrupted = false;
        _.shouldClick = ( _.touchObject.swipeLength > 10 ) ? false : true;

        if ( _.touchObject.curX === undefined ) {
            return false;
        }

        if ( _.touchObject.edgeHit === true ) {
            _.$slider.trigger('edge', [_, _.swipeDirection() ]);
        }

        if ( _.touchObject.swipeLength >= _.touchObject.minSwipe ) {

            direction = _.swipeDirection();

            switch ( direction ) {

                case 'left':
                case 'down':

                    slideCount =
                        _.options.swipeToSlide ?
                            _.checkNavigable( _.currentSlide + _.getSlideCount() ) :
                            _.currentSlide + _.getSlideCount();

                    _.currentDirection = 0;

                    break;

                case 'right':
                case 'up':

                    slideCount =
                        _.options.swipeToSlide ?
                            _.checkNavigable( _.currentSlide - _.getSlideCount() ) :
                            _.currentSlide - _.getSlideCount();

                    _.currentDirection = 1;

                    break;

                default:


            }

            if( direction != 'vertical' ) {

                _.slideHandler( slideCount );
                _.touchObject = {};
                _.$slider.trigger('swipe', [_, direction ]);

            }

        } else {

            if ( _.touchObject.startX !== _.touchObject.curX ) {

                _.slideHandler( _.currentSlide );
                _.touchObject = {};

            }

        }

    };

    Slick.prototype.swipeHandler = function(event) {

        var _ = this;

        if ((_.options.swipe === false) || ('ontouchend' in document && _.options.swipe === false)) {
            return;
        } else if (_.options.draggable === false && event.type.indexOf('mouse') !== -1) {
            return;
        }

        _.touchObject.fingerCount = event.originalEvent && event.originalEvent.touches !== undefined ?
            event.originalEvent.touches.length : 1;

        _.touchObject.minSwipe = _.listWidth / _.options
            .touchThreshold;

        if (_.options.verticalSwiping === true) {
            _.touchObject.minSwipe = _.listHeight / _.options
                .touchThreshold;
        }

        switch (event.data.action) {

            case 'start':
                _.swipeStart(event);
                break;

            case 'move':
                _.swipeMove(event);
                break;

            case 'end':
                _.swipeEnd(event);
                break;

        }

    };

    Slick.prototype.swipeMove = function(event) {

        var _ = this,
            edgeWasHit = false,
            curLeft, swipeDirection, swipeLength, positionOffset, touches;

        touches = event.originalEvent !== undefined ? event.originalEvent.touches : null;

        if (!_.dragging || touches && touches.length !== 1) {
            return false;
        }

        curLeft = _.getLeft(_.currentSlide);

        _.touchObject.curX = touches !== undefined ? touches[0].pageX : event.clientX;
        _.touchObject.curY = touches !== undefined ? touches[0].pageY : event.clientY;

        _.touchObject.swipeLength = Math.round(Math.sqrt(
            Math.pow(_.touchObject.curX - _.touchObject.startX, 2)));

        if (_.options.verticalSwiping === true) {
            _.touchObject.swipeLength = Math.round(Math.sqrt(
                Math.pow(_.touchObject.curY - _.touchObject.startY, 2)));
        }

        swipeDirection = _.swipeDirection();

        if (swipeDirection === 'vertical') {
            return;
        }

        if (event.originalEvent !== undefined && _.touchObject.swipeLength > 4) {
            event.preventDefault();
        }

        positionOffset = (_.options.rtl === false ? 1 : -1) * (_.touchObject.curX > _.touchObject.startX ? 1 : -1);
        if (_.options.verticalSwiping === true) {
            positionOffset = _.touchObject.curY > _.touchObject.startY ? 1 : -1;
        }


        swipeLength = _.touchObject.swipeLength;

        _.touchObject.edgeHit = false;

        if (_.options.infinite === false) {
            if ((_.currentSlide === 0 && swipeDirection === 'right') || (_.currentSlide >= _.getDotCount() && swipeDirection === 'left')) {
                swipeLength = _.touchObject.swipeLength * _.options.edgeFriction;
                _.touchObject.edgeHit = true;
            }
        }

        if (_.options.vertical === false) {
            _.swipeLeft = curLeft + swipeLength * positionOffset;
        } else {
            _.swipeLeft = curLeft + (swipeLength * (_.$list.height() / _.listWidth)) * positionOffset;
        }
        if (_.options.verticalSwiping === true) {
            _.swipeLeft = curLeft + swipeLength * positionOffset;
        }

        if (_.options.fade === true || _.options.touchMove === false) {
            return false;
        }

        if (_.animating === true) {
            _.swipeLeft = null;
            return false;
        }

        _.setCSS(_.swipeLeft);

    };

    Slick.prototype.swipeStart = function(event) {

        var _ = this,
            touches;

        _.interrupted = true;

        if (_.touchObject.fingerCount !== 1 || _.slideCount <= _.options.slidesToShow) {
            _.touchObject = {};
            return false;
        }

        if (event.originalEvent !== undefined && event.originalEvent.touches !== undefined) {
            touches = event.originalEvent.touches[0];
        }

        _.touchObject.startX = _.touchObject.curX = touches !== undefined ? touches.pageX : event.clientX;
        _.touchObject.startY = _.touchObject.curY = touches !== undefined ? touches.pageY : event.clientY;

        _.dragging = true;

    };

    Slick.prototype.unfilterSlides = Slick.prototype.slickUnfilter = function() {

        var _ = this;

        if (_.$slidesCache !== null) {

            _.unload();

            _.$slideTrack.children(this.options.slide).detach();

            _.$slidesCache.appendTo(_.$slideTrack);

            _.reinit();

        }

    };

    Slick.prototype.unload = function() {

        var _ = this;

        $('.slick-cloned', _.$slider).remove();

        if (_.$dots) {
            _.$dots.remove();
        }

        if (_.$prevArrow && _.htmlExpr.test(_.options.prevArrow)) {
            _.$prevArrow.remove();
        }

        if (_.$nextArrow && _.htmlExpr.test(_.options.nextArrow)) {
            _.$nextArrow.remove();
        }

        _.$slides
            .removeClass('slick-slide slick-active slick-visible slick-current')
            .attr('aria-hidden', 'true')
            .css('width', '');

    };

    Slick.prototype.unslick = function(fromBreakpoint) {

        var _ = this;
        _.$slider.trigger('unslick', [_, fromBreakpoint]);
        _.destroy();

    };

    Slick.prototype.updateArrows = function() {

        var _ = this,
            centerOffset;

        centerOffset = Math.floor(_.options.slidesToShow / 2);

        if ( _.options.arrows === true &&
            _.slideCount > _.options.slidesToShow &&
            !_.options.infinite ) {

            _.$prevArrow.removeClass('slick-disabled').attr('aria-disabled', 'false');
            _.$nextArrow.removeClass('slick-disabled').attr('aria-disabled', 'false');

            if (_.currentSlide === 0) {

                _.$prevArrow.addClass('slick-disabled').attr('aria-disabled', 'true');
                _.$nextArrow.removeClass('slick-disabled').attr('aria-disabled', 'false');

            } else if (_.currentSlide >= _.slideCount - _.options.slidesToShow && _.options.centerMode === false) {

                _.$nextArrow.addClass('slick-disabled').attr('aria-disabled', 'true');
                _.$prevArrow.removeClass('slick-disabled').attr('aria-disabled', 'false');

            } else if (_.currentSlide >= _.slideCount - 1 && _.options.centerMode === true) {

                _.$nextArrow.addClass('slick-disabled').attr('aria-disabled', 'true');
                _.$prevArrow.removeClass('slick-disabled').attr('aria-disabled', 'false');

            }

        }

    };

    Slick.prototype.updateDots = function() {

        var _ = this;

        if (_.$dots !== null) {

            _.$dots
                .find('li')
                .removeClass('slick-active')
                .attr('aria-hidden', 'true');

            _.$dots
                .find('li')
                .eq(Math.floor(_.currentSlide / _.options.slidesToScroll))
                .addClass('slick-active')
                .attr('aria-hidden', 'false');

        }

    };

    Slick.prototype.visibility = function() {

        var _ = this;

        if ( _.options.autoplay ) {

            if ( document[_.hidden] ) {

                _.interrupted = true;

            } else {

                _.interrupted = false;

            }

        }

    };

    $.fn.slick = function() {
        var _ = this,
            opt = arguments[0],
            args = Array.prototype.slice.call(arguments, 1),
            l = _.length,
            i,
            ret;
        for (i = 0; i < l; i++) {
            if (typeof opt == 'object' || typeof opt == 'undefined')
                _[i].slick = new Slick(_[i], opt);
            else
                ret = _[i].slick[opt].apply(_[i].slick, args);
            if (typeof ret != 'undefined') return ret;
        }
        return _;
    };

}));
/* $(document).ready(function(){
	customProductForm(productId, productCatId);
	var productOptions=[] ;
}); */
(function() {
	var runningAjaxReq = false;

	checkRunningAjax = function(){
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
	};

	var dv = '#listing';
	var prodCatId = 0;
	var blockCount = 0;

	customProductForm = function( productId, prodcat_id ){
		$(dv).html( fcom.getLoader() );
		if( (typeof productId == 'undefined' || productId == 0) && (typeof prodcat_id == 'undefined' || prodcat_id == 0) ){
			customCatalogProductCategoryForm( );
			return;
		}

		fcom.ajax(fcom.makeUrl('Seller', 'customProductGeneralForm', [ productId, prodcat_id ]), '', function(t) {
			$(dv).html(t);
		});
	};

	customCatalogProductCategoryForm = function(){
		fcom.ajax(fcom.makeUrl('Seller', 'customCatalogProductCategoryForm'), '', function(t) {
			$(dv).html(t);
			customCategoryListing(prodCatId,blockCount);
		});
	};

	customCategoryListing = function(prodCatId,section){
		$(section).parent().find('li').removeClass('is-active');
		$(section).addClass('is-active');
		var bcount = $(section).closest('.categoryblock-js').attr('rel');
		if(typeof bcount != 'undefined'){
			blockCount = bcount;
			blockCount = parseInt(blockCount)+1;
		}
		$(section).closest('.categoryblock-js').nextAll('div').remove();
		var data = "prodCatId="+prodCatId+"&blockCount="+blockCount;
		fcom.ajax(fcom.makeUrl('Seller', 'customCategoryListing'), data, function(t) {
			var ans = $.parseJSON(t);
			$.mbsmessage.close();
			if(ans.structure != ''){
				$('.slick-track').append(ans.structure);
				$('#categories-js .slick-prev').remove();
				$('#categories-js .slick-next').remove();
				$('.select-categories-slider-js').slick('reinit');
				if(blockCount > 2){
					$('.select-categories-slider-js').slick("slickNext");
				}
				if( $('.box-categories ul').length == 1){
					$('.slick-next').css('pointer-events', 'none');
					$('.slick-next').addClass('slick-disabled');
				}
			}
			prodCatId = ans.prodcat_id;
		});
	};

	customCatalogProductForm = function( id, prodcat_id ){
		$(dv).html( fcom.getLoader() );
		if( typeof id == 'undefined' || id == 0 && (typeof prodcat_id == 'undefined' || prodcat_id == 0)){
			customCatalogProductCategoryForm( );
			return;
		}

		fcom.ajax(fcom.makeUrl('Seller', 'customProductGeneralForm', [ id , prodcat_id ]), '', function(t) {
			$(dv).html(t);
		});
	};

	searchCategory = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.ajax(fcom.makeUrl('Seller', 'searchCategory'), (data), function(t) {
            //console.log('called');
			$('#categories-js').hide();
			$('#categorySearchListing').html(t);
		});
	};

	categorySearchByCode = function(prodCatCode){
		frm = document.frmCustomCatalogProductCategoryForm;
		var data = fcom.frmData(frm);
		fcom.ajax(fcom.makeUrl('Seller', 'searchCategory',[prodCatCode]), (data), function(t) {
			$('#categories-js').hide();
			$('#categorySearchListing').html(t);
		});
	};

	clearCategorySearch = function(){
		window.location.reload();
	};


	/* customProductForm = function( productId ){
		fcom.resetEditorInstance();
		fcom.ajax(fcom.makeUrl('Seller', 'customProductGeneralForm', [ productId ]), '', function(t) {
			$(dv).html(t);
		});
	}; */

	setupCustomProduct = function(frm){
		if (!$(frm).validate()) return;

		addingNew = ($(frm.product_id).val() == 0);
		$(frm.product_options).val(productOptions);
		var data = fcom.frmData(frm);

		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupCustomProduct'), (data), function(t) {

			$.mbsmessage.close();

			if (addingNew) {
				customProductLangForm(t.product_id, t.lang_id);
				/* localStorage.setItem("productId", t.product_id); */
				return ;
			}
			productId =  t.product_id;
			addShippingTab(t.product_id,t.product_type);
		});
	};

	customProductLangForm = function(productId, lang_id){
		fcom.resetEditorInstance();
			fcom.ajax(fcom.makeUrl('Seller', 'customProductLangForm', [productId, lang_id]), '', function(t) {
				$(dv).html(t);
				fcom.setEditorLayout(lang_id);

			});
	};

	productOptionsForm = function( id ){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Seller', 'customProductOptionsForm', [id]), '', function(t) {
				fcom.updateFaceboxContent(t,'faceboxWidth');
				reloadProductOptions(id);
			});
		});
	};

	optionForm = function(optionId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Seller', 'optionForm', [optionId]), '', function(t) {
				try{
					res = jQuery.parseJSON(t);
					$.facebox(res.msg,'faceboxWidth');
				}catch (e){
					$.facebox(t,'faceboxWidth');
					addOptionForm(optionId);
					optionValueListing(optionId);
				}
			});
		});
		fcom.resetFaceboxHeight();
	};

	addOptionForm = function(optionId){
		var dv = $('#loadForm');
		fcom.ajax(fcom.makeUrl('Seller', 'addOptionForm', [optionId]), '', function(t) {
			dv.html(t);
		});
	};

	optionValueListing = function(optionId){
		if(optionId == 0 ) { $('#showHideContainer').addClass('hide'); return ;}
		var dv =$('#optionValueListing');
		dv.html('Loading....');
		var data = 'option_id='+optionId;
		fcom.ajax(fcom.makeUrl('OptionValues','search'),data,function(res){
			dv.html(res);
		});
		fcom.resetFaceboxHeight();
	};

	optionValueForm = function (optionId,id){
		var dv = $('#loadForm');
		fcom.ajax(fcom.makeUrl('OptionValues', 'form', [optionId,id]), '', function(t) {
			dv.html(t);
			jscolor.installByClassName('jscolor');
		});
	};

	setUpOptionValues = function(frm) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('OptionValues', 'setup'), data, function(t) {
			$.mbsmessage.close();
			if (t.optionId > 0 ) {
				optionValueForm(t.optionId,0);
				optionValueListing(t.optionId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	deleteOptionValue = function(optionId,id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id+'&option_id='+optionId;
		fcom.updateWithAjax(fcom.makeUrl('OptionValues','deleteRecord'),data,function(res){
			$.mbsmessage.close();
			optionValueForm(optionId,0);
			optionValueListing(optionId);
		});
	}

	optionValueSearchPage = function(page){
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSearchOptionValuePaging;
		$(frm.page).val(page);
		searchOptionValueListing(frm);
	};

	searchOptionValueListing = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$("#optionValueListing").html('Loading....');
		fcom.ajax(fcom.makeUrl('OptionValues','search'),data,function(res){
			$("#optionValueListing").html(res);
		});
	};

	showHideValues = function(obj){

		var type =obj.value;
		var data ='optionType='+type;
		fcom.ajax(fcom.makeUrl('Options','canSetValue'),data,function(t){
			var res = $.parseJSON(t);
			if(res.hideBox == true){
				$('#showHideContainer').addClass('hide'); return ;
			}
			$('#showHideContainer').removeClass('hide');
		});
	};

	submitOptionForm=function(frm,fn){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupOptions'), data, function(t) {
			reloadList();
			$.mbsmessage.close();
			if(t.optionId > 0){
				optionForm(t.optionId); return;
			}
			$(document).trigger('close.facebox');
		});
	};

	searchOptions = function(form){
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/
		$("#optionListing").html('Loading....');

		fcom.ajax(fcom.makeUrl('seller','searchOptions'),data,function(res){
			$("#optionListing").html(res);
		});
	};

	deleteOptionRecord=function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.ajax(fcom.makeUrl('seller','deleteSellerOption'),data,function(t){
			var ans= jQuery.parseJSON(t);
			if(ans.status!=1){
				$.mbsmessage(ans.msg, true, 'alert--danger');
			}
			$.mbsmessage(ans.msg, true, 'alert--success');
			reloadList();

		});
	};

	reloadList = function() {
		var frm = document.frmOptionsSearchPaging;
		searchOptions(frm);
	};

 /* Product shipping  */
	addShippingTab = function(id){
		var ShipDiv = "#tab_shipping";
		var e = document.getElementById("product_type");
		var type = e.options[e.selectedIndex].value;

		if(type == prodTypeDigital){
			$(ShipDiv).html('');
			$('.not-digital-js').hide();
			return;
		}else{
			$('.not-digital-js').show();
		}
		fcom.ajax(fcom.makeUrl('seller','getShippingTab'),'product_id='+id,function(t){
			try{
					res= jQuery.parseJSON(t);
					$.facebox(res.msg,'faceboxWidth');
				}catch (e){

					$(ShipDiv).html(t);
				}

		});
	}

	optionForm = function(optionId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Seller', 'importProductShippingRate', [optionId]), '', function(t) {
				try{
					res= jQuery.parseJSON(t);
					$.facebox(res.msg,'faceboxWidth');
				}catch (e){

					$.facebox(t,'faceboxWidth');
					}
				fcom.resetFaceboxHeight();
			});
		});
	}
	
	importProductShippingRateFile = function(method, actionType) {
        var data = new FormData();
        $inputs = $('#frmImportExport input[type=text],#frmImportExport select,#frmImportExport input[type=hidden]');
        $inputs.each(function() {
            data.append(this.name, $(this).val());
        });
        if ($('#import_file')[0].files.length == 0) {
            $.mbsmessage(langLbl.selectFile, false, 'alert--danger');
        }
        $.each($('#import_file')[0].files, function(i, file) {
            $.mbsmessage(langLbl.processing, false, 'alert--process');
            $('#fileupload_div').html(fcom.getLoader());
            data.append('import_file', file);
            $.ajax({
                url: fcom.makeUrl('Seller', method, [actionType]),
                type: "POST",
                data: data,
                processData: false,
                contentType: false,
                success: function(t) {
                    try {
                        var ans = $.parseJSON(t);
                        if (ans.status == 1 || ans.status == true) {
                            $(document).trigger('close.facebox');
                            $(document).trigger('close.mbsmessage');
                            $.systemMessage(ans.msg, 'alert--success');
                            if ('importData' == method) {
                                importForm(actionType);
                            } else {
                                importMediaForm(actionType);
                            }
                        } else {
                            $('#fileupload_div').html('');
                            $(document).trigger('close.mbsmessage');
                            $.systemMessage(ans.msg, 'alert--danger');
                        }

                        if (typeof ans.CSVfileUrl !== 'undefined') {
                            location.href = ans.CSVfileUrl;
                        }
                    } catch (exc) {
                        $(document).trigger('close.mbsmessage');
                        $.systemMessage(exc.message, 'alert--danger');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert("Error Occured.");
                }
            });
        });
    };


	shippingautocomplete = function(shipping_row) {
		$('input[name="product_shipping[' + shipping_row + '][city_name]"]').focusout(function() {
			    setTimeout(function(){ $('.suggestions').hide(); }, 500);
		});

		$('input[name="product_shipping[' + shipping_row + '][company_name]"]').focusout(function() {
			    setTimeout(function(){ $('.suggestions').hide(); }, 500);
		});

		$('input[name="product_shipping[' + shipping_row + '][processing_time]"]').focusout(function() {
			    setTimeout(function(){ $('.suggestions').hide(); }, 500);
		});
		$('input[name="product_shipping[' + shipping_row + '][city_name]"]').autocomplete({
			'source': function(request, response) {

				$.ajax({
					url: fcom.makeUrl('seller', 'countries_autocomplete'),
					data: {keyword: request,fIsAjax:1,includeEverywhere:true},
					dataType: 'json',
					type: 'post',
					success: function(json) {

						response($.map(json, function(item) {

							return {
								label: item['name'] ,
								value: item['id']
								};
						}));
					},
				});
			},
			'select': function(item) {
				$('input[name="product_shipping[' + shipping_row + '][city_name]"]').val(item.label);
				$('input[name="product_shipping[' + shipping_row + '][city_id]"]').val(item.value);
			}
		});


		$('input[name="product_shipping[' + shipping_row + '][company_name]"]').autocomplete({
				'source': function(request, response) {

				$.ajax({
					url: fcom.makeUrl('seller', 'shippingCompanyAutocomplete'),
					data: {keyword: request,fIsAjax:1},
					dataType: 'json',
					type: 'post',
					success: function(json) {

						response($.map(json, function(item) {

							return {
								label: item['name'] ,
								value: item['id']
								};
						}));
					},
				});
			},
			'select': function(item) {

				$('input[name="product_shipping[' + shipping_row + '][company_name]"]').val(item.label);
					$('input[name="product_shipping[' + shipping_row + '][company_id]"]').val(item.value);


			}

		});

		$('input[name="product_shipping[' + shipping_row + '][processing_time]"]').autocomplete({
				'source': function(request, response) {

				$.ajax({
					url: fcom.makeUrl('seller', 'shippingMethodDurationAutocomplete'),
					data: {keyword: request,fIsAjax:1},
					dataType: 'json',
					type: 'post',
					success: function(json) {

						response($.map(json, function(item) {

							return {
								label: item['name']+'['+ item['duraion']+']' ,
								value: item['id']
								};
						}));
					},
				});
			},
			'select': function(item) {

				$('input[name="product_shipping[' + shipping_row + '][processing_time]"]').val(item.label);
					$('input[name="product_shipping[' + shipping_row + '][processing_time_id]"]').val(item.value);


			}

		});
	}

 /*  End of  Product shipping  */
/* Custom Product Options */
	sellerCustomProductOptions = function(id){
		var dv = '#listing';
			fcom.ajax(fcom.makeUrl('Seller', 'customProductOptions', [id]), '', function(t) {
				$(dv).html(t);
				reloadProductOptions(id);

			});

	}

	updateProductOption = function (product_id, option_id){
		fcom.ajax(fcom.makeUrl('Seller', 'updateProductOption'), 'product_id='+product_id+'&option_id='+option_id, function(t) {
			$.mbsmessage.close();
			reloadProductOptions(product_id);
		});
	}

	removeProductOption = function( product_id,option_id){
		fcom.ajax(fcom.makeUrl('Seller', 'checkOptionLinkedToInventory'), 'product_id='+product_id+'&option_id='+option_id, function(t) {
			ans = jQuery.parseJSON(t);
			if( ans.status != true ){
				var agree = alert(ans.msg);
                return false;
			}
			fcom.ajax(fcom.makeUrl('Seller', 'removeProductOption'), 'product_id='+product_id+'&option_id='+option_id, function(t) {
                var ans = $.parseJSON(t);
                if( ans.status == 0 ){
                    return;
                }
                $.mbsmessage(ans.msg, true, 'alert--success');
                reloadProductOptions(product_id);
			});
		});
	};

	reloadProductOptions = function( productId){

		$("#product_options_list").html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('seller', 'ProductOptions', [productId]),'', function(t) {
			$("#product_options_list").html(t);
		});
	}

	/* Custom Product Options */

	/* Custom product Specifications */

	sellerCustomProductSpecifications = function(id){
		fcom.ajax(fcom.makeUrl('Seller', 'customProductSpecifications', [id]), '', function(t) {
			var dv = '#listing';
			$(dv).html(t);
			reloadProductSpecifications(id);



		});
	}
	reloadProductSpecifications = function( productId){

		$("#product_specifications_list").html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('seller', 'ProductSpecifications', [productId]),'', function(t) {
			try{
					res= jQuery.parseJSON(t);

					$("#product_specifications_list").html(res.msg);
				}catch (e){

					$("#product_specifications_list").html(t);
				}

		});
	}
	addProdSpec = function(productId,prodSpecId){
		fcom.ajax(fcom.makeUrl('seller', 'prodSpecForm', [productId]),'prodSpecId='+prodSpecId, function(t) {
			$.facebox(t,'faceboxWidth');

		});
	}
	deleteProdSpec = function(productId,prodSpecId){
		fcom.updateWithAjax(fcom.makeUrl('seller', 'deleteProdSpec', [productId]),'prodSpecId='+prodSpecId, function(t) {
			sellerCustomProductSpecifications(productId);
			reloadProductSpecifications(productId);

		});
	}

	submitSpecificationForm = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupProductSpecifications'), data, function(t) {
			$.mbsmessage.close();
			sellerCustomProductSpecifications(t.productId);
			reloadProductSpecifications(t.productId);
			if(t.productId > 0){
				if(t.prodSpecId < 1)
				{
					addProdSpec(t.productId);
				}else{
					$(document).trigger('close.facebox');
				}
				(t.productId); return;
			}

		});
		return false;
	}
	customProductLinks = function( product_id ){
			fcom.resetEditorInstance();
		fcom.ajax(fcom.makeUrl('Seller', 'customProductLinks', [product_id]), '', function(t) {
			$(dv).html(t);
			reloadProductLinks(product_id);
			});
	}

	updateProductLink = function (product_id, option_id){
	//reloadProductLinks(product_id);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'updateProductLink'), 'product_id='+product_id+'&option_id='+option_id, function(t) {
			reloadProductLinks(product_id);
		});
	}

	removeProductCategory = function(product_id, option_id){
		var agree = confirm(langLbl.confirmDeleteOption);
		if(!agree){ return false; }
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'removeProductCategory'), 'product_id='+product_id+'&option_id='+option_id, function(t) {
			reloadProductLinks(product_id);
		});
	};

	reloadProductLinks = function( product_id ){
		$("#product_links_list").html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Seller', 'productLinks', [product_id]), '', function(t) {
			$("#product_links_list").html(t);
		});
	}

	setupProductLinks = function(frm){
		$('input[name="product_category"]').val($('input[name="list_category"]').val());
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupProductLinks'), data, function(t) {
			$(document).trigger('close.facebox');
		});
	}

	addTagForm = function(id) {
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('seller', 'addTagsForm', [id]), '', function(t) {
				$.facebox(t,'faceboxWidth medium-fb-width');
			});
		});
	};

	setupTag = function(frm) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('seller', 'setupTag'), data, function(t) {
			$.mbsmessage.close();
			reloadList();
			if (t.langId>0) {
				addTagLangForm(t.tagId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	addTagLangForm = function(tagId, langId) {
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('seller', 'tagsLangForm', [tagId, langId]), '', function(t) {
				$.facebox(t);
			});
		});
	};

	setupTagLang = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('seller', 'tagLangSetup'), data, function(t) {
			$.mbsmessage.close();
			reloadList();
			if (t.langId>0) {
				addTagLangForm(t.tagId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};
	addBrandReqForm = function(id) {
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('seller', 'addBrandReqForm', [id]), '', function(t) {
				$.facebox(t,'faceboxWidth medium-fb-width');
			});
		});
	};

	setupBrandReq = function(frm) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('seller', 'setupBrandReq'), data, function(t) {
			$.mbsmessage.close();

			if (t.langId>0) {
				addBrandReqLangForm(t.brandReqId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	addBrandReqLangForm = function(brandReqId, langId) {
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('seller', 'brandReqLangForm', [brandReqId, langId]), '', function(t) {
				$.facebox(t);
			});
		});
	};

	setupBrandReqLang = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('seller', 'brandReqLangSetup'), data, function(t) {

			if (t.langId>0) {
				addBrandReqLangForm(t.brandReqId, t.langId);
				return ;
			}
			if (t.openMediaForm)
			{
				brandMediaForm(t.brandReqId);
				return;
			}
			$(document).trigger('close.facebox');
		});
	};
	checkUniqueBrandName = function(obj,$langId,$brandId){

		data ="brandName="+$(obj).val() + "&langId= "+$langId+ "&brandId= "+$brandId;
		fcom.ajax(fcom.makeUrl('Brands', 'checkUniqueBrandName'), data, function(t) {
			$.mbsmessage.close();
			$res = $.parseJSON(t);

				if($res.status==0){
					$(obj).val('');

					$alertType = 'alert--danger';

					$.mbsmessage($res.msg,true, $alertType);
				}

		});
	}

	brandMediaForm = function(brandId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('seller', 'brandMediaForm', [brandId]), '', function(t) {
				$.facebox(t);
			});
		});
	};


	removeBrandLogo = function( brandId, langId ){
		if(!confirm(langLbl.confirmDelete)){return;}
		fcom.updateWithAjax(fcom.makeUrl('seller', 'removeBrandLogo',[brandId, langId]), '', function(t) {
			brandMediaForm( brandId );
			reloadList();
		});
	}

})();

$(document).on('click','.uploadFile-Js',function(){
	var node = this;
	$('#form-upload').remove();
	/* var brandId = document.frmProdBrandLang.brand_id.value;
	var langId = document.frmProdBrandLang.lang_id.value; */

	var brandId = $(node).attr( 'data-brand_id' );
	var langId = document.frmBrandMedia.brand_lang_id.value;

	var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
	frm = frm.concat('<input type="file" name="file" />');
	frm = frm.concat('<input type="hidden" name="brand_id" value="' + brandId + '"/>');
	frm = frm.concat('<input type="hidden" name="lang_id" value="' + langId + '"/>');
	frm = frm.concat('</form>');
	$( 'body' ).prepend( frm );
	$('#form-upload input[name=\'file\']').trigger('click');
	if ( typeof timer != 'undefined' ) {
		clearInterval(timer);
	}
	timer = setInterval(function() {
		if ($('#form-upload input[name=\'file\']').val() != '') {
			clearInterval(timer);
			$val = $(node).val();
			$.ajax({
				url: fcom.makeUrl('Seller', 'uploadLogo'),
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
						//$.mbsmessage(ans.msg);
						$('.text-danger').remove();
						$('#input-field').html(ans.msg);
						if( ans.status == true ){
							$('#input-field').removeClass('text-danger');
							$('#input-field').addClass('text-success');
							//brandLangForm( brandId, langId );
							brandMediaForm(ans.brandId);
						}else{
							$('#input-field').removeClass('text-success');
							$('#input-field').addClass('text-danger');
						}
						reloadList();
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
		}
	}, 500);
});
