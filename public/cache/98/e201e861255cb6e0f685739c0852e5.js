/*!
 * Masonry PACKAGED v4.0.0
 * Cascading grid layout library
 * http://masonry.desandro.com
 * MIT License
 * by David DeSandro
 */

/**
 * Bridget makes jQuery widgets
 * v2.0.0
 * MIT license
 */

/* jshint browser: true, strict: true, undef: true, unused: true */

( function( window, factory ) {
  'use strict';
  /* globals define: false, module: false, require: false */

  if ( typeof define == 'function' && define.amd ) {
    // AMD
    define( 'jquery-bridget/jquery-bridget',[ 'jquery' ], function( jQuery ) {
      factory( window, jQuery );
    });
  } else if ( typeof module == 'object' && module.exports ) {
    // CommonJS
    module.exports = factory(
      window,
      require('jquery')
    );
  } else {
    // browser global
    window.jQueryBridget = factory(
      window,
      window.jQuery
    );
  }

}( window, function factory( window, jQuery ) {
'use strict';

// ----- utils ----- //

var arraySlice = Array.prototype.slice;

// helper function for logging errors
// $.error breaks jQuery chaining
var console = window.console;
var logError = typeof console == 'undefined' ? function() {} :
  function( message ) {
    console.error( message );
  };

// ----- jQueryBridget ----- //

function jQueryBridget( namespace, PluginClass, $ ) {
  $ = $ || jQuery || window.jQuery;
  if ( !$ ) {
    return;
  }

  // add option method -> $().plugin('option', {...})
  if ( !PluginClass.prototype.option ) {
    // option setter
    PluginClass.prototype.option = function( opts ) {
      // bail out if not an object
      if ( !$.isPlainObject( opts ) ){
        return;
      }
      this.options = $.extend( true, this.options, opts );
    };
  }

  // make jQuery plugin
  $.fn[ namespace ] = function( arg0 /*, arg1 */ ) {
    if ( typeof arg0 == 'string' ) {
      // method call $().plugin( 'methodName', { options } )
      // shift arguments by 1
      var args = arraySlice.call( arguments, 1 );
      return methodCall( this, arg0, args );
    }
    // just $().plugin({ options })
    plainCall( this, arg0 );
    return this;
  };

  // $().plugin('methodName')
  function methodCall( $elems, methodName, args ) {
    var returnValue;
    var pluginMethodStr = '$().' + namespace + '("' + methodName + '")';

    $elems.each( function( i, elem ) {
      // get instance
      var instance = $.data( elem, namespace );
      if ( !instance ) {
        logError( namespace + ' not initialized. Cannot call methods, i.e. ' +
          pluginMethodStr );
        return;
      }

      var method = instance[ methodName ];
      if ( !method || methodName.charAt(0) == '_' ) {
        logError( pluginMethodStr + ' is not a valid method' );
        return;
      }

      // apply method, get return value
      var value = method.apply( instance, args );
      // set return value if value is returned, use only first value
      returnValue = returnValue === undefined ? value : returnValue;
    });

    return returnValue !== undefined ? returnValue : $elems;
  }

  function plainCall( $elems, options ) {
    $elems.each( function( i, elem ) {
      var instance = $.data( elem, namespace );
      if ( instance ) {
        // set options & init
        instance.option( options );
        instance._init();
      } else {
        // initialize new instance
        instance = new PluginClass( elem, options );
        $.data( elem, namespace, instance );
      }
    });
  }

  updateJQuery( $ );

}

// ----- updateJQuery ----- //

// set $.bridget for v1 backwards compatibility
function updateJQuery( $ ) {
  if ( !$ || ( $ && $.bridget ) ) {
    return;
  }
  $.bridget = jQueryBridget;
}

updateJQuery( jQuery || window.jQuery );

// -----  ----- //

return jQueryBridget;

}));

/**
 * EvEmitter v1.0.1
 * Lil' event emitter
 * MIT License
 */

/* jshint unused: true, undef: true, strict: true */

( function( global, factory ) {
  // universal module definition
  /* jshint strict: false */ /* globals define, module */
  if ( typeof define == 'function' && define.amd ) {
    // AMD - RequireJS
    define( 'ev-emitter/ev-emitter',factory );
  } else if ( typeof module == 'object' && module.exports ) {
    // CommonJS - Browserify, Webpack
    module.exports = factory();
  } else {
    // Browser globals
    global.EvEmitter = factory();
  }

}( this, function() {



function EvEmitter() {}

var proto = EvEmitter.prototype;

proto.on = function( eventName, listener ) {
  if ( !eventName || !listener ) {
    return;
  }
  // set events hash
  var events = this._events = this._events || {};
  // set listeners array
  var listeners = events[ eventName ] = events[ eventName ] || [];
  // only add once
  if ( listeners.indexOf( listener ) == -1 ) {
    listeners.push( listener );
  }

  return this;
};

proto.once = function( eventName, listener ) {
  if ( !eventName || !listener ) {
    return;
  }
  // add event
  this.on( eventName, listener );
  // set once flag
  // set onceEvents hash
  var onceEvents = this._onceEvents = this._onceEvents || {};
  // set onceListeners array
  var onceListeners = onceEvents[ eventName ] = onceEvents[ eventName ] || [];
  // set flag
  onceListeners[ listener ] = true;

  return this;
};

proto.off = function( eventName, listener ) {
  var listeners = this._events && this._events[ eventName ];
  if ( !listeners || !listeners.length ) {
    return;
  }
  var index = listeners.indexOf( listener );
  if ( index != -1 ) {
    listeners.splice( index, 1 );
  }

  return this;
};

proto.emitEvent = function( eventName, args ) {
  var listeners = this._events && this._events[ eventName ];
  if ( !listeners || !listeners.length ) {
    return;
  }
  var i = 0;
  var listener = listeners[i];
  args = args || [];
  // once stuff
  var onceListeners = this._onceEvents && this._onceEvents[ eventName ];

  while ( listener ) {
    var isOnce = onceListeners && onceListeners[ listener ];
    if ( isOnce ) {
      // remove listener
      // remove before trigger to prevent recursion
      this.off( eventName, listener );
      // unset once flag
      delete onceListeners[ listener ];
    }
    // trigger listener
    listener.apply( this, args );
    // get next listener
    i += isOnce ? 0 : 1;
    listener = listeners[i];
  }

  return this;
};

return EvEmitter;

}));

/*!
 * getSize v2.0.2
 * measure size of elements
 * MIT license
 */

/*jshint browser: true, strict: true, undef: true, unused: true */
/*global define: false, module: false, console: false */

( function( window, factory ) {
  'use strict';

  if ( typeof define == 'function' && define.amd ) {
    // AMD
    define( 'get-size/get-size',[],function() {
      return factory();
    });
  } else if ( typeof module == 'object' && module.exports ) {
    // CommonJS
    module.exports = factory();
  } else {
    // browser global
    window.getSize = factory();
  }

})( window, function factory() {
'use strict';

// -------------------------- helpers -------------------------- //

// get a number from a string, not a percentage
function getStyleSize( value ) {
  var num = parseFloat( value );
  // not a percent like '100%', and a number
  var isValid = value.indexOf('%') == -1 && !isNaN( num );
  return isValid && num;
}

function noop() {}

var logError = typeof console == 'undefined' ? noop :
  function( message ) {
    console.error( message );
  };

// -------------------------- measurements -------------------------- //

var measurements = [
  'paddingLeft',
  'paddingRight',
  'paddingTop',
  'paddingBottom',
  'marginLeft',
  'marginRight',
  'marginTop',
  'marginBottom',
  'borderLeftWidth',
  'borderRightWidth',
  'borderTopWidth',
  'borderBottomWidth'
];

var measurementsLength = measurements.length;

function getZeroSize() {
  var size = {
    width: 0,
    height: 0,
    innerWidth: 0,
    innerHeight: 0,
    outerWidth: 0,
    outerHeight: 0
  };
  for ( var i=0; i < measurementsLength; i++ ) {
    var measurement = measurements[i];
    size[ measurement ] = 0;
  }
  return size;
}

// -------------------------- getStyle -------------------------- //

/**
 * getStyle, get style of element, check for Firefox bug
 * https://bugzilla.mozilla.org/show_bug.cgi?id=548397
 */
function getStyle( elem ) {
  var style = getComputedStyle( elem );
  if ( !style ) {
    logError( 'Style returned ' + style +
      '. Are you running this code in a hidden iframe on Firefox? ' +
      'See http://bit.ly/getsizebug1' );
  }
  return style;
}

// -------------------------- setup -------------------------- //

var isSetup = false;

var isBoxSizeOuter;

/**
 * setup
 * check isBoxSizerOuter
 * do on first getSize() rather than on page load for Firefox bug
 */
function setup() {
  // setup once
  if ( isSetup ) {
    return;
  }
  isSetup = true;

  // -------------------------- box sizing -------------------------- //

  /**
   * WebKit measures the outer-width on style.width on border-box elems
   * IE & Firefox<29 measures the inner-width
   */
  var div = document.createElement('div');
  div.style.width = '200px';
  div.style.padding = '1px 2px 3px 4px';
  div.style.borderStyle = 'solid';
  div.style.borderWidth = '1px 2px 3px 4px';
  div.style.boxSizing = 'border-box';

  var body = document.body || document.documentElement;
  body.appendChild( div );
  var style = getStyle( div );

  getSize.isBoxSizeOuter = isBoxSizeOuter = getStyleSize( style.width ) == 200;
  body.removeChild( div );

}

// -------------------------- getSize -------------------------- //

function getSize( elem ) {
  setup();

  // use querySeletor if elem is string
  if ( typeof elem == 'string' ) {
    elem = document.querySelector( elem );
  }

  // do not proceed on non-objects
  if ( !elem || typeof elem != 'object' || !elem.nodeType ) {
    return;
  }

  var style = getStyle( elem );

  // if hidden, everything is 0
  if ( style.display == 'none' ) {
    return getZeroSize();
  }

  var size = {};
  size.width = elem.offsetWidth;
  size.height = elem.offsetHeight;

  var isBorderBox = size.isBorderBox = style.boxSizing == 'border-box';

  // get all measurements
  for ( var i=0; i < measurementsLength; i++ ) {
    var measurement = measurements[i];
    var value = style[ measurement ];
    var num = parseFloat( value );
    // any 'auto', 'medium' value will be 0
    size[ measurement ] = !isNaN( num ) ? num : 0;
  }

  var paddingWidth = size.paddingLeft + size.paddingRight;
  var paddingHeight = size.paddingTop + size.paddingBottom;
  var marginWidth = size.marginLeft + size.marginRight;
  var marginHeight = size.marginTop + size.marginBottom;
  var borderWidth = size.borderLeftWidth + size.borderRightWidth;
  var borderHeight = size.borderTopWidth + size.borderBottomWidth;

  var isBorderBoxSizeOuter = isBorderBox && isBoxSizeOuter;

  // overwrite width and height if we can get it from style
  var styleWidth = getStyleSize( style.width );
  if ( styleWidth !== false ) {
    size.width = styleWidth +
      // add padding and border unless it's already including it
      ( isBorderBoxSizeOuter ? 0 : paddingWidth + borderWidth );
  }

  var styleHeight = getStyleSize( style.height );
  if ( styleHeight !== false ) {
    size.height = styleHeight +
      // add padding and border unless it's already including it
      ( isBorderBoxSizeOuter ? 0 : paddingHeight + borderHeight );
  }

  size.innerWidth = size.width - ( paddingWidth + borderWidth );
  size.innerHeight = size.height - ( paddingHeight + borderHeight );

  size.outerWidth = size.width + marginWidth;
  size.outerHeight = size.height + marginHeight;

  return size;
}

return getSize;

});

/**
 * matchesSelector v2.0.1
 * matchesSelector( element, '.selector' )
 * MIT license
 */

/*jshint browser: true, strict: true, undef: true, unused: true */

( function( window, factory ) {
  /*global define: false, module: false */
  'use strict';
  // universal module definition
  if ( typeof define == 'function' && define.amd ) {
    // AMD
    define( 'matches-selector/matches-selector',factory );
  } else if ( typeof module == 'object' && module.exports ) {
    // CommonJS
    module.exports = factory();
  } else {
    // browser global
    window.matchesSelector = factory();
  }

}( window, function factory() {
  'use strict';

  var matchesMethod = ( function() {
    var ElemProto = Element.prototype;
    // check for the standard method name first
    if ( ElemProto.matches ) {
      return 'matches';
    }
    // check un-prefixed
    if ( ElemProto.matchesSelector ) {
      return 'matchesSelector';
    }
    // check vendor prefixes
    var prefixes = [ 'webkit', 'moz', 'ms', 'o' ];

    for ( var i=0; i < prefixes.length; i++ ) {
      var prefix = prefixes[i];
      var method = prefix + 'MatchesSelector';
      if ( ElemProto[ method ] ) {
        return method;
      }
    }
  })();

  return function matchesSelector( elem, selector ) {
    return elem[ matchesMethod ]( selector );
  };

}));

/**
 * Fizzy UI utils v2.0.0
 * MIT license
 */

/*jshint browser: true, undef: true, unused: true, strict: true */

( function( window, factory ) {
  /*global define: false, module: false, require: false */
  'use strict';
  // universal module definition

  if ( typeof define == 'function' && define.amd ) {
    // AMD
    define( 'fizzy-ui-utils/utils',[
      'matches-selector/matches-selector'
    ], function( matchesSelector ) {
      return factory( window, matchesSelector );
    });
  } else if ( typeof module == 'object' && module.exports ) {
    // CommonJS
    module.exports = factory(
      window,
      require('desandro-matches-selector')
    );
  } else {
    // browser global
    window.fizzyUIUtils = factory(
      window,
      window.matchesSelector
    );
  }

}( window, function factory( window, matchesSelector ) {



var utils = {};

// ----- extend ----- //

// extends objects
utils.extend = function( a, b ) {
  for ( var prop in b ) {
    a[ prop ] = b[ prop ];
  }
  return a;
};

// ----- modulo ----- //

utils.modulo = function( num, div ) {
  return ( ( num % div ) + div ) % div;
};

// ----- makeArray ----- //

// turn element or nodeList into an array
utils.makeArray = function( obj ) {
  var ary = [];
  if ( Array.isArray( obj ) ) {
    // use object if already an array
    ary = obj;
  } else if ( obj && typeof obj.length == 'number' ) {
    // convert nodeList to array
    for ( var i=0; i < obj.length; i++ ) {
      ary.push( obj[i] );
    }
  } else {
    // array of single index
    ary.push( obj );
  }
  return ary;
};

// ----- removeFrom ----- //

utils.removeFrom = function( ary, obj ) {
  var index = ary.indexOf( obj );
  if ( index != -1 ) {
    ary.splice( index, 1 );
  }
};

// ----- getParent ----- //

utils.getParent = function( elem, selector ) {
  while ( elem != document.body ) {
    elem = elem.parentNode;
    if ( matchesSelector( elem, selector ) ) {
      return elem;
    }
  }
};

// ----- getQueryElement ----- //

// use element as selector string
utils.getQueryElement = function( elem ) {
  if ( typeof elem == 'string' ) {
    return document.querySelector( elem );
  }
  return elem;
};

// ----- handleEvent ----- //

// enable .ontype to trigger from .addEventListener( elem, 'type' )
utils.handleEvent = function( event ) {
  var method = 'on' + event.type;
  if ( this[ method ] ) {
    this[ method ]( event );
  }
};

// ----- filterFindElements ----- //

utils.filterFindElements = function( elems, selector ) {
  // make array of elems
  elems = utils.makeArray( elems );
  var ffElems = [];

  elems.forEach( function( elem ) {
    // check that elem is an actual element
    if ( !( elem instanceof HTMLElement ) ) {
      return;
    }
    // add elem if no selector
    if ( !selector ) {
      ffElems.push( elem );
      return;
    }
    // filter & find items if we have a selector
    // filter
    if ( matchesSelector( elem, selector ) ) {
      ffElems.push( elem );
    }
    // find children
    var childElems = elem.querySelectorAll( selector );
    // concat childElems to filterFound array
    for ( var i=0; i < childElems.length; i++ ) {
      ffElems.push( childElems[i] );
    }
  });

  return ffElems;
};

// ----- debounceMethod ----- //

utils.debounceMethod = function( _class, methodName, threshold ) {
  // original method
  var method = _class.prototype[ methodName ];
  var timeoutName = methodName + 'Timeout';

  _class.prototype[ methodName ] = function() {
    var timeout = this[ timeoutName ];
    if ( timeout ) {
      clearTimeout( timeout );
    }
    var args = arguments;

    var _this = this;
    this[ timeoutName ] = setTimeout( function() {
      method.apply( _this, args );
      delete _this[ timeoutName ];
    }, threshold || 100 );
  };
};

// ----- docReady ----- //

utils.docReady = function( callback ) {
  if ( document.readyState == 'complete' ) {
    callback();
  } else {
    document.addEventListener( 'DOMContentLoaded', callback );
  }
};

// ----- htmlInit ----- //

// http://jamesroberts.name/blog/2010/02/22/string-functions-for-javascript-trim-to-camel-case-to-dashed-and-to-underscore/
utils.toDashed = function( str ) {
  return str.replace( /(.)([A-Z])/g, function( match, $1, $2 ) {
    return $1 + '-' + $2;
  }).toLowerCase();
};

var console = window.console;
/**
 * allow user to initialize classes via [data-namespace] or .js-namespace class
 * htmlInit( Widget, 'widgetName' )
 * options are parsed from data-namespace-options
 */
utils.htmlInit = function( WidgetClass, namespace ) {
  utils.docReady( function() {
    var dashedNamespace = utils.toDashed( namespace );
    var dataAttr = 'data-' + dashedNamespace;
    var dataAttrElems = document.querySelectorAll( '[' + dataAttr + ']' );
    var jsDashElems = document.querySelectorAll( '.js-' + dashedNamespace );
    var elems = utils.makeArray( dataAttrElems )
      .concat( utils.makeArray( jsDashElems ) );
    var dataOptionsAttr = dataAttr + '-options';
    var jQuery = window.jQuery;

    elems.forEach( function( elem ) {
      var attr = elem.getAttribute( dataAttr ) ||
        elem.getAttribute( dataOptionsAttr );
      var options;
      try {
        options = attr && JSON.parse( attr );
      } catch ( error ) {
        // log error, do not initialize
        if ( console ) {
          console.error( 'Error parsing ' + dataAttr + ' on ' + elem.className +
          ': ' + error );
        }
        return;
      }
      // initialize
      var instance = new WidgetClass( elem, options );
      // make available via $().data('layoutname')
      if ( jQuery ) {
        jQuery.data( elem, namespace, instance );
      }
    });

  });
};

// -----  ----- //

return utils;

}));

/**
 * Outlayer Item
 */

( function( window, factory ) {
  // universal module definition
  /* jshint strict: false */ /* globals define, module, require */
  if ( typeof define == 'function' && define.amd ) {
    // AMD - RequireJS
    define( 'outlayer/item',[
        'ev-emitter/ev-emitter',
        'get-size/get-size'
      ],
      function( EvEmitter, getSize ) {
        return factory( window, EvEmitter, getSize );
      }
    );
  } else if ( typeof module == 'object' && module.exports ) {
    // CommonJS - Browserify, Webpack
    module.exports = factory(
      window,
      require('ev-emitter'),
      require('get-size')
    );
  } else {
    // browser global
    window.Outlayer = {};
    window.Outlayer.Item = factory(
      window,
      window.EvEmitter,
      window.getSize
    );
  }

}( window, function factory( window, EvEmitter, getSize ) {
'use strict';

// ----- helpers ----- //

function isEmptyObj( obj ) {
  for ( var prop in obj ) {
    return false;
  }
  prop = null;
  return true;
}

// -------------------------- CSS3 support -------------------------- //


var docElemStyle = document.documentElement.style;

var transitionProperty = typeof docElemStyle.transition == 'string' ?
  'transition' : 'WebkitTransition';
var transformProperty = typeof docElemStyle.transform == 'string' ?
  'transform' : 'WebkitTransform';

var transitionEndEvent = {
  WebkitTransition: 'webkitTransitionEnd',
  transition: 'transitionend'
}[ transitionProperty ];

// cache all vendor properties
var vendorProperties = [
  transformProperty,
  transitionProperty,
  transitionProperty + 'Duration',
  transitionProperty + 'Property'
];

// -------------------------- Item -------------------------- //

function Item( element, layout ) {
  if ( !element ) {
    return;
  }

  this.element = element;
  // parent layout class, i.e. Masonry, Isotope, or Packery
  this.layout = layout;
  this.position = {
    x: 0,
    y: 0
  };

  this._create();
}

// inherit EvEmitter
var proto = Item.prototype = Object.create( EvEmitter.prototype );
proto.constructor = Item;

proto._create = function() {
  // transition objects
  this._transn = {
    ingProperties: {},
    clean: {},
    onEnd: {}
  };

  this.css({
    position: 'absolute'
  });
};

// trigger specified handler for event type
proto.handleEvent = function( event ) {
  var method = 'on' + event.type;
  if ( this[ method ] ) {
    this[ method ]( event );
  }
};

proto.getSize = function() {
  this.size = getSize( this.element );
};

/**
 * apply CSS styles to element
 * @param {Object} style
 */
proto.css = function( style ) {
  var elemStyle = this.element.style;

  for ( var prop in style ) {
    // use vendor property if available
    var supportedProp = vendorProperties[ prop ] || prop;
    elemStyle[ supportedProp ] = style[ prop ];
  }
};

 // measure position, and sets it
proto.getPosition = function() {
  var style = getComputedStyle( this.element );
  var isOriginLeft = this.layout._getOption('originLeft');
  var isOriginTop = this.layout._getOption('originTop');
  var xValue = style[ isOriginLeft ? 'left' : 'right' ];
  var yValue = style[ isOriginTop ? 'top' : 'bottom' ];
  // convert percent to pixels
  var layoutSize = this.layout.size;
  var x = xValue.indexOf('%') != -1 ?
    ( parseFloat( xValue ) / 100 ) * layoutSize.width : parseInt( xValue, 10 );
  var y = yValue.indexOf('%') != -1 ?
    ( parseFloat( yValue ) / 100 ) * layoutSize.height : parseInt( yValue, 10 );

  // clean up 'auto' or other non-integer values
  x = isNaN( x ) ? 0 : x;
  y = isNaN( y ) ? 0 : y;
  // remove padding from measurement
  x -= isOriginLeft ? layoutSize.paddingLeft : layoutSize.paddingRight;
  y -= isOriginTop ? layoutSize.paddingTop : layoutSize.paddingBottom;

  this.position.x = x;
  this.position.y = y;
};

// set settled position, apply padding
proto.layoutPosition = function() {
  var layoutSize = this.layout.size;
  var style = {};
  var isOriginLeft = this.layout._getOption('originLeft');
  var isOriginTop = this.layout._getOption('originTop');

  // x
  var xPadding = isOriginLeft ? 'paddingLeft' : 'paddingRight';
  var xProperty = isOriginLeft ? 'left' : 'right';
  var xResetProperty = isOriginLeft ? 'right' : 'left';

  var x = this.position.x + layoutSize[ xPadding ];
  // set in percentage or pixels
  style[ xProperty ] = this.getXValue( x );
  // reset other property
  style[ xResetProperty ] = '';

  // y
  var yPadding = isOriginTop ? 'paddingTop' : 'paddingBottom';
  var yProperty = isOriginTop ? 'top' : 'bottom';
  var yResetProperty = isOriginTop ? 'bottom' : 'top';

  var y = this.position.y + layoutSize[ yPadding ];
  // set in percentage or pixels
  style[ yProperty ] = this.getYValue( y );
  // reset other property
  style[ yResetProperty ] = '';

  this.css( style );
  this.emitEvent( 'layout', [ this ] );
};

proto.getXValue = function( x ) {
  var isHorizontal = this.layout._getOption('horizontal');
  return this.layout.options.percentPosition && !isHorizontal ?
    ( ( x / this.layout.size.width ) * 100 ) + '%' : x + 'px';
};

proto.getYValue = function( y ) {
  var isHorizontal = this.layout._getOption('horizontal');
  return this.layout.options.percentPosition && isHorizontal ?
    ( ( y / this.layout.size.height ) * 100 ) + '%' : y + 'px';
};

proto._transitionTo = function( x, y ) {
  this.getPosition();
  // get current x & y from top/left
  var curX = this.position.x;
  var curY = this.position.y;

  var compareX = parseInt( x, 10 );
  var compareY = parseInt( y, 10 );
  var didNotMove = compareX === this.position.x && compareY === this.position.y;

  // save end position
  this.setPosition( x, y );

  // if did not move and not transitioning, just go to layout
  if ( didNotMove && !this.isTransitioning ) {
    this.layoutPosition();
    return;
  }

  var transX = x - curX;
  var transY = y - curY;
  var transitionStyle = {};
  transitionStyle.transform = this.getTranslate( transX, transY );

  this.transition({
    to: transitionStyle,
    onTransitionEnd: {
      transform: this.layoutPosition
    },
    isCleaning: true
  });
};

proto.getTranslate = function( x, y ) {
  // flip cooridinates if origin on right or bottom
  var isOriginLeft = this.layout._getOption('originLeft');
  var isOriginTop = this.layout._getOption('originTop');
  x = isOriginLeft ? x : -x;
  y = isOriginTop ? y : -y;
  return 'translate3d(' + x + 'px, ' + y + 'px, 0)';
};

// non transition + transform support
proto.goTo = function( x, y ) {
  this.setPosition( x, y );
  this.layoutPosition();
};

proto.moveTo = proto._transitionTo;

proto.setPosition = function( x, y ) {
  this.position.x = parseInt( x, 10 );
  this.position.y = parseInt( y, 10 );
};

// ----- transition ----- //

/**
 * @param {Object} style - CSS
 * @param {Function} onTransitionEnd
 */

// non transition, just trigger callback
proto._nonTransition = function( args ) {
  this.css( args.to );
  if ( args.isCleaning ) {
    this._removeStyles( args.to );
  }
  for ( var prop in args.onTransitionEnd ) {
    args.onTransitionEnd[ prop ].call( this );
  }
};

/**
 * proper transition
 * @param {Object} args - arguments
 *   @param {Object} to - style to transition to
 *   @param {Object} from - style to start transition from
 *   @param {Boolean} isCleaning - removes transition styles after transition
 *   @param {Function} onTransitionEnd - callback
 */
proto._transition = function( args ) {
  // redirect to nonTransition if no transition duration
  if ( !parseFloat( this.layout.options.transitionDuration ) ) {
    this._nonTransition( args );
    return;
  }

  var _transition = this._transn;
  // keep track of onTransitionEnd callback by css property
  for ( var prop in args.onTransitionEnd ) {
    _transition.onEnd[ prop ] = args.onTransitionEnd[ prop ];
  }
  // keep track of properties that are transitioning
  for ( prop in args.to ) {
    _transition.ingProperties[ prop ] = true;
    // keep track of properties to clean up when transition is done
    if ( args.isCleaning ) {
      _transition.clean[ prop ] = true;
    }
  }

  // set from styles
  if ( args.from ) {
    this.css( args.from );
    // force redraw. http://blog.alexmaccaw.com/css-transitions
    var h = this.element.offsetHeight;
    // hack for JSHint to hush about unused var
    h = null;
  }
  // enable transition
  this.enableTransition( args.to );
  // set styles that are transitioning
  this.css( args.to );

  this.isTransitioning = true;

};

// dash before all cap letters, including first for
// WebkitTransform => -webkit-transform
function toDashedAll( str ) {
  return str.replace( /([A-Z])/g, function( $1 ) {
    return '-' + $1.toLowerCase();
  });
}

var transitionProps = 'opacity,' +
  toDashedAll( vendorProperties.transform || 'transform' );

proto.enableTransition = function(/* style */) {
  // HACK changing transitionProperty during a transition
  // will cause transition to jump
  if ( this.isTransitioning ) {
    return;
  }

  // make `transition: foo, bar, baz` from style object
  // HACK un-comment this when enableTransition can work
  // while a transition is happening
  // var transitionValues = [];
  // for ( var prop in style ) {
  //   // dash-ify camelCased properties like WebkitTransition
  //   prop = vendorProperties[ prop ] || prop;
  //   transitionValues.push( toDashedAll( prop ) );
  // }
  // enable transition styles
  this.css({
    transitionProperty: transitionProps,
    transitionDuration: this.layout.options.transitionDuration
  });
  // listen for transition end event
  this.element.addEventListener( transitionEndEvent, this, false );
};

proto.transition = Item.prototype[ transitionProperty ? '_transition' : '_nonTransition' ];

// ----- events ----- //

proto.onwebkitTransitionEnd = function( event ) {
  this.ontransitionend( event );
};

proto.onotransitionend = function( event ) {
  this.ontransitionend( event );
};

// properties that I munge to make my life easier
var dashedVendorProperties = {
  '-webkit-transform': 'transform'
};

proto.ontransitionend = function( event ) {
  // disregard bubbled events from children
  if ( event.target !== this.element ) {
    return;
  }
  var _transition = this._transn;
  // get property name of transitioned property, convert to prefix-free
  var propertyName = dashedVendorProperties[ event.propertyName ] || event.propertyName;

  // remove property that has completed transitioning
  delete _transition.ingProperties[ propertyName ];
  // check if any properties are still transitioning
  if ( isEmptyObj( _transition.ingProperties ) ) {
    // all properties have completed transitioning
    this.disableTransition();
  }
  // clean style
  if ( propertyName in _transition.clean ) {
    // clean up style
    this.element.style[ event.propertyName ] = '';
    delete _transition.clean[ propertyName ];
  }
  // trigger onTransitionEnd callback
  if ( propertyName in _transition.onEnd ) {
    var onTransitionEnd = _transition.onEnd[ propertyName ];
    onTransitionEnd.call( this );
    delete _transition.onEnd[ propertyName ];
  }

  this.emitEvent( 'transitionEnd', [ this ] );
};

proto.disableTransition = function() {
  this.removeTransitionStyles();
  this.element.removeEventListener( transitionEndEvent, this, false );
  this.isTransitioning = false;
};

/**
 * removes style property from element
 * @param {Object} style
**/
proto._removeStyles = function( style ) {
  // clean up transition styles
  var cleanStyle = {};
  for ( var prop in style ) {
    cleanStyle[ prop ] = '';
  }
  this.css( cleanStyle );
};

var cleanTransitionStyle = {
  transitionProperty: '',
  transitionDuration: ''
};

proto.removeTransitionStyles = function() {
  // remove transition
  this.css( cleanTransitionStyle );
};

// ----- show/hide/remove ----- //

// remove element from DOM
proto.removeElem = function() {
  this.element.parentNode.removeChild( this.element );
  // remove display: none
  this.css({ display: '' });
  this.emitEvent( 'remove', [ this ] );
};

proto.remove = function() {
  // just remove element if no transition support or no transition
  if ( !transitionProperty || !parseFloat( this.layout.options.transitionDuration ) ) {
    this.removeElem();
    return;
  }

  // start transition
  this.once( 'transitionEnd', function() {
    this.removeElem();
  });
  this.hide();
};

proto.reveal = function() {
  delete this.isHidden;
  // remove display: none
  this.css({ display: '' });

  var options = this.layout.options;

  var onTransitionEnd = {};
  var transitionEndProperty = this.getHideRevealTransitionEndProperty('visibleStyle');
  onTransitionEnd[ transitionEndProperty ] = this.onRevealTransitionEnd;

  this.transition({
    from: options.hiddenStyle,
    to: options.visibleStyle,
    isCleaning: true,
    onTransitionEnd: onTransitionEnd
  });
};

proto.onRevealTransitionEnd = function() {
  // check if still visible
  // during transition, item may have been hidden
  if ( !this.isHidden ) {
    this.emitEvent('reveal');
  }
};

/**
 * get style property use for hide/reveal transition end
 * @param {String} styleProperty - hiddenStyle/visibleStyle
 * @returns {String}
 */
proto.getHideRevealTransitionEndProperty = function( styleProperty ) {
  var optionStyle = this.layout.options[ styleProperty ];
  // use opacity
  if ( optionStyle.opacity ) {
    return 'opacity';
  }
  // get first property
  for ( var prop in optionStyle ) {
    return prop;
  }
};

proto.hide = function() {
  // set flag
  this.isHidden = true;
  // remove display: none
  this.css({ display: '' });

  var options = this.layout.options;

  var onTransitionEnd = {};
  var transitionEndProperty = this.getHideRevealTransitionEndProperty('hiddenStyle');
  onTransitionEnd[ transitionEndProperty ] = this.onHideTransitionEnd;

  this.transition({
    from: options.visibleStyle,
    to: options.hiddenStyle,
    // keep hidden stuff hidden
    isCleaning: true,
    onTransitionEnd: onTransitionEnd
  });
};

proto.onHideTransitionEnd = function() {
  // check if still hidden
  // during transition, item may have been un-hidden
  if ( this.isHidden ) {
    this.css({ display: 'none' });
    this.emitEvent('hide');
  }
};

proto.destroy = function() {
  this.css({
    position: '',
    left: '',
    right: '',
    top: '',
    bottom: '',
    transition: '',
    transform: ''
  });
};

return Item;

}));

/*!
 * Outlayer v2.0.0
 * the brains and guts of a layout library
 * MIT license
 */

( function( window, factory ) {
  'use strict';
  // universal module definition
  /* jshint strict: false */ /* globals define, module, require */
  if ( typeof define == 'function' && define.amd ) {
    // AMD - RequireJS
    define( 'outlayer/outlayer',[
        'ev-emitter/ev-emitter',
        'get-size/get-size',
        'fizzy-ui-utils/utils',
        './item'
      ],
      function( EvEmitter, getSize, utils, Item ) {
        return factory( window, EvEmitter, getSize, utils, Item);
      }
    );
  } else if ( typeof module == 'object' && module.exports ) {
    // CommonJS - Browserify, Webpack
    module.exports = factory(
      window,
      require('ev-emitter'),
      require('get-size'),
      require('fizzy-ui-utils'),
      require('./item')
    );
  } else {
    // browser global
    window.Outlayer = factory(
      window,
      window.EvEmitter,
      window.getSize,
      window.fizzyUIUtils,
      window.Outlayer.Item
    );
  }

}( window, function factory( window, EvEmitter, getSize, utils, Item ) {
'use strict';

// ----- vars ----- //

var console = window.console;
var jQuery = window.jQuery;
var noop = function() {};

// -------------------------- Outlayer -------------------------- //

// globally unique identifiers
var GUID = 0;
// internal store of all Outlayer intances
var instances = {};


/**
 * @param {Element, String} element
 * @param {Object} options
 * @constructor
 */
function Outlayer( element, options ) {
  var queryElement = utils.getQueryElement( element );
  if ( !queryElement ) {
    if ( console ) {
      console.error( 'Bad element for ' + this.constructor.namespace +
        ': ' + ( queryElement || element ) );
    }
    return;
  }
  this.element = queryElement;
  // add jQuery
  if ( jQuery ) {
    this.$element = jQuery( this.element );
  }

  // options
  this.options = utils.extend( {}, this.constructor.defaults );
  this.option( options );

  // add id for Outlayer.getFromElement
  var id = ++GUID;
  this.element.outlayerGUID = id; // expando
  instances[ id ] = this; // associate via id

  // kick it off
  this._create();

  var isInitLayout = this._getOption('initLayout');
  if ( isInitLayout ) {
    this.layout();
  }
}

// settings are for internal use only
Outlayer.namespace = 'outlayer';
Outlayer.Item = Item;

// default options
Outlayer.defaults = {
  containerStyle: {
    position: 'relative'
  },
  initLayout: true,
  originLeft: true,
  originTop: true,
  resize: true,
  resizeContainer: true,
  // item options
  transitionDuration: '0.4s',
  hiddenStyle: {
    opacity: 0,
    transform: 'scale(0.001)'
  },
  visibleStyle: {
    opacity: 1,
    transform: 'scale(1)'
  }
};

var proto = Outlayer.prototype;
// inherit EvEmitter
utils.extend( proto, EvEmitter.prototype );

/**
 * set options
 * @param {Object} opts
 */
proto.option = function( opts ) {
  utils.extend( this.options, opts );
};

/**
 * get backwards compatible option value, check old name
 */
proto._getOption = function( option ) {
  var oldOption = this.constructor.compatOptions[ option ];
  return oldOption && this.options[ oldOption ] !== undefined ?
    this.options[ oldOption ] : this.options[ option ];
};

Outlayer.compatOptions = {
  // currentName: oldName
  initLayout: 'isInitLayout',
  horizontal: 'isHorizontal',
  layoutInstant: 'isLayoutInstant',
  originLeft: 'isOriginLeft',
  originTop: 'isOriginTop',
  resize: 'isResizeBound',
  resizeContainer: 'isResizingContainer'
};

proto._create = function() {
  // get items from children
  this.reloadItems();
  // elements that affect layout, but are not laid out
  this.stamps = [];
  this.stamp( this.options.stamp );
  // set container style
  utils.extend( this.element.style, this.options.containerStyle );

  // bind resize method
  var canBindResize = this._getOption('resize');
  if ( canBindResize ) {
    this.bindResize();
  }
};

// goes through all children again and gets bricks in proper order
proto.reloadItems = function() {
  // collection of item elements
  this.items = this._itemize( this.element.children );
};


/**
 * turn elements into Outlayer.Items to be used in layout
 * @param {Array or NodeList or HTMLElement} elems
 * @returns {Array} items - collection of new Outlayer Items
 */
proto._itemize = function( elems ) {

  var itemElems = this._filterFindItemElements( elems );
  var Item = this.constructor.Item;

  // create new Outlayer Items for collection
  var items = [];
  for ( var i=0; i < itemElems.length; i++ ) {
    var elem = itemElems[i];
    var item = new Item( elem, this );
    items.push( item );
  }

  return items;
};

/**
 * get item elements to be used in layout
 * @param {Array or NodeList or HTMLElement} elems
 * @returns {Array} items - item elements
 */
proto._filterFindItemElements = function( elems ) {
  return utils.filterFindElements( elems, this.options.itemSelector );
};

/**
 * getter method for getting item elements
 * @returns {Array} elems - collection of item elements
 */
proto.getItemElements = function() {
  return this.items.map( function( item ) {
    return item.element;
  });
};

// ----- init & layout ----- //

/**
 * lays out all items
 */
proto.layout = function() {
  this._resetLayout();
  this._manageStamps();

  // don't animate first layout
  var layoutInstant = this._getOption('layoutInstant');
  var isInstant = layoutInstant !== undefined ?
    layoutInstant : !this._isLayoutInited;
  this.layoutItems( this.items, isInstant );

  // flag for initalized
  this._isLayoutInited = true;
};

// _init is alias for layout
proto._init = proto.layout;

/**
 * logic before any new layout
 */
proto._resetLayout = function() {
  this.getSize();
};


proto.getSize = function() {
  this.size = getSize( this.element );
};

/**
 * get measurement from option, for columnWidth, rowHeight, gutter
 * if option is String -> get element from selector string, & get size of element
 * if option is Element -> get size of element
 * else use option as a number
 *
 * @param {String} measurement
 * @param {String} size - width or height
 * @private
 */
proto._getMeasurement = function( measurement, size ) {
  var option = this.options[ measurement ];
  var elem;
  if ( !option ) {
    // default to 0
    this[ measurement ] = 0;
  } else {
    // use option as an element
    if ( typeof option == 'string' ) {
      elem = this.element.querySelector( option );
    } else if ( option instanceof HTMLElement ) {
      elem = option;
    }
    // use size of element, if element
    this[ measurement ] = elem ? getSize( elem )[ size ] : option;
  }
};

/**
 * layout a collection of item elements
 * @api public
 */
proto.layoutItems = function( items, isInstant ) {
  items = this._getItemsForLayout( items );

  this._layoutItems( items, isInstant );

  this._postLayout();
};

/**
 * get the items to be laid out
 * you may want to skip over some items
 * @param {Array} items
 * @returns {Array} items
 */
proto._getItemsForLayout = function( items ) {
  return items.filter( function( item ) {
    return !item.isIgnored;
  });
};

/**
 * layout items
 * @param {Array} items
 * @param {Boolean} isInstant
 */
proto._layoutItems = function( items, isInstant ) {
  this._emitCompleteOnItems( 'layout', items );

  if ( !items || !items.length ) {
    // no items, emit event with empty array
    return;
  }

  var queue = [];

  items.forEach( function( item ) {
    // get x/y object from method
    var position = this._getItemLayoutPosition( item );
    // enqueue
    position.item = item;
    position.isInstant = isInstant || item.isLayoutInstant;
    queue.push( position );
  }, this );

  this._processLayoutQueue( queue );
};

/**
 * get item layout position
 * @param {Outlayer.Item} item
 * @returns {Object} x and y position
 */
proto._getItemLayoutPosition = function( /* item */ ) {
  return {
    x: 0,
    y: 0
  };
};

/**
 * iterate over array and position each item
 * Reason being - separating this logic prevents 'layout invalidation'
 * thx @paul_irish
 * @param {Array} queue
 */
proto._processLayoutQueue = function( queue ) {
  queue.forEach( function( obj ) {
    this._positionItem( obj.item, obj.x, obj.y, obj.isInstant );
  }, this );
};

/**
 * Sets position of item in DOM
 * @param {Outlayer.Item} item
 * @param {Number} x - horizontal position
 * @param {Number} y - vertical position
 * @param {Boolean} isInstant - disables transitions
 */
proto._positionItem = function( item, x, y, isInstant ) {
  if ( isInstant ) {
    // if not transition, just set CSS
    item.goTo( x, y );
  } else {
    item.moveTo( x, y );
  }
};

/**
 * Any logic you want to do after each layout,
 * i.e. size the container
 */
proto._postLayout = function() {
  this.resizeContainer();
};

proto.resizeContainer = function() {
  var isResizingContainer = this._getOption('resizeContainer');
  if ( !isResizingContainer ) {
    return;
  }
  var size = this._getContainerSize();
  if ( size ) {
    this._setContainerMeasure( size.width, true );
    this._setContainerMeasure( size.height, false );
  }
};

/**
 * Sets width or height of container if returned
 * @returns {Object} size
 *   @param {Number} width
 *   @param {Number} height
 */
proto._getContainerSize = noop;

/**
 * @param {Number} measure - size of width or height
 * @param {Boolean} isWidth
 */
proto._setContainerMeasure = function( measure, isWidth ) {
  if ( measure === undefined ) {
    return;
  }

  var elemSize = this.size;
  // add padding and border width if border box
  if ( elemSize.isBorderBox ) {
    measure += isWidth ? elemSize.paddingLeft + elemSize.paddingRight +
      elemSize.borderLeftWidth + elemSize.borderRightWidth :
      elemSize.paddingBottom + elemSize.paddingTop +
      elemSize.borderTopWidth + elemSize.borderBottomWidth;
  }

  measure = Math.max( measure, 0 );
  this.element.style[ isWidth ? 'width' : 'height' ] = measure + 'px';
};

/**
 * emit eventComplete on a collection of items events
 * @param {String} eventName
 * @param {Array} items - Outlayer.Items
 */
proto._emitCompleteOnItems = function( eventName, items ) {
  var _this = this;
  function onComplete() {
    _this.dispatchEvent( eventName + 'Complete', null, [ items ] );
  }

  var count = items.length;
  if ( !items || !count ) {
    onComplete();
    return;
  }

  var doneCount = 0;
  function tick() {
    doneCount++;
    if ( doneCount == count ) {
      onComplete();
    }
  }

  // bind callback
  items.forEach( function( item ) {
    item.once( eventName, tick );
  });
};

/**
 * emits events via EvEmitter and jQuery events
 * @param {String} type - name of event
 * @param {Event} event - original event
 * @param {Array} args - extra arguments
 */
proto.dispatchEvent = function( type, event, args ) {
  // add original event to arguments
  var emitArgs = event ? [ event ].concat( args ) : args;
  this.emitEvent( type, emitArgs );

  if ( jQuery ) {
    // set this.$element
    this.$element = this.$element || jQuery( this.element );
    if ( event ) {
      // create jQuery event
      var $event = jQuery.Event( event );
      $event.type = type;
      this.$element.trigger( $event, args );
    } else {
      // just trigger with type if no event available
      this.$element.trigger( type, args );
    }
  }
};

// -------------------------- ignore & stamps -------------------------- //


/**
 * keep item in collection, but do not lay it out
 * ignored items do not get skipped in layout
 * @param {Element} elem
 */
proto.ignore = function( elem ) {
  var item = this.getItem( elem );
  if ( item ) {
    item.isIgnored = true;
  }
};

/**
 * return item to layout collection
 * @param {Element} elem
 */
proto.unignore = function( elem ) {
  var item = this.getItem( elem );
  if ( item ) {
    delete item.isIgnored;
  }
};

/**
 * adds elements to stamps
 * @param {NodeList, Array, Element, or String} elems
 */
proto.stamp = function( elems ) {
  elems = this._find( elems );
  if ( !elems ) {
    return;
  }

  this.stamps = this.stamps.concat( elems );
  // ignore
  elems.forEach( this.ignore, this );
};

/**
 * removes elements to stamps
 * @param {NodeList, Array, or Element} elems
 */
proto.unstamp = function( elems ) {
  elems = this._find( elems );
  if ( !elems ){
    return;
  }

  elems.forEach( function( elem ) {
    // filter out removed stamp elements
    utils.removeFrom( this.stamps, elem );
    this.unignore( elem );
  }, this );
};

/**
 * finds child elements
 * @param {NodeList, Array, Element, or String} elems
 * @returns {Array} elems
 */
proto._find = function( elems ) {
  if ( !elems ) {
    return;
  }
  // if string, use argument as selector string
  if ( typeof elems == 'string' ) {
    elems = this.element.querySelectorAll( elems );
  }
  elems = utils.makeArray( elems );
  return elems;
};

proto._manageStamps = function() {
  if ( !this.stamps || !this.stamps.length ) {
    return;
  }

  this._getBoundingRect();

  this.stamps.forEach( this._manageStamp, this );
};

// update boundingLeft / Top
proto._getBoundingRect = function() {
  // get bounding rect for container element
  var boundingRect = this.element.getBoundingClientRect();
  var size = this.size;
  this._boundingRect = {
    left: boundingRect.left + size.paddingLeft + size.borderLeftWidth,
    top: boundingRect.top + size.paddingTop + size.borderTopWidth,
    right: boundingRect.right - ( size.paddingRight + size.borderRightWidth ),
    bottom: boundingRect.bottom - ( size.paddingBottom + size.borderBottomWidth )
  };
};

/**
 * @param {Element} stamp
**/
proto._manageStamp = noop;

/**
 * get x/y position of element relative to container element
 * @param {Element} elem
 * @returns {Object} offset - has left, top, right, bottom
 */
proto._getElementOffset = function( elem ) {
  var boundingRect = elem.getBoundingClientRect();
  var thisRect = this._boundingRect;
  var size = getSize( elem );
  var offset = {
    left: boundingRect.left - thisRect.left - size.marginLeft,
    top: boundingRect.top - thisRect.top - size.marginTop,
    right: thisRect.right - boundingRect.right - size.marginRight,
    bottom: thisRect.bottom - boundingRect.bottom - size.marginBottom
  };
  return offset;
};

// -------------------------- resize -------------------------- //

// enable event handlers for listeners
// i.e. resize -> onresize
proto.handleEvent = utils.handleEvent;

/**
 * Bind layout to window resizing
 */
proto.bindResize = function() {
  window.addEventListener( 'resize', this );
  this.isResizeBound = true;
};

/**
 * Unbind layout to window resizing
 */
proto.unbindResize = function() {
  window.removeEventListener( 'resize', this );
  this.isResizeBound = false;
};

proto.onresize = function() {
  this.resize();
};

utils.debounceMethod( Outlayer, 'onresize', 100 );

proto.resize = function() {
  // don't trigger if size did not change
  // or if resize was unbound. See #9
  if ( !this.isResizeBound || !this.needsResizeLayout() ) {
    return;
  }

  this.layout();
};

/**
 * check if layout is needed post layout
 * @returns Boolean
 */
proto.needsResizeLayout = function() {
  var size = getSize( this.element );
  // check that this.size and size are there
  // IE8 triggers resize on body size change, so they might not be
  var hasSizes = this.size && size;
  return hasSizes && size.innerWidth !== this.size.innerWidth;
};

// -------------------------- methods -------------------------- //

/**
 * add items to Outlayer instance
 * @param {Array or NodeList or Element} elems
 * @returns {Array} items - Outlayer.Items
**/
proto.addItems = function( elems ) {
  var items = this._itemize( elems );
  // add items to collection
  if ( items.length ) {
    this.items = this.items.concat( items );
  }
  return items;
};

/**
 * Layout newly-appended item elements
 * @param {Array or NodeList or Element} elems
 */
proto.appended = function( elems ) {
  var items = this.addItems( elems );
  if ( !items.length ) {
    return;
  }
  // layout and reveal just the new items
  this.layoutItems( items, true );
  this.reveal( items );
};

/**
 * Layout prepended elements
 * @param {Array or NodeList or Element} elems
 */
proto.prepended = function( elems ) {
  var items = this._itemize( elems );
  if ( !items.length ) {
    return;
  }
  // add items to beginning of collection
  var previousItems = this.items.slice(0);
  this.items = items.concat( previousItems );
  // start new layout
  this._resetLayout();
  this._manageStamps();
  // layout new stuff without transition
  this.layoutItems( items, true );
  this.reveal( items );
  // layout previous items
  this.layoutItems( previousItems );
};

/**
 * reveal a collection of items
 * @param {Array of Outlayer.Items} items
 */
proto.reveal = function( items ) {
  this._emitCompleteOnItems( 'reveal', items );
  if ( !items || !items.length ) {
    return;
  }
  items.forEach( function( item ) {
    item.reveal();
  });
};

/**
 * hide a collection of items
 * @param {Array of Outlayer.Items} items
 */
proto.hide = function( items ) {
  this._emitCompleteOnItems( 'hide', items );
  if ( !items || !items.length ) {
    return;
  }
  items.forEach( function( item ) {
    item.hide();
  });
};

/**
 * reveal item elements
 * @param {Array}, {Element}, {NodeList} items
 */
proto.revealItemElements = function( elems ) {
  var items = this.getItems( elems );
  this.reveal( items );
};

/**
 * hide item elements
 * @param {Array}, {Element}, {NodeList} items
 */
proto.hideItemElements = function( elems ) {
  var items = this.getItems( elems );
  this.hide( items );
};

/**
 * get Outlayer.Item, given an Element
 * @param {Element} elem
 * @param {Function} callback
 * @returns {Outlayer.Item} item
 */
proto.getItem = function( elem ) {
  // loop through items to get the one that matches
  for ( var i=0; i < this.items.length; i++ ) {
    var item = this.items[i];
    if ( item.element == elem ) {
      // return item
      return item;
    }
  }
};

/**
 * get collection of Outlayer.Items, given Elements
 * @param {Array} elems
 * @returns {Array} items - Outlayer.Items
 */
proto.getItems = function( elems ) {
  elems = utils.makeArray( elems );
  var items = [];
  elems.forEach( function( elem ) {
    var item = this.getItem( elem );
    if ( item ) {
      items.push( item );
    }
  }, this );

  return items;
};

/**
 * remove element(s) from instance and DOM
 * @param {Array or NodeList or Element} elems
 */
proto.remove = function( elems ) {
  var removeItems = this.getItems( elems );

  this._emitCompleteOnItems( 'remove', removeItems );

  // bail if no items to remove
  if ( !removeItems || !removeItems.length ) {
    return;
  }

  removeItems.forEach( function( item ) {
    item.remove();
    // remove item from collection
    utils.removeFrom( this.items, item );
  }, this );
};

// ----- destroy ----- //

// remove and disable Outlayer instance
proto.destroy = function() {
  // clean up dynamic styles
  var style = this.element.style;
  style.height = '';
  style.position = '';
  style.width = '';
  // destroy items
  this.items.forEach( function( item ) {
    item.destroy();
  });

  this.unbindResize();

  var id = this.element.outlayerGUID;
  delete instances[ id ]; // remove reference to instance by id
  delete this.element.outlayerGUID;
  // remove data for jQuery
  if ( jQuery ) {
    jQuery.removeData( this.element, this.constructor.namespace );
  }

};

// -------------------------- data -------------------------- //

/**
 * get Outlayer instance from element
 * @param {Element} elem
 * @returns {Outlayer}
 */
Outlayer.data = function( elem ) {
  elem = utils.getQueryElement( elem );
  var id = elem && elem.outlayerGUID;
  return id && instances[ id ];
};


// -------------------------- create Outlayer class -------------------------- //

/**
 * create a layout class
 * @param {String} namespace
 */
Outlayer.create = function( namespace, options ) {
  // sub-class Outlayer
  var Layout = subclass( Outlayer );
  // apply new options and compatOptions
  Layout.defaults = utils.extend( {}, Outlayer.defaults );
  utils.extend( Layout.defaults, options );
  Layout.compatOptions = utils.extend( {}, Outlayer.compatOptions  );

  Layout.namespace = namespace;

  Layout.data = Outlayer.data;

  // sub-class Item
  Layout.Item = subclass( Item );

  // -------------------------- declarative -------------------------- //

  utils.htmlInit( Layout, namespace );

  // -------------------------- jQuery bridge -------------------------- //

  // make into jQuery plugin
  if ( jQuery && jQuery.bridget ) {
    jQuery.bridget( namespace, Layout );
  }

  return Layout;
};

function subclass( Parent ) {
  function SubClass() {
    Parent.apply( this, arguments );
  }

  SubClass.prototype = Object.create( Parent.prototype );
  SubClass.prototype.constructor = SubClass;

  return SubClass;
}

// ----- fin ----- //

// back in global
Outlayer.Item = Item;

return Outlayer;

}));

/*!
 * Masonry v4.0.0
 * Cascading grid layout library
 * http://masonry.desandro.com
 * MIT License
 * by David DeSandro
 */

( function( window, factory ) {
  // universal module definition
  /* jshint strict: false */ /*globals define, module, require */
  if ( typeof define == 'function' && define.amd ) {
    // AMD
    define( [
        'outlayer/outlayer',
        'get-size/get-size'
      ],
      factory );
  } else if ( typeof module == 'object' && module.exports ) {
    // CommonJS
    module.exports = factory(
      require('outlayer'),
      require('get-size')
    );
  } else {
    // browser global
    window.Masonry = factory(
      window.Outlayer,
      window.getSize
    );
  }

}( window, function factory( Outlayer, getSize ) {



// -------------------------- masonryDefinition -------------------------- //

  // create an Outlayer layout class
  var Masonry = Outlayer.create('masonry');
  // isFitWidth -> fitWidth
  Masonry.compatOptions.fitWidth = 'isFitWidth';

  Masonry.prototype._resetLayout = function() {
    this.getSize();
    this._getMeasurement( 'columnWidth', 'outerWidth' );
    this._getMeasurement( 'gutter', 'outerWidth' );
    this.measureColumns();

    // reset column Y
    this.colYs = [];
    for ( var i=0; i < this.cols; i++ ) {
      this.colYs.push( 0 );
    }

    this.maxY = 0;
  };

  Masonry.prototype.measureColumns = function() {
    this.getContainerWidth();
    // if columnWidth is 0, default to outerWidth of first item
    if ( !this.columnWidth ) {
      var firstItem = this.items[0];
      var firstItemElem = firstItem && firstItem.element;
      // columnWidth fall back to item of first element
      this.columnWidth = firstItemElem && getSize( firstItemElem ).outerWidth ||
        // if first elem has no width, default to size of container
        this.containerWidth;
    }

    var columnWidth = this.columnWidth += this.gutter;

    // calculate columns
    var containerWidth = this.containerWidth + this.gutter;
    var cols = containerWidth / columnWidth;
    // fix rounding errors, typically with gutters
    var excess = columnWidth - containerWidth % columnWidth;
    // if overshoot is less than a pixel, round up, otherwise floor it
    var mathMethod = excess && excess < 1 ? 'round' : 'floor';
    cols = Math[ mathMethod ]( cols );
    this.cols = Math.max( cols, 1 );
  };

  Masonry.prototype.getContainerWidth = function() {
    // container is parent if fit width
    var isFitWidth = this._getOption('fitWidth');
    var container = isFitWidth ? this.element.parentNode : this.element;
    // check that this.size and size are there
    // IE8 triggers resize on body size change, so they might not be
    var size = getSize( container );
    this.containerWidth = size && size.innerWidth;
  };

  Masonry.prototype._getItemLayoutPosition = function( item ) {
    item.getSize();
    // how many columns does this brick span
    var remainder = item.size.outerWidth % this.columnWidth;
    var mathMethod = remainder && remainder < 1 ? 'round' : 'ceil';
    // round if off by 1 pixel, otherwise use ceil
    var colSpan = Math[ mathMethod ]( item.size.outerWidth / this.columnWidth );
    colSpan = Math.min( colSpan, this.cols );

    var colGroup = this._getColGroup( colSpan );
    // get the minimum Y value from the columns
    var minimumY = Math.min.apply( Math, colGroup );
    var shortColIndex = colGroup.indexOf( minimumY );

    // position the brick
    var position = {
      x: this.columnWidth * shortColIndex,
      y: minimumY
    };

    // apply setHeight to necessary columns
    var setHeight = minimumY + item.size.outerHeight;
    var setSpan = this.cols + 1 - colGroup.length;
    for ( var i = 0; i < setSpan; i++ ) {
      this.colYs[ shortColIndex + i ] = setHeight;
    }

    return position;
  };

  /**
   * @param {Number} colSpan - number of columns the element spans
   * @returns {Array} colGroup
   */
  Masonry.prototype._getColGroup = function( colSpan ) {
    if ( colSpan < 2 ) {
      // if brick spans only one column, use all the column Ys
      return this.colYs;
    }

    var colGroup = [];
    // how many different places could this brick fit horizontally
    var groupCount = this.cols + 1 - colSpan;
    // for each group potential horizontal position
    for ( var i = 0; i < groupCount; i++ ) {
      // make an array of colY values for that one group
      var groupColYs = this.colYs.slice( i, i + colSpan );
      // and get the max value of the array
      colGroup[i] = Math.max.apply( Math, groupColYs );
    }
    return colGroup;
  };

  Masonry.prototype._manageStamp = function( stamp ) {
    var stampSize = getSize( stamp );
    var offset = this._getElementOffset( stamp );
    // get the columns that this stamp affects
    var isOriginLeft = this._getOption('originLeft');
    var firstX = isOriginLeft ? offset.left : offset.right;
    var lastX = firstX + stampSize.outerWidth;
    var firstCol = Math.floor( firstX / this.columnWidth );
    firstCol = Math.max( 0, firstCol );
    var lastCol = Math.floor( lastX / this.columnWidth );
    // lastCol should not go over if multiple of columnWidth #425
    lastCol -= lastX % this.columnWidth ? 0 : 1;
    lastCol = Math.min( this.cols - 1, lastCol );
    // set colYs to bottom of the stamp

    var isOriginTop = this._getOption('originTop');
    var stampMaxY = ( isOriginTop ? offset.top : offset.bottom ) +
      stampSize.outerHeight;
    for ( var i = firstCol; i <= lastCol; i++ ) {
      this.colYs[i] = Math.max( stampMaxY, this.colYs[i] );
    }
  };

  Masonry.prototype._getContainerSize = function() {
    this.maxY = Math.max.apply( Math, this.colYs );
    var size = {
      height: this.maxY
    };

    if ( this._getOption('fitWidth') ) {
      size.width = this._getContainerFitWidth();
    }

    return size;
  };

  Masonry.prototype._getContainerFitWidth = function() {
    var unusedCols = 0;
    // count unused columns
    var i = this.cols;
    while ( --i ) {
      if ( this.colYs[i] !== 0 ) {
        break;
      }
      unusedCols++;
    }
    // fit container to columns that have been used
    return ( this.cols - unusedCols ) * this.columnWidth - this.gutter;
  };

  Masonry.prototype.needsResizeLayout = function() {
    var previousWidth = this.containerWidth;
    this.getContainerWidth();
    return previousWidth != this.containerWidth;
  };

  return Masonry;

}));

var searchArr = [];
var page = 1;
$(document).ready(function(){
	var frm = document.frmProductSearch;

	var frmSiteSearch = document.frmSiteSearch;
	$(frmSiteSearch.keyword).val($(frm.keyword).val());
	setSelectedCatValue($(frm.category).val());

	$.each( frm.elements, function(index, elem){
		if( elem.type != 'text' && elem.type != 'textarea' && elem.type != 'hidden' && elem.type != 'submit' ){
			/* i.e for selectbox */
			$(elem).change(function(){
				reloadProductListing(frm);
				//searchProducts(frm,0,0,1);
			});
		}
	});
	/* ] */

	/* form submit upon onchange of elements not inside form tag[ */


	if( typeof isBrandPage !== 'undefined' && isBrandPage !== null ){
		$("input[name=brands]").attr("disabled","disabled");
		$("input[name=brands]").parent("label").addClass("disabled");
	}

	$(document).on('change', 'input[name=brands]', function() {
		var id= $(this).parent().parent().find('label').attr('id');
		if($(this).is(":checked")){
			addFilter(id,this);
			addToSearchQueryString(id,this);
		}else{
			removeFilter(id,this);
		}
		removePaginationFromLink();
		reloadProductListing(frm);
		//searchProducts(frm,0,0,1,1);
	});

	$(document).on('change', 'input[name=category]', function() {
		var id= $(this).parent().parent().find('label').attr('id');
		if($(this).is(":checked")){
			addFilter(id,this);
			addToSearchQueryString(id,this);
		}else{
			removeFilter(id,this);
		}
		removePaginationFromLink();
		reloadProductListing(frm);
		//searchProducts(frm,0,0,1,1);
	});

	$(document).on('change', 'input[name=optionvalues]', function() {
		var id= $(this).parent().parent().find('label').attr('id');
		if($(this).is(":checked")){
			addFilter(id,this);
			addToSearchQueryString(id,this);
		}else{
			removeFilter(id,this);
		}
		removePaginationFromLink();
		reloadProductListing(frm);
		//searchProducts(frm,0,0,1,1);
	});

	$(document).on('change', 'input[name=conditions]', function() {
		var id= $(this).parent().parent().find('label').attr('id');
		if($(this).is(":checked")){
			addFilter(id,this);
			addToSearchQueryString(id,this);
		}else{
			removeFilter(id,this);
		}
		removePaginationFromLink();
		reloadProductListing(frm);
		//searchProducts(frm,0,0,1,1);
	});

	$(document).on('change', 'input[name=free_shipping]', function() {
		alert("Pending...");
	});

	$(document).on('change', 'select[name=pageSize]', function() {
		removePageSideFromLink();
		removePaginationFromLink();
		reloadProductListing();
	});

	$(document).on('change', 'select[name=sortBy]', function() {
		removePageSideFromLink();
		removePaginationFromLink();
		reloadProductListing();
	});

	$(document).on('change', 'input[name=out_of_stock]', function() {
		var id= $(this).parent().parent().find('label').attr('id');
		if($(this).is(":checked")){
			addFilter(id,this);
			addToSearchQueryString(id,this);
		}else{
			removeFilter(id,this);
		}
		removePaginationFromLink();
		reloadProductListing(frm);
		//searchProducts(frm,0,0,1,1);
	});

	$(document).on('blur', 'input[name=priceFilterMinValue]', function(e) {
		e.preventDefault();
		removePaginationFromLink();
		addPricefilter(true);
	});

	$(document).on('blur', 'input[name=priceFilterMaxValue]', function(e) {
		e.preventDefault();
		removePaginationFromLink();
		addPricefilter(true);
	});

	$(document).on('keyup', 'input[name=priceFilterMinValue]', function(e) {
		var code = e.which;
		if( code == 13 ) {
			e.preventDefault();
			removePaginationFromLink();
			addPricefilter(true);
		}
	});

	$(document).on('keyup', 'input[name=priceFilterMaxValue]', function(e) {
		var code = e.which;
		if( code == 13 ) {
			e.preventDefault();
			removePaginationFromLink();
			addPricefilter(true);
		}
	});

	/* ] */

	$(window).on('load',function(){
		showSelectedFilters();
		initialize();
	})

	function showSelectedFilters(){
		if(($( "#filters" ).find('a').length)>0){
			$('#resetAll').css('display','block');
		}else{
			$('#resetAll').css('display','none');
		}
	}
	/* for toggling of grid/list view[ */

	$(document).on('click', '.list-grid-toggle', function() {
	  var txt = $(".icon").hasClass('icon-grid') ? 'List' : 'Grid';
	  $('.icon').toggleClass('icon-grid');
	  if($(".icon").hasClass('icon-grid')){
		$('#productsList').removeClass('listing-products--grid').addClass('listing-products--list');
	  }else{
		$('#productsList').removeClass('listing-products--list').addClass('listing-products--grid');
	  }
	  /* $(".label").text(txt); */
	});

	/* ] */

	/******** function for left collapseable links  ****************/
	$(".block__body-js").show();
	$(".block__head-js").click(function(){
		$(this).toggleClass("is-active");
	});

	$(".block__head-js").click(function(){
		$(this).siblings(".block__body-js").slideToggle("slow");
	});

	var ww = document.body.clientWidth;
	if (ww <= 1050) {
		$(".block__body-js").hide();
		$(".block__body-js:first").show();
	}else{
		$(".block__body-js").show();
	}

	/******** function for left filters mobile  ****************/
	$(document).on('click', '.btn--filter', function() {
		$(this).toggleClass("is-active");
		var el = $("html");
		if(el.hasClass('filter__show')) el.removeClass("filter__show");
		else el.addClass('filter__show');
		return false;
	});

	$(document).on('click', 'html,.overlay--filter', function() {
		if($('html').hasClass('filter__show')){
			$('.btn--filter').removeClass("is-active");
			$('html').removeClass('filter__show');
		}
	});

	$(document).on('click', '.filters', function(e) {
		e.stopPropagation();
	});

	if($(window).width()<1050){
		if($(".grids")[0]){
			$('.grids').masonry({
			  itemSelector: '.grids__item',
			});
		}
	}

});

/* function updateQueryStringParameter(uri, key, value) {
  var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
  var separator = uri.indexOf('?') !== -1 ? "&" : "?";
  if (uri.match(re)) {
	return uri.replace(re, '$1' + key + "=" + value + '$2');
  }
  else {
	return uri + separator + key + "=" + value;
  }
} */

function htmlEncode(value){
  return $('<div/>').text(value).html();
}

function addFilter(id,obj){
	removePaginationFromLink();
	var click = "onclick=removeFilter('"+id+"',this)";
	$filter = $(obj).parent().text();
	$filterVal = htmlEncode($(obj).parent().text());
	if(!$('#filters').find('a').hasClass(id)){
		$('#filters').append("<a href='javascript:void(0);' class="+id+"   "+click+ ">"+$filterVal+"</a>");
	}
}

function resetListingFilter(){
    searchArr = [];
    document.frmProductSearch.reset();
    document.frmProductSearchPaging.reset();
    var frm = document.frmProductSearch;
    $('#filters a').each(function(){
        id = $(this).attr('class');
        clearFilters(id,this);
    });
    updatePriceFilter();
    reloadProductListing(frm);
    //searchProducts(frm,0,0,1,1);
}

function addPaginationInlink(page){
	searchArr['page'] = page;
}

function removePaginationFromLink(){
	if(typeof searchArr['page'] == 'undefined') {return;}
	delete searchArr['page'];
	var frm = document.frmProductSearchPaging;
	$(frm.page).val(1);
}

function removePageSideFromLink(){
	if(typeof searchArr['pagesize'] == 'undefined') {return;}
	delete searchArr['pagesize'];
}


function removeFilter(id,obj){
	$('.'+id).remove();
	$('#'+id).find('input[type=\'checkbox\']').attr('checked', false);
	var frm = document.frmProductSearch;
	/* form submit upon onchange of form elements select box[ */
	removeFromSearchQueryString(id);
	reloadProductListing(frm);
	//searchProducts(frm,0,0,1,1);
}

function clearFilters(id,obj){
	$('.'+id).remove();
	$('#'+id).find('input[type=\'checkbox\']').attr('checked', false);
}

function addToSearchQueryString(id,obj){
	//$filter = $(obj).parent().text();
	var attrVal = $(obj).attr('data-title')
	if (typeof attrVal !== typeof undefined && attrVal !== false) {
		$filterVal = htmlEncode(removeSpecialCharacter(attrVal));
	}else{
		$filterVal = htmlEncode(removeSpecialCharacter($(obj).parent().text()));
	}
	$filterVal = $filterVal.trim().toLowerCase();
	//searchArr[id] = encodeURIComponent($filterVal.replace(/ /g,'-'));
	searchArr[id] = $filterVal.replace(/ /g,'-');
}

function removeSpecialCharacter($str){
	return $str.replace(/[&\/\\#,+()$~%.'":*?<>{}]/g, '');
}

function removeFromSearchQueryString(key){
	delete searchArr[key];
}

function getSearchQueryUrl(includeBaseUrl){
	url = '';
	if(typeof includeBaseUrl != 'undefined' || includeBaseUrl != null){
		url = $currentPageUrl;
	}

	var keyword = $("input[id=keyword]").val();
	if(keyword !=''){
		delete searchArr['keyword'];
		url = url +'/'+'keyword-'+keyword.replace(/_/g,'-');
	}

	var category = parseInt($("input[id=searched_category]").val());
	if(category > 0){
		delete searchArr['category'];
		url = url +'/'+'category-'+category;
	}

	for (var key in searchArr) {
		url = url +'/'+ key.replace(/_/g,'-') + '-'+ searchArr[key];
	}

	/* var currency = parseInt($("input[name=currency_id]").val());
	if(currency > 0){
		delete searchArr['currency'];
		url = url +'/'+'currency-'+currency;
	} */

	var featured = parseInt($("input[name=featured]").val());
	if(featured > 0){
		url = url +'/'+'featured-'+featured;
	}

	var collection_id = parseInt($("input[name=collection_id]").val());
	if(collection_id > 0){
		url = url +'/'+'collection-'+collection_id;
	}

	var shop_id = parseInt($("input[name=shop_id]").val());
	if(shop_id > 0){
		url = url +'/'+'shop-'+shop_id;
	}

	/* var page = parseInt($("input[name=page]").val());
	if(page > 1){
		url = url +'/'+'page-'+page;
	} */

	var e = document.getElementById("sortBy");
	if($(e).is("select")) {
		var sortBy = e.options[e.selectedIndex].value;
	}else{
		var sortBy = e.value;
	}

	if(sortBy){
		url = url +'/'+'sort-'+sortBy.replace(/_/g,'-');
	}

	var e = document.getElementById("pageSize");
	var pageSize = parseInt(e.options[e.selectedIndex].value);
	if(pageSize > 0){
		url = url +'/'+'pagesize-'+pageSize;
	}

	return encodeURI(url);
}

function addPricefilter(reloadPage){
	if(typeof reloadPage == 'undefined'){
		reloadPage = false;
	}
	$('.price').remove();
	if(!$('#filters').find('a').hasClass('price')){
		$('#filters').append('<a href="javascript:void(0)" class="price" onclick="removePriceFilter(this)" >'+currencySymbolLeft+$("input[name=priceFilterMinValue]").val()+currencySymbolRight+' - '+currencySymbolLeft+$("input[name=priceFilterMaxValue]").val()+currencySymbolRight+'</a>');
	}
	searchArr['price_min_range'] = $("input[name=priceFilterMinValue]").val();
	searchArr['price_max_range'] = $("input[name=priceFilterMaxValue]").val();
	searchArr['currency'] = langLbl.siteCurrencyId;
	var frm = document.frmProductSearch;
	if(reloadPage){
		reloadProductListing(frm);
	}
	//searchProducts(frm,0,0,1,1);
}
function removePriceFilter(reloadPage){
	if(typeof reloadPage == 'undefined'){
		reloadPage = true;
	}
	updatePriceFilter();
	var frm = document.frmProductSearch;
	delete searchArr['price_min_range'];
	delete searchArr['price_max_range'];
	delete searchArr['currency'];
	if(reloadPage){
		reloadProductListing(frm);
	}
	//searchProducts(frm,0,0,1,1);
	$('.price').remove();
}

function updatePriceFilter(minPrice,maxPrice,addPriceFilter){
	if(typeof addPriceFilter == 'undefined'){
		addPriceFilter = false;
	}

	if(typeof minPrice == 'undefined' || typeof maxPrice == 'undefined'){
		minPrice = $("#filterDefaultMinValue").val();
		maxPrice = $("#filterDefaultMaxValue").val();
	}

	$('input[name="priceFilterMinValue"]').val(minPrice);
	$('input[name="priceFilterMaxValue"]').val(maxPrice);

	if(addPriceFilter){
		addPricefilter();
	}

	var frm = document.frmProductSearch;
	var $range = $("#price_range");
	range = $range.data("ionRangeSlider");
	updateRange(minPrice,maxPrice);
    if (typeof range !== 'undefined'){
        range.reset();
    }
}

(function() {
	updateRange = function (from,to) {
        if (typeof range !== 'undefined'){
            range.update({
                from: from,
                to: to
            });
        }
	};

	bannerAdds = function(url){
		fcom.ajax(url, '', function(res){
			$("#searchPageBanners").html(res);
		});
	};

	reloadProductListing = function(frm){
		getSetSelectedOptionsUrl(frm);
		window.location.href = getSearchQueryUrl(true)+'/';
	};

	searchProducts = function(frm){
        var keyword = $.trim($(frm.keyword).val());
        if (3 > keyword.length || '' === keyword) {
            $.mbsmessage(langLbl.searchString, true, 'alert--danger');
            return;
        }
		$("input[id=keyword]").val(keyword);
		reloadProductListing(frm);
	};

	loadProductListingfilters = function(frm){
		var url = window.location.href;
		if($currentPageUrl == removeLastSpace(url)+'/index'){
			url = fcom.makeUrl('Products','filters');
		}else{
			url = url.replace($currentPageUrl, fcom.makeUrl('Products','filters'));
		}

		if (url.indexOf("products/filters") == -1) {
			url = fcom.makeUrl('Products','filters');
	    }
		//url = fcom.makeUrl('Products','filters');
		var data = fcom.frmData(frm);
		fcom.ajax(url, data, function(res){
			$('.productFilters-js').html(res);
			getSetSelectedOptionsUrl(frm);
		});
	};

	removeLastSpace = function(str){
		return str.replace(/\/*$/, "");
	}

	getSetSelectedOptionsUrl = function(frm){
		var data = fcom.frmData(frm);

		/* Category filter value pickup[ */
		var category=[];
		$("input:checkbox[name=category]:checked").each(function(){
			var id = $(this).parent().parent().find('label').attr('id');
			addToSearchQueryString (id,this);
			addFilter (id,this);
			category.push($(this).val());
		});
		if ( category.length ){
			data=data+"&category="+[category];
		}
		/* ] */

		/* brands filter value pickup[ */
		var brands=[];
		$("input:checkbox[name=brands]:checked").each(function(){
			var id = $(this).parent().parent().find('label').attr('id');
			addToSearchQueryString (id,this);
			addFilter (id,this);
			brands.push($(this).val());
		});
		if ( brands.length ){
			data=data+"&brand="+[brands];
		}
		/* ] */

		/* Option filter value pickup[ */
		var optionvalues=[];
		$("input:checkbox[name=optionvalues]:checked").each(function(){
			var id = $(this).parent().parent().find('label').attr('id');
			addToSearchQueryString (id,this);
			addFilter (id,this);
			optionvalues.push($(this).val());
		});
		if ( optionvalues.length ){
			data=data+"&optionvalue="+[optionvalues];
		}
		/* ] */

		/* condition filters value pickup[ */
		var conditions=[];
		$("input:checkbox[name=conditions]:checked").each(function(){
			var id = $(this).parent().parent().find('label').attr('id');
			addToSearchQueryString (id,this);
			addFilter (id,this);
			conditions.push($(this).val());
		});
		if ( conditions.length ){
			data=data+"&condition="+[conditions];
		}
		/* ] */

		/* Free Shipping Filter value pickup[ */

		/* ] */

		/* Out Of Stock Filter value pickup[ */
		$("input:checkbox[name=out_of_stock]:checked").each(function(){
			var id = $(this).parent().parent().find('label').attr('id');
			addToSearchQueryString (id,this);
			addFilter (id,this);
			data=data+"&out_of_stock=1";
		});
		/* ] */

		/* price filter value pickup[ */
		if(typeof $("input[name=priceFilterMinValue]").val() != "undefined"){
			data = data+"&min_price_range="+$("input[name=priceFilterMinValue]").val();
		}

		if(typeof $("input[name=priceFilterMaxValue]").val() != "undefined"){
			data = data+"&max_price_range="+$("input[name=priceFilterMaxValue]").val();
		}

		if ( ($("input[name=filterDefaultMinValue]").val() !=  $("input[name=priceFilterMinValue]").val()) || ($("input[name=filterDefaultMaxValue]").val() !=  $("input[name=priceFilterMaxValue]").val())){
			addPricefilter(false);
		}

		return data;
	};

	goToProductListingSearchPage = function(page) {
		if(typeof page == undefined || page == null){
			page = 1;
		}

		removePaginationFromLink(page);
		var frm = document.frmProductSearchPaging;
		$(frm.page).val(page);
		$("form[name='frmProductSearchPaging']").remove();
		getSetSelectedOptionsUrl(frm);
		window.location.href = getSearchQueryUrl(true)+'/page-'+page+'/';
		//searchProducts(frm,0,0,1,1);
		/* $('html, body').animate({ scrollTop: 0 }, 'slow'); */
	};

	saveProductSearch = function() {
		if( isUserLogged() == 0 ){
			loginPopUpBox();
			return false;
		}
		$.facebox(function() {
		fcom.ajax(fcom.makeUrl('SavedProductsSearch','form'), '' ,function(ans){
			$.facebox(ans,'faceboxWidth small-fb-width collection-ui-popup');
				if( ans.status ){
					$(document).trigger('close.facebox');
				}
			});
		});
		return false;
	};

	setupSaveProductSearch = function(frm){
		if ( !$(frm).validate() ) return false;
		var data = fcom.frmData(frm);
		data = data+"&pssearch_type="+$productSearchPageType;
		data = data+"&pssearch_record_id="+$recordId;
		data = data+"&curr_page="+$currentPageUrl;
		fcom.updateWithAjax(fcom.makeUrl('SavedProductsSearch', 'setup'), data, function(ans) {
			if( ans.status ){
				$(document).trigger('close.facebox');
			}
		});
	};


})();
/* Ion.RangeSlider
// version 2.1.4 Build: 355
// © Denis Ineshin, 2016
// https://github.com/IonDen
//
// Project page:    http://ionden.com/a/plugins/ion.rangeSlider/en.html
// GitHub page:     https://github.com/IonDen/ion.rangeSlider
//
// Released under MIT licence:
// http://ionden.com/a/plugins/licence-en.html
// =====================================================================================================================
*/
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], function ($) {
            factory($, document, window, navigator);
        });
    } else {
        factory(jQuery, document, window, navigator);
    }
} (function ($, document, window, navigator, undefined) {
    "use strict";

     
    var plugin_count = 0;

    /*// IE8 fix*/
    var is_old_ie = (function () {
        var n = navigator.userAgent,
            r = /msie\s\d+/i,
            v;
        if (n.search(r) > 0) {
            v = r.exec(n).toString();
            v = v.split(" ")[1];
            if (v < 9) {
                $("html").addClass("lt-ie9");
                return true;
            }
        }
        return false;
    } ());
    if (!Function.prototype.bind) {
        Function.prototype.bind = function bind(that) {

            var target = this;
            var slice = [].slice;

            if (typeof target != "function") {
                throw new TypeError();
            }

            var args = slice.call(arguments, 1),
                bound = function () {

                    if (this instanceof bound) {

                        var F = function(){};
                        F.prototype = target.prototype;
                        var self = new F();

                        var result = target.apply(
                            self,
                            args.concat(slice.call(arguments))
                        );
                        if (Object(result) === result) {
                            return result;
                        }
                        return self;

                    } else {

                        return target.apply(
                            that,
                            args.concat(slice.call(arguments))
                        );

                    }

                };

            return bound;
        };
    }
    if (!Array.prototype.indexOf) {
        Array.prototype.indexOf = function(searchElement, fromIndex) {
            var k;
            if (this == null) {
                throw new TypeError('"this" is null or not defined');
            }
            var O = Object(this);
            var len = O.length >>> 0;
            if (len === 0) {
                return -1;
            }
            var n = +fromIndex || 0;
            if (Math.abs(n) === Infinity) {
                n = 0;
            }
            if (n >= len) {
                return -1;
            }
            k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);
            while (k < len) {
                if (k in O && O[k] === searchElement) {
                    return k;
                }
                k++;
            }
            return -1;
        };
    }


/*
    // =================================================================================================================
    // Template
*/
    var base_html =
        '<span class="irs">' +
        '<span class="irs-line" tabindex="-1"><span class="irs-line-left"></span><span class="irs-line-mid"></span><span class="irs-line-right"></span></span>' +
        '<span class="irs-min">0</span><span class="irs-max">1</span>' +
        '<span class="irs-from">0</span><span class="irs-to">0</span><span class="irs-single">0</span>' +
        '</span>' +
        '<span class="irs-grid"></span>' +
        '<span class="irs-bar"></span>';

    var single_html =
        '<span class="irs-bar-edge"></span>' +
        '<span class="irs-shadow shadow-single"></span>' +
        '<span class="irs-slider single"></span>';

    var double_html =
        '<span class="irs-shadow shadow-from"></span>' +
        '<span class="irs-shadow shadow-to"></span>' +
        '<span class="irs-slider from"></span>' +
        '<span class="irs-slider to"></span>';

    var disable_html =
        '<span class="irs-disable-mask"></span>';


/*
    // =================================================================================================================
    // Core
*/
    /**
     * Main plugin constructor
     *
     * @param input {Object} link to base input element
     * @param options {Object} slider config
     * @param plugin_count {Number}
     * @constructor
     */
    var IonRangeSlider = function (input, options, plugin_count) {
        this.VERSION = "2.1.4";
        this.input = input;
        this.plugin_count = plugin_count;
        this.current_plugin = 0;
        this.calc_count = 0;
        this.update_tm = 0;
        this.old_from = 0;
        this.old_to = 0;
        this.old_min_interval = null;
        this.raf_id = null;
        this.dragging = false;
        this.force_redraw = false;
        this.no_diapason = false;
        this.is_key = false;
        this.is_update = false;
        this.is_start = true;
        this.is_finish = false;
        this.is_active = false;
        this.is_resize = false;
        this.is_click = false;

       /* // cache for links to all DOM elements*/
        this.$cache = {
            win: $(window),
            body: $(document.body),
            input: $(input),
            cont: null,
            rs: null,
            min: null,
            max: null,
            from: null,
            to: null,
            single: null,
            bar: null,
            line: null,
            s_single: null,
            s_from: null,
            s_to: null,
            shad_single: null,
            shad_from: null,
            shad_to: null,
            edge: null,
            grid: null,
            grid_labels: []
        };

       /* // storage for measure variables*/
        this.coords = {
           /* // left*/
            x_gap: 0,
            x_pointer: 0,

           /* // width*/
            w_rs: 0,
            w_rs_old: 0,
            w_handle: 0,

           /* // percents*/
            p_gap: 0,
            p_gap_left: 0,
            p_gap_right: 0,
            p_step: 0,
            p_pointer: 0,
            p_handle: 0,
            p_single_fake: 0,
            p_single_real: 0,
            p_from_fake: 0,
            p_from_real: 0,
            p_to_fake: 0,
            p_to_real: 0,
            p_bar_x: 0,
            p_bar_w: 0,

            /*// grid*/
            grid_gap: 0,
            big_num: 0,
            big: [],
            big_w: [],
            big_p: [],
            big_x: []
        };

        /*// storage for labels measure variables*/
        this.labels = {
           /* // width*/
            w_min: 0,
            w_max: 0,
            w_from: 0,
            w_to: 0,
            w_single: 0,

           /* // percents*/
            p_min: 0,
            p_max: 0,
            p_from_fake: 0,
            p_from_left: 0,
            p_to_fake: 0,
            p_to_left: 0,
            p_single_fake: 0,
            p_single_left: 0
        };



        /**
         * get and validate config
         */
        var $inp = this.$cache.input,
            val = $inp.prop("value"),
            config, config_from_data, prop;

       /* // default config*/
        config = {
            type: "single",

            min: 10,
            max: 100,
            from: null,
            to: null,
            step: 1,

            min_interval: 0,
            max_interval: 0,
            drag_interval: false,

            values: [],
            p_values: [],

            from_fixed: false,
            from_min: null,
            from_max: null,
            from_shadow: false,

            to_fixed: false,
            to_min: null,
            to_max: null,
            to_shadow: false,

            prettify_enabled: true,
            prettify_separator: " ",
            prettify: null,

            force_edges: false,

            keyboard: false,
            keyboard_step: 5,

            grid: false,
            grid_margin: true,
            grid_num: 4,
            grid_snap: false,

            hide_min_max: false,
            hide_from_to: false,

            prefix: "",
            postfix: "",
            max_postfix: "",
            decorate_both: true,
            values_separator: " — ",

            input_values_separator: ";",

            disable: false,

            onStart: null,
            onChange: null,
            onFinish: null,
            onUpdate: null
        };



       /* // config from data-attributes extends js config*/
        config_from_data = {
            type: $inp.data("type"),

            min: $inp.data("min"),
            max: $inp.data("max"),
            from: $inp.data("from"),
            to: $inp.data("to"),
            step: $inp.data("step"),

            min_interval: $inp.data("minInterval"),
            max_interval: $inp.data("maxInterval"),
            drag_interval: $inp.data("dragInterval"),

            values: $inp.data("values"),

            from_fixed: $inp.data("fromFixed"),
            from_min: $inp.data("fromMin"),
            from_max: $inp.data("fromMax"),
            from_shadow: $inp.data("fromShadow"),

            to_fixed: $inp.data("toFixed"),
            to_min: $inp.data("toMin"),
            to_max: $inp.data("toMax"),
            to_shadow: $inp.data("toShadow"),

            prettify_enabled: $inp.data("prettifyEnabled"),
            prettify_separator: $inp.data("prettifySeparator"),

            force_edges: $inp.data("forceEdges"),

            keyboard: $inp.data("keyboard"),
            keyboard_step: $inp.data("keyboardStep"),

            grid: $inp.data("grid"),
            grid_margin: $inp.data("gridMargin"),
            grid_num: $inp.data("gridNum"),
            grid_snap: $inp.data("gridSnap"),

            hide_min_max: $inp.data("hideMinMax"),
            hide_from_to: $inp.data("hideFromTo"),

            prefix: $inp.data("prefix"),
            postfix: $inp.data("postfix"),
            max_postfix: $inp.data("maxPostfix"),
            decorate_both: $inp.data("decorateBoth"),
            values_separator: $inp.data("valuesSeparator"),

            input_values_separator: $inp.data("inputValuesSeparator"),

            disable: $inp.data("disable")
        };
        config_from_data.values = config_from_data.values && config_from_data.values.split(",");

        for (prop in config_from_data) {
            if (config_from_data.hasOwnProperty(prop)) {
                if (!config_from_data[prop] && config_from_data[prop] !== 0) {
                    delete config_from_data[prop];
                }
            }
        }



        /*// input value extends default config*/
        if (val) {
            val = val.split(config_from_data.input_values_separator || options.input_values_separator || ";");

            if (val[0] && val[0] == +val[0]) {
                val[0] = +val[0];
            }
            if (val[1] && val[1] == +val[1]) {
                val[1] = +val[1];
            }

            if (options && options.values && options.values.length) {
                config.from = val[0] && options.values.indexOf(val[0]);
                config.to = val[1] && options.values.indexOf(val[1]);
            } else {
                config.from = val[0] && +val[0];
                config.to = val[1] && +val[1];
            }
        }



        /*// js config extends default config*/
        $.extend(config, options);


       /* // data config extends config*/
        $.extend(config, config_from_data);
        this.options = config;



        /*// validate config, to be sure that all data types are correct*/
        this.validate();



       /* // default result object, returned to callbacks*/
        this.result = {
            input: this.$cache.input,
            slider: null,

            min: this.options.min,
            max: this.options.max,

            from: this.options.from,
            from_percent: 0,
            from_value: null,

            to: this.options.to,
            to_percent: 0,
            to_value: null
        };



        this.init();
    };

    IonRangeSlider.prototype = {

        /**
         * Starts or updates the plugin instance
         *
         * @param is_update {boolean}
         */
        init: function (is_update) {
            this.no_diapason = false;
            this.coords.p_step = this.convertToPercent(this.options.step, true);

            this.target = "base";

            this.toggleInput();
            this.append();
            this.setMinMax();

            if (is_update) {
                this.force_redraw = true;
                this.calc(true);

              /*  // callbacks called*/
                this.callOnUpdate();
            } else {
                this.force_redraw = true;
                this.calc(true);

                /*// callbacks called*/
                this.callOnStart();
            }

            this.updateScene();
        },

        /**
         * Appends slider template to a DOM
         */
        append: function () {
            var container_html = '<span class="irs js-irs-' + this.plugin_count + '"></span>';
            this.$cache.input.before(container_html);
            this.$cache.input.prop("readonly", true);
            this.$cache.cont = this.$cache.input.prev();
            this.result.slider = this.$cache.cont;

            this.$cache.cont.html(base_html);
            this.$cache.rs = this.$cache.cont.find(".irs");
            this.$cache.min = this.$cache.cont.find(".irs-min");
            this.$cache.max = this.$cache.cont.find(".irs-max");
            this.$cache.from = this.$cache.cont.find(".irs-from");
            this.$cache.to = this.$cache.cont.find(".irs-to");
            this.$cache.single = this.$cache.cont.find(".irs-single");
            this.$cache.bar = this.$cache.cont.find(".irs-bar");
            this.$cache.line = this.$cache.cont.find(".irs-line");
            this.$cache.grid = this.$cache.cont.find(".irs-grid");

            if (this.options.type === "single") {
                this.$cache.cont.append(single_html);
                this.$cache.edge = this.$cache.cont.find(".irs-bar-edge");
                this.$cache.s_single = this.$cache.cont.find(".single");
                this.$cache.from[0].style.visibility = "hidden";
                this.$cache.to[0].style.visibility = "hidden";
                this.$cache.shad_single = this.$cache.cont.find(".shadow-single");
            } else {
                this.$cache.cont.append(double_html);
                this.$cache.s_from = this.$cache.cont.find(".from");
                this.$cache.s_to = this.$cache.cont.find(".to");
                this.$cache.shad_from = this.$cache.cont.find(".shadow-from");
                this.$cache.shad_to = this.$cache.cont.find(".shadow-to");

                this.setTopHandler();
            }

            if (this.options.hide_from_to) {
                this.$cache.from[0].style.display = "none";
                this.$cache.to[0].style.display = "none";
                this.$cache.single[0].style.display = "none";
            }

            this.appendGrid();

            if (this.options.disable) {
                this.appendDisableMask();
                this.$cache.input[0].disabled = true;
            } else {
                this.$cache.cont.removeClass("irs-disabled");
                this.$cache.input[0].disabled = false;
                this.bindEvents();
            }

            if (this.options.drag_interval) {
                this.$cache.bar[0].style.cursor = "ew-resize";
            }
        },

        /**
         * Determine which handler has a priority
         * works only for double slider type
         */
        setTopHandler: function () {
            var min = this.options.min,
                max = this.options.max,
                from = this.options.from,
                to = this.options.to;

            if (from > min && to === max) {
                this.$cache.s_from.addClass("type_last");
            } else if (to < max) {
                this.$cache.s_to.addClass("type_last");
            }
        },

        /**
         * Determine which handles was clicked last
         * and which handler should have hover effect
         *
         * @param target {String}
         */
        changeLevel: function (target) {
            switch (target) {
                case "single":
                    this.coords.p_gap = this.toFixed(this.coords.p_pointer - this.coords.p_single_fake);
                    break;
                case "from":
                    this.coords.p_gap = this.toFixed(this.coords.p_pointer - this.coords.p_from_fake);
                    this.$cache.s_from.addClass("state_hover");
                    this.$cache.s_from.addClass("type_last");
                    this.$cache.s_to.removeClass("type_last");
                    break;
                case "to":
                    this.coords.p_gap = this.toFixed(this.coords.p_pointer - this.coords.p_to_fake);
                    this.$cache.s_to.addClass("state_hover");
                    this.$cache.s_to.addClass("type_last");
                    this.$cache.s_from.removeClass("type_last");
                    break;
                case "both":
                    this.coords.p_gap_left = this.toFixed(this.coords.p_pointer - this.coords.p_from_fake);
                    this.coords.p_gap_right = this.toFixed(this.coords.p_to_fake - this.coords.p_pointer);
                    this.$cache.s_to.removeClass("type_last");
                    this.$cache.s_from.removeClass("type_last");
                    break;
            }
        },

        /**
         * Then slider is disabled
         * appends extra layer with opacity
         */
        appendDisableMask: function () {
            this.$cache.cont.append(disable_html);
            this.$cache.cont.addClass("irs-disabled");
        },

        /**
         * Remove slider instance
         * and ubind all events
         */
        remove: function () {
            this.$cache.cont.remove();
            this.$cache.cont = null;

            this.$cache.line.off("keydown.irs_" + this.plugin_count);

            this.$cache.body.off("touchmove.irs_" + this.plugin_count);
            this.$cache.body.off("mousemove.irs_" + this.plugin_count);

            this.$cache.win.off("touchend.irs_" + this.plugin_count);
            this.$cache.win.off("mouseup.irs_" + this.plugin_count);

            if (is_old_ie) {
                this.$cache.body.off("mouseup.irs_" + this.plugin_count);
                this.$cache.body.off("mouseleave.irs_" + this.plugin_count);
            }

            this.$cache.grid_labels = [];
            this.coords.big = [];
            this.coords.big_w = [];
            this.coords.big_p = [];
            this.coords.big_x = [];

            cancelAnimationFrame(this.raf_id);
        },

        /**
         * bind all slider events
         */
        bindEvents: function () {
            if (this.no_diapason) {
                return;
            }

            this.$cache.body.on("touchmove.irs_" + this.plugin_count, this.pointerMove.bind(this));
            this.$cache.body.on("mousemove.irs_" + this.plugin_count, this.pointerMove.bind(this));

            this.$cache.win.on("touchend.irs_" + this.plugin_count, this.pointerUp.bind(this));
            this.$cache.win.on("mouseup.irs_" + this.plugin_count, this.pointerUp.bind(this));

            this.$cache.line.on("touchstart.irs_" + this.plugin_count, this.pointerClick.bind(this, "click"));
            this.$cache.line.on("mousedown.irs_" + this.plugin_count, this.pointerClick.bind(this, "click"));

            if (this.options.drag_interval && this.options.type === "double") {
                this.$cache.bar.on("touchstart.irs_" + this.plugin_count, this.pointerDown.bind(this, "both"));
                this.$cache.bar.on("mousedown.irs_" + this.plugin_count, this.pointerDown.bind(this, "both"));
            } else {
                this.$cache.bar.on("touchstart.irs_" + this.plugin_count, this.pointerClick.bind(this, "click"));
                this.$cache.bar.on("mousedown.irs_" + this.plugin_count, this.pointerClick.bind(this, "click"));
            }

            if (this.options.type === "single") {
                this.$cache.single.on("touchstart.irs_" + this.plugin_count, this.pointerDown.bind(this, "single"));
                this.$cache.s_single.on("touchstart.irs_" + this.plugin_count, this.pointerDown.bind(this, "single"));
                this.$cache.shad_single.on("touchstart.irs_" + this.plugin_count, this.pointerClick.bind(this, "click"));

                this.$cache.single.on("mousedown.irs_" + this.plugin_count, this.pointerDown.bind(this, "single"));
                this.$cache.s_single.on("mousedown.irs_" + this.plugin_count, this.pointerDown.bind(this, "single"));
                this.$cache.edge.on("mousedown.irs_" + this.plugin_count, this.pointerClick.bind(this, "click"));
                this.$cache.shad_single.on("mousedown.irs_" + this.plugin_count, this.pointerClick.bind(this, "click"));
            } else {
                this.$cache.single.on("touchstart.irs_" + this.plugin_count, this.pointerDown.bind(this, null));
                this.$cache.single.on("mousedown.irs_" + this.plugin_count, this.pointerDown.bind(this, null));

                this.$cache.from.on("touchstart.irs_" + this.plugin_count, this.pointerDown.bind(this, "from"));
                this.$cache.s_from.on("touchstart.irs_" + this.plugin_count, this.pointerDown.bind(this, "from"));
                this.$cache.to.on("touchstart.irs_" + this.plugin_count, this.pointerDown.bind(this, "to"));
                this.$cache.s_to.on("touchstart.irs_" + this.plugin_count, this.pointerDown.bind(this, "to"));
                this.$cache.shad_from.on("touchstart.irs_" + this.plugin_count, this.pointerClick.bind(this, "click"));
                this.$cache.shad_to.on("touchstart.irs_" + this.plugin_count, this.pointerClick.bind(this, "click"));

                this.$cache.from.on("mousedown.irs_" + this.plugin_count, this.pointerDown.bind(this, "from"));
                this.$cache.s_from.on("mousedown.irs_" + this.plugin_count, this.pointerDown.bind(this, "from"));
                this.$cache.to.on("mousedown.irs_" + this.plugin_count, this.pointerDown.bind(this, "to"));
                this.$cache.s_to.on("mousedown.irs_" + this.plugin_count, this.pointerDown.bind(this, "to"));
                this.$cache.shad_from.on("mousedown.irs_" + this.plugin_count, this.pointerClick.bind(this, "click"));
                this.$cache.shad_to.on("mousedown.irs_" + this.plugin_count, this.pointerClick.bind(this, "click"));
            }

            if (this.options.keyboard) {
                this.$cache.line.on("keydown.irs_" + this.plugin_count, this.key.bind(this, "keyboard"));
            }

            if (is_old_ie) {
                this.$cache.body.on("mouseup.irs_" + this.plugin_count, this.pointerUp.bind(this));
                this.$cache.body.on("mouseleave.irs_" + this.plugin_count, this.pointerUp.bind(this));
            }
        },

        /**
         * Mousemove or touchmove
         * only for handlers
         *
         * @param e {Object} event object
         */
        pointerMove: function (e) {
            if (!this.dragging) {
                return;
            }

            var x = e.pageX || e.originalEvent.touches && e.originalEvent.touches[0].pageX;
            this.coords.x_pointer = x - this.coords.x_gap;

            this.calc();
        },

        /**
         * Mouseup or touchend
         * only for handlers
         *
         * @param e {Object} event object
         */
        pointerUp: function (e) {
            if (this.current_plugin !== this.plugin_count) {
                return;
            }

            if (this.is_active) {
                this.is_active = false;
            } else {
                return;
            }

            this.$cache.cont.find(".state_hover").removeClass("state_hover");

            this.force_redraw = true;

            if (is_old_ie) {
                $("*").prop("unselectable", false);
            }

            this.updateScene();
            this.restoreOriginalMinInterval();

           /* // callbacks call*/
            if ($.contains(this.$cache.cont[0], e.target) || this.dragging) {
                this.is_finish = true;
                this.callOnFinish();
            }
            
            this.dragging = false;
        },

        /**
         * Mousedown or touchstart
         * only for handlers
         *
         * @param target {String|null}
         * @param e {Object} event object
         */
        pointerDown: function (target, e) {
            e.preventDefault();
            var x = e.pageX || e.originalEvent.touches && e.originalEvent.touches[0].pageX;
            if (e.button === 2) {
                return;
            }

            if (target === "both") {
                this.setTempMinInterval();
            }

            if (!target) {
                target = this.target;
            }

            this.current_plugin = this.plugin_count;
            this.target = target;

            this.is_active = true;
            this.dragging = true;

            this.coords.x_gap = this.$cache.rs.offset().left;
            this.coords.x_pointer = x - this.coords.x_gap;

            this.calcPointerPercent();
            this.changeLevel(target);

            if (is_old_ie) {
                $("*").prop("unselectable", true);
            }

            this.$cache.line.trigger("focus");

            this.updateScene();
        },

        /**
         * Mousedown or touchstart
         * for other slider elements, like diapason line
         *
         * @param target {String}
         * @param e {Object} event object
         */
        pointerClick: function (target, e) {
            e.preventDefault();
            var x = e.pageX || e.originalEvent.touches && e.originalEvent.touches[0].pageX;
            if (e.button === 2) {
                return;
            }

            this.current_plugin = this.plugin_count;
            this.target = target;

            this.is_click = true;
            this.coords.x_gap = this.$cache.rs.offset().left;
            this.coords.x_pointer = +(x - this.coords.x_gap).toFixed();

            this.force_redraw = true;
            this.calc();

            this.$cache.line.trigger("focus");
        },

        /**
         * Keyborard controls for focused slider
         *
         * @param target {String}
         * @param e {Object} event object
         * @returns {boolean|undefined}
         */
        key: function (target, e) {
            if (this.current_plugin !== this.plugin_count || e.altKey || e.ctrlKey || e.shiftKey || e.metaKey) {
                return;
            }

            switch (e.which) {
                case 83: /* W*/
                case 65: /* A*/
                case 40: /* DOWN*/
                case 37: /* LEFT*/
                    e.preventDefault();
                    this.moveByKey(false);
                    break;

                case 87: /* S*/
                case 68: /* D*/
                case 38: /* UP*/
                case 39: /* RIGHT*/
                    e.preventDefault();
                    this.moveByKey(true);
                    break;
            }

            return true;
        },

        /**
         * Move by key. Beta
         * @todo refactor than have plenty of time
         *
         * @param right {boolean} direction to move
         */
        moveByKey: function (right) {
            var p = this.coords.p_pointer;

            if (right) {
                p += this.options.keyboard_step;
            } else {
                p -= this.options.keyboard_step;
            }

            this.coords.x_pointer = this.toFixed(this.coords.w_rs / 100 * p);
            this.is_key = true;
            this.calc();
        },

        /**
         * Set visibility and content
         * of Min and Max labels
         */
        setMinMax: function () {
            if (!this.options) {
                return;
            }

            if (this.options.hide_min_max) {
                this.$cache.min[0].style.display = "none";
                this.$cache.max[0].style.display = "none";
                return;
            }

            if (this.options.values.length) {
                this.$cache.min.html(this.decorate(this.options.p_values[this.options.min]));
                this.$cache.max.html(this.decorate(this.options.p_values[this.options.max]));
            } else {
                this.$cache.min.html(this.decorate(this._prettify(this.options.min), this.options.min));
                this.$cache.max.html(this.decorate(this._prettify(this.options.max), this.options.max));
            }

            this.labels.w_min = this.$cache.min.outerWidth(false);
            this.labels.w_max = this.$cache.max.outerWidth(false);
        },

        /**
         * Then dragging interval, prevent interval collapsing
         * using min_interval option
         */
        setTempMinInterval: function () {
            var interval = this.result.to - this.result.from;

            if (this.old_min_interval === null) {
                this.old_min_interval = this.options.min_interval;
            }

            this.options.min_interval = interval;
        },

        /**
         * Restore min_interval option to original
         */
        restoreOriginalMinInterval: function () {
            if (this.old_min_interval !== null) {
                this.options.min_interval = this.old_min_interval;
                this.old_min_interval = null;
            }
        },


/*
        // =============================================================================================================
        // Calculations
*/
        /**
         * All calculations and measures start here
         *
         * @param update {boolean=}
         */
        calc: function (update) {
            if (!this.options) {
                return;
            }

            this.calc_count++;

            if (this.calc_count === 10 || update) {
                this.calc_count = 0;
                this.coords.w_rs = this.$cache.rs.outerWidth(false);

                this.calcHandlePercent();
            }

            if (!this.coords.w_rs) {
                return;
            }

            this.calcPointerPercent();
            var handle_x = this.getHandleX();

            if (this.target === "click") {
                this.coords.p_gap = this.coords.p_handle / 2;
                handle_x = this.getHandleX();

                if (this.options.drag_interval) {
                    this.target = "both_one";
                } else {
                    this.target = this.chooseHandle(handle_x);
                }
            }

            switch (this.target) {
                case "base":
                    var w = (this.options.max - this.options.min) / 100,
                        f = (this.result.from - this.options.min) / w,
                        t = (this.result.to - this.options.min) / w;

                    this.coords.p_single_real = this.toFixed(f);
                    this.coords.p_from_real = this.toFixed(f);
                    this.coords.p_to_real = this.toFixed(t);

                    this.coords.p_single_real = this.checkDiapason(this.coords.p_single_real, this.options.from_min, this.options.from_max);
                    this.coords.p_from_real = this.checkDiapason(this.coords.p_from_real, this.options.from_min, this.options.from_max);
                    this.coords.p_to_real = this.checkDiapason(this.coords.p_to_real, this.options.to_min, this.options.to_max);

                    this.coords.p_single_fake = this.convertToFakePercent(this.coords.p_single_real);
                    this.coords.p_from_fake = this.convertToFakePercent(this.coords.p_from_real);
                    this.coords.p_to_fake = this.convertToFakePercent(this.coords.p_to_real);

                    this.target = null;

                    break;

                case "single":
                    if (this.options.from_fixed) {
                        break;
                    }

                    this.coords.p_single_real = this.convertToRealPercent(handle_x);
                    this.coords.p_single_real = this.calcWithStep(this.coords.p_single_real);
                    this.coords.p_single_real = this.checkDiapason(this.coords.p_single_real, this.options.from_min, this.options.from_max);

                    this.coords.p_single_fake = this.convertToFakePercent(this.coords.p_single_real);

                    break;

                case "from":
                    if (this.options.from_fixed) {
                        break;
                    }

                    this.coords.p_from_real = this.convertToRealPercent(handle_x);
                    this.coords.p_from_real = this.calcWithStep(this.coords.p_from_real);
                    if (this.coords.p_from_real > this.coords.p_to_real) {
                        this.coords.p_from_real = this.coords.p_to_real;
                    }
                    this.coords.p_from_real = this.checkDiapason(this.coords.p_from_real, this.options.from_min, this.options.from_max);
                    this.coords.p_from_real = this.checkMinInterval(this.coords.p_from_real, this.coords.p_to_real, "from");
                    this.coords.p_from_real = this.checkMaxInterval(this.coords.p_from_real, this.coords.p_to_real, "from");

                    this.coords.p_from_fake = this.convertToFakePercent(this.coords.p_from_real);

                    break;

                case "to":
                    if (this.options.to_fixed) {
                        break;
                    }

                    this.coords.p_to_real = this.convertToRealPercent(handle_x);
                    this.coords.p_to_real = this.calcWithStep(this.coords.p_to_real);
                    if (this.coords.p_to_real < this.coords.p_from_real) {
                        this.coords.p_to_real = this.coords.p_from_real;
                    }
                    this.coords.p_to_real = this.checkDiapason(this.coords.p_to_real, this.options.to_min, this.options.to_max);
                    this.coords.p_to_real = this.checkMinInterval(this.coords.p_to_real, this.coords.p_from_real, "to");
                    this.coords.p_to_real = this.checkMaxInterval(this.coords.p_to_real, this.coords.p_from_real, "to");

                    this.coords.p_to_fake = this.convertToFakePercent(this.coords.p_to_real);

                    break;

                case "both":
                    if (this.options.from_fixed || this.options.to_fixed) {
                        break;
                    }

                    handle_x = this.toFixed(handle_x + (this.coords.p_handle * 0.1));

                    this.coords.p_from_real = this.convertToRealPercent(handle_x) - this.coords.p_gap_left;
                    this.coords.p_from_real = this.calcWithStep(this.coords.p_from_real);
                    this.coords.p_from_real = this.checkDiapason(this.coords.p_from_real, this.options.from_min, this.options.from_max);
                    this.coords.p_from_real = this.checkMinInterval(this.coords.p_from_real, this.coords.p_to_real, "from");
                    this.coords.p_from_fake = this.convertToFakePercent(this.coords.p_from_real);

                    this.coords.p_to_real = this.convertToRealPercent(handle_x) + this.coords.p_gap_right;
                    this.coords.p_to_real = this.calcWithStep(this.coords.p_to_real);
                    this.coords.p_to_real = this.checkDiapason(this.coords.p_to_real, this.options.to_min, this.options.to_max);
                    this.coords.p_to_real = this.checkMinInterval(this.coords.p_to_real, this.coords.p_from_real, "to");
                    this.coords.p_to_fake = this.convertToFakePercent(this.coords.p_to_real);

                    break;

                case "both_one":
                    if (this.options.from_fixed || this.options.to_fixed) {
                        break;
                    }

                    var real_x = this.convertToRealPercent(handle_x),
                        from = this.result.from_percent,
                        to = this.result.to_percent,
                        full = to - from,
                        half = full / 2,
                        new_from = real_x - half,
                        new_to = real_x + half;

                    if (new_from < 0) {
                        new_from = 0;
                        new_to = new_from + full;
                    }

                    if (new_to > 100) {
                        new_to = 100;
                        new_from = new_to - full;
                    }

                    this.coords.p_from_real = this.calcWithStep(new_from);
                    this.coords.p_from_real = this.checkDiapason(this.coords.p_from_real, this.options.from_min, this.options.from_max);
                    this.coords.p_from_fake = this.convertToFakePercent(this.coords.p_from_real);

                    this.coords.p_to_real = this.calcWithStep(new_to);
                    this.coords.p_to_real = this.checkDiapason(this.coords.p_to_real, this.options.to_min, this.options.to_max);
                    this.coords.p_to_fake = this.convertToFakePercent(this.coords.p_to_real);

                    break;
            }

            if (this.options.type === "single") {
                this.coords.p_bar_x = (this.coords.p_handle / 2);
                this.coords.p_bar_w = this.coords.p_single_fake;

                this.result.from_percent = this.coords.p_single_real;
                this.result.from = this.convertToValue(this.coords.p_single_real);

                if (this.options.values.length) {
                    this.result.from_value = this.options.values[this.result.from];
                }
            } else {
                this.coords.p_bar_x = this.toFixed(this.coords.p_from_fake + (this.coords.p_handle / 2));
                this.coords.p_bar_w = this.toFixed(this.coords.p_to_fake - this.coords.p_from_fake);

                this.result.from_percent = this.coords.p_from_real;
                this.result.from = this.convertToValue(this.coords.p_from_real);
                this.result.to_percent = this.coords.p_to_real;
                this.result.to = this.convertToValue(this.coords.p_to_real);

                if (this.options.values.length) {
                    this.result.from_value = this.options.values[this.result.from];
                    this.result.to_value = this.options.values[this.result.to];
                }
            }

            this.calcMinMax();
            this.calcLabels();
        },


        /**
         * calculates pointer X in percent
         */
        calcPointerPercent: function () {
            if (!this.coords.w_rs) {
                this.coords.p_pointer = 0;
                return;
            }

            if (this.coords.x_pointer < 0 || isNaN(this.coords.x_pointer)  ) {
                this.coords.x_pointer = 0;
            } else if (this.coords.x_pointer > this.coords.w_rs) {
                this.coords.x_pointer = this.coords.w_rs;
            }

            this.coords.p_pointer = this.toFixed(this.coords.x_pointer / this.coords.w_rs * 100);
        },

        convertToRealPercent: function (fake) {
            var full = 100 - this.coords.p_handle;
            return fake / full * 100;
        },

        convertToFakePercent: function (real) {
            var full = 100 - this.coords.p_handle;
            return real / 100 * full;
        },

        getHandleX: function () {
            var max = 100 - this.coords.p_handle,
                x = this.toFixed(this.coords.p_pointer - this.coords.p_gap);

            if (x < 0) {
                x = 0;
            } else if (x > max) {
                x = max;
            }

            return x;
        },

        calcHandlePercent: function () {
            if (this.options.type === "single") {
                this.coords.w_handle = this.$cache.s_single.outerWidth(false);
            } else {
                this.coords.w_handle = this.$cache.s_from.outerWidth(false);
            }

            this.coords.p_handle = this.toFixed(this.coords.w_handle / this.coords.w_rs * 100);
        },

        /**
         * Find closest handle to pointer click
         *
         * @param real_x {Number}
         * @returns {String}
         */
        chooseHandle: function (real_x) {
            if (this.options.type === "single") {
                return "single";
            } else {
                var m_point = this.coords.p_from_real + ((this.coords.p_to_real - this.coords.p_from_real) / 2);
                if (real_x >= m_point) {
                    return this.options.to_fixed ? "from" : "to";
                } else {
                    return this.options.from_fixed ? "to" : "from";
                }
            }
        },

        /**
         * Measure Min and Max labels width in percent
         */
        calcMinMax: function () {
            if (!this.coords.w_rs) {
                return;
            }

            this.labels.p_min = this.labels.w_min / this.coords.w_rs * 100;
            this.labels.p_max = this.labels.w_max / this.coords.w_rs * 100;
        },

        /**
         * Measure labels width and X in percent
         */
        calcLabels: function () {
            if (!this.coords.w_rs || this.options.hide_from_to) {
                return;
            }

            if (this.options.type === "single") {

                this.labels.w_single = this.$cache.single.outerWidth(false);
                this.labels.p_single_fake = this.labels.w_single / this.coords.w_rs * 100;
                this.labels.p_single_left = this.coords.p_single_fake + (this.coords.p_handle / 2) - (this.labels.p_single_fake / 2);
                this.labels.p_single_left = this.checkEdges(this.labels.p_single_left, this.labels.p_single_fake);

            } else {

                this.labels.w_from = this.$cache.from.outerWidth(false);
                this.labels.p_from_fake = this.labels.w_from / this.coords.w_rs * 100;
                this.labels.p_from_left = this.coords.p_from_fake + (this.coords.p_handle / 2) - (this.labels.p_from_fake / 2);
                this.labels.p_from_left = this.toFixed(this.labels.p_from_left);
                this.labels.p_from_left = this.checkEdges(this.labels.p_from_left, this.labels.p_from_fake);

                this.labels.w_to = this.$cache.to.outerWidth(false);
                this.labels.p_to_fake = this.labels.w_to / this.coords.w_rs * 100;
                this.labels.p_to_left = this.coords.p_to_fake + (this.coords.p_handle / 2) - (this.labels.p_to_fake / 2);
                this.labels.p_to_left = this.toFixed(this.labels.p_to_left);
                this.labels.p_to_left = this.checkEdges(this.labels.p_to_left, this.labels.p_to_fake);

                this.labels.w_single = this.$cache.single.outerWidth(false);
                this.labels.p_single_fake = this.labels.w_single / this.coords.w_rs * 100;
                this.labels.p_single_left = ((this.labels.p_from_left + this.labels.p_to_left + this.labels.p_to_fake) / 2) - (this.labels.p_single_fake / 2);
                this.labels.p_single_left = this.toFixed(this.labels.p_single_left);
                this.labels.p_single_left = this.checkEdges(this.labels.p_single_left, this.labels.p_single_fake);

            }
        },

/*

        // =============================================================================================================
        // Drawings
*/
        /**
         * Main function called in request animation frame
         * to update everything
         */
        updateScene: function () {
            if (this.raf_id) {
                cancelAnimationFrame(this.raf_id);
                this.raf_id = null;
            }

            clearTimeout(this.update_tm);
            this.update_tm = null;

            if (!this.options) {
                return;
            }

            this.drawHandles();

            if (this.is_active) {
                this.raf_id = requestAnimationFrame(this.updateScene.bind(this));
            } else {
                this.update_tm = setTimeout(this.updateScene.bind(this), 300);
            }
        },

        /**
         * Draw handles
         */
        drawHandles: function () {
            this.coords.w_rs = this.$cache.rs.outerWidth(false);

            if (!this.coords.w_rs) {
                return;
            }

            if (this.coords.w_rs !== this.coords.w_rs_old) {
                this.target = "base";
                this.is_resize = true;
            }

            if (this.coords.w_rs !== this.coords.w_rs_old || this.force_redraw) {
                this.setMinMax();
                this.calc(true);
                this.drawLabels();
                if (this.options.grid) {
                    this.calcGridMargin();
                    this.calcGridLabels();
                }
                this.force_redraw = true;
                this.coords.w_rs_old = this.coords.w_rs;
                this.drawShadow();
            }

            if (!this.coords.w_rs) {
                return;
            }

            if (!this.dragging && !this.force_redraw && !this.is_key) {
                return;
            }

            if (this.old_from !== this.result.from || this.old_to !== this.result.to || this.force_redraw || this.is_key) {

                this.drawLabels();

                this.$cache.bar[0].style.left = this.coords.p_bar_x + "%";
                this.$cache.bar[0].style.width = this.coords.p_bar_w + "%";

                if (this.options.type === "single") {
                    this.$cache.s_single[0].style.left = this.coords.p_single_fake + "%";

                    this.$cache.single[0].style.left = this.labels.p_single_left + "%";

                    if (this.options.values.length) {
                        this.$cache.input.prop("value", this.result.from_value);
                    } else {
                        this.$cache.input.prop("value", this.result.from);
                    }
                    this.$cache.input.data("from", this.result.from);
                } else {
                    this.$cache.s_from[0].style.left = this.coords.p_from_fake + "%";
                    this.$cache.s_to[0].style.left = this.coords.p_to_fake + "%";

                    if (this.old_from !== this.result.from || this.force_redraw) {
                        this.$cache.from[0].style.left = this.labels.p_from_left + "%";
                    }
                    if (this.old_to !== this.result.to || this.force_redraw) {
                        this.$cache.to[0].style.left = this.labels.p_to_left + "%";
                    }

                    this.$cache.single[0].style.left = this.labels.p_single_left + "%";

                    if (this.options.values.length) {
                        this.$cache.input.prop("value", this.result.from_value + this.options.input_values_separator + this.result.to_value);
                    } else {
                        this.$cache.input.prop("value", this.result.from + this.options.input_values_separator + this.result.to);
                    }
                    this.$cache.input.data("from", this.result.from);
                    this.$cache.input.data("to", this.result.to);
                }

                if ((this.old_from !== this.result.from || this.old_to !== this.result.to) && !this.is_start) {
                    this.$cache.input.trigger("change");
                }

                this.old_from = this.result.from;
                this.old_to = this.result.to;

        /*        // callbacks call*/
                if (!this.is_resize && !this.is_update && !this.is_start && !this.is_finish) {
                    this.callOnChange();
                }
                if (this.is_key || this.is_click) {
                    this.is_key = false;
                    this.is_click = false;
                    this.callOnFinish();
                }

                this.is_update = false;
                this.is_resize = false;
                this.is_finish = false;
            }

            this.is_start = false;
            this.is_key = false;
            this.is_click = false;
            this.force_redraw = false;
        },

        /**
         * Draw labels
         * measure labels collisions
         * collapse close labels
         */
        drawLabels: function () {
            if (!this.options) {
                return;
            }

            var values_num = this.options.values.length,
                p_values = this.options.p_values,
                text_single,
                text_from,
                text_to;

            if (this.options.hide_from_to) {
                return;
            }

            if (this.options.type === "single") {

                if (values_num) {
                    text_single = this.decorate(p_values[this.result.from]);
                    this.$cache.single.html(text_single);
                } else {
                    text_single = this.decorate(this._prettify(this.result.from), this.result.from);
                    this.$cache.single.html(text_single);
                }

                this.calcLabels();

                if (this.labels.p_single_left < this.labels.p_min + 1) {
                    this.$cache.min[0].style.visibility = "hidden";
                } else {
                    this.$cache.min[0].style.visibility = "visible";
                }

                if (this.labels.p_single_left + this.labels.p_single_fake > 100 - this.labels.p_max - 1) {
                    this.$cache.max[0].style.visibility = "hidden";
                } else {
                    this.$cache.max[0].style.visibility = "visible";
                }

            } else {

                if (values_num) {

                    if (this.options.decorate_both) {
                        text_single = this.decorate(p_values[this.result.from]);
                        text_single += this.options.values_separator;
                        text_single += this.decorate(p_values[this.result.to]);
                    } else {
                        text_single = this.decorate(p_values[this.result.from] + this.options.values_separator + p_values[this.result.to]);
                    }
                    text_from = this.decorate(p_values[this.result.from]);
                    text_to = this.decorate(p_values[this.result.to]);

                    this.$cache.single.html(text_single);
                    this.$cache.from.html(text_from);
                    this.$cache.to.html(text_to);

                } else {

                    if (this.options.decorate_both) {
                        text_single = this.decorate(this._prettify(this.result.from), this.result.from);
                        text_single += this.options.values_separator;
                        text_single += this.decorate(this._prettify(this.result.to), this.result.to);
                    } else {
                        text_single = this.decorate(this._prettify(this.result.from) + this.options.values_separator + this._prettify(this.result.to), this.result.to);
                    }
                    text_from = this.decorate(this._prettify(this.result.from), this.result.from);
                    text_to = this.decorate(this._prettify(this.result.to), this.result.to);

                    this.$cache.single.html(text_single);
                    this.$cache.from.html(text_from);
                    this.$cache.to.html(text_to);

                }

                this.calcLabels();

                var min = Math.min(this.labels.p_single_left, this.labels.p_from_left),
                    single_left = this.labels.p_single_left + this.labels.p_single_fake,
                    to_left = this.labels.p_to_left + this.labels.p_to_fake,
                    max = Math.max(single_left, to_left);

                if (this.labels.p_from_left + this.labels.p_from_fake >= this.labels.p_to_left) {
                    this.$cache.from[0].style.visibility = "hidden";
                    this.$cache.to[0].style.visibility = "hidden";
                    this.$cache.single[0].style.visibility = "visible";

                    if (this.result.from === this.result.to) {
                        if (this.target === "from") {
                            this.$cache.from[0].style.visibility = "visible";
                        } else if (this.target === "to") {
                            this.$cache.to[0].style.visibility = "visible";
                        } else if (!this.target) {
                            this.$cache.from[0].style.visibility = "visible";
                        }
                        this.$cache.single[0].style.visibility = "hidden";
                        max = to_left;
                    } else {
                        this.$cache.from[0].style.visibility = "hidden";
                        this.$cache.to[0].style.visibility = "hidden";
                        this.$cache.single[0].style.visibility = "visible";
                        max = Math.max(single_left, to_left);
                    }
                } else {
                    this.$cache.from[0].style.visibility = "visible";
                    this.$cache.to[0].style.visibility = "visible";
                    this.$cache.single[0].style.visibility = "hidden";
                }

                if (min < this.labels.p_min + 1) {
                    this.$cache.min[0].style.visibility = "hidden";
                } else {
                    this.$cache.min[0].style.visibility = "visible";
                }

                if (max > 100 - this.labels.p_max - 1) {
                    this.$cache.max[0].style.visibility = "hidden";
                } else {
                    this.$cache.max[0].style.visibility = "visible";
                }

            }
        },

        /**
         * Draw shadow intervals
         */
        drawShadow: function () {
            var o = this.options,
                c = this.$cache,

                is_from_min = typeof o.from_min === "number" && !isNaN(o.from_min),
                is_from_max = typeof o.from_max === "number" && !isNaN(o.from_max),
                is_to_min = typeof o.to_min === "number" && !isNaN(o.to_min),
                is_to_max = typeof o.to_max === "number" && !isNaN(o.to_max),

                from_min,
                from_max,
                to_min,
                to_max;

            if (o.type === "single") {
                if (o.from_shadow && (is_from_min || is_from_max)) {
                    from_min = this.convertToPercent(is_from_min ? o.from_min : o.min);
                    from_max = this.convertToPercent(is_from_max ? o.from_max : o.max) - from_min;
                    from_min = this.toFixed(from_min - (this.coords.p_handle / 100 * from_min));
                    from_max = this.toFixed(from_max - (this.coords.p_handle / 100 * from_max));
                    from_min = from_min + (this.coords.p_handle / 2);

                    c.shad_single[0].style.display = "block";
                    c.shad_single[0].style.left = from_min + "%";
                    c.shad_single[0].style.width = from_max + "%";
                } else {
                    c.shad_single[0].style.display = "none";
                }
            } else {
                if (o.from_shadow && (is_from_min || is_from_max)) {
                    from_min = this.convertToPercent(is_from_min ? o.from_min : o.min);
                    from_max = this.convertToPercent(is_from_max ? o.from_max : o.max) - from_min;
                    from_min = this.toFixed(from_min - (this.coords.p_handle / 100 * from_min));
                    from_max = this.toFixed(from_max - (this.coords.p_handle / 100 * from_max));
                    from_min = from_min + (this.coords.p_handle / 2);

                    c.shad_from[0].style.display = "block";
                    c.shad_from[0].style.left = from_min + "%";
                    c.shad_from[0].style.width = from_max + "%";
                } else {
                    c.shad_from[0].style.display = "none";
                }

                if (o.to_shadow && (is_to_min || is_to_max)) {
                    to_min = this.convertToPercent(is_to_min ? o.to_min : o.min);
                    to_max = this.convertToPercent(is_to_max ? o.to_max : o.max) - to_min;
                    to_min = this.toFixed(to_min - (this.coords.p_handle / 100 * to_min));
                    to_max = this.toFixed(to_max - (this.coords.p_handle / 100 * to_max));
                    to_min = to_min + (this.coords.p_handle / 2);

                    c.shad_to[0].style.display = "block";
                    c.shad_to[0].style.left = to_min + "%";
                    c.shad_to[0].style.width = to_max + "%";
                } else {
                    c.shad_to[0].style.display = "none";
                }
            }
        },

/*

        // =============================================================================================================
        // Callbacks
*/
        callOnStart: function () {
            if (this.options.onStart && typeof this.options.onStart === "function") {
                this.options.onStart(this.result);
            }
        },
        callOnChange: function () {
            if (this.options.onChange && typeof this.options.onChange === "function") {
                this.options.onChange(this.result);
            }
        },
        callOnFinish: function () {
            if (this.options.onFinish && typeof this.options.onFinish === "function") {
                this.options.onFinish(this.result);
            }
        },
        callOnUpdate: function () {
            if (this.options.onUpdate && typeof this.options.onUpdate === "function") {
                this.options.onUpdate(this.result);
            }
        },


/*
        // =============================================================================================================
        // Service methods
*/
        toggleInput: function () {
            this.$cache.input.toggleClass("irs-hidden-input");
        },

        /**
         * Convert real value to percent
         *
         * @param value {Number} X in real
         * @param no_min {boolean=} don't use min value
         * @returns {Number} X in percent
         */
        convertToPercent: function (value, no_min) {
            var diapason = this.options.max - this.options.min,
                one_percent = diapason / 100,
                val, percent;

            if (!diapason) {
                this.no_diapason = true;
                return 0;
            }

            if (no_min) {
                val = value;
            } else {
                val = value - this.options.min;
            }

            percent = val / one_percent;

            return this.toFixed(percent);
        },

        /**
         * Convert percent to real values
         *
         * @param percent {Number} X in percent
         * @returns {Number} X in real
         */
        convertToValue: function (percent) {
            var min = this.options.min,
                max = this.options.max,
                min_decimals = min.toString().split(".")[1],
                max_decimals = max.toString().split(".")[1],
                min_length, max_length,
                avg_decimals = 0,
                abs = 0;

            if (percent === 0) {
                return this.options.min;
            }
            if (percent === 100) {
                return this.options.max;
            }


            if (min_decimals) {
                min_length = min_decimals.length;
                avg_decimals = min_length;
            }
            if (max_decimals) {
                max_length = max_decimals.length;
                avg_decimals = max_length;
            }
            if (min_length && max_length) {
                avg_decimals = (min_length >= max_length) ? min_length : max_length;
            }

            if (min < 0) {
                abs = Math.abs(min);
                min = +(min + abs).toFixed(avg_decimals);
                max = +(max + abs).toFixed(avg_decimals);
            }

            var number = ((max - min) / 100 * percent) + min,
                string = this.options.step.toString().split(".")[1],
                result;

            if (string) {
                number = +number.toFixed(string.length);
            } else {
                number = number / this.options.step;
                number = number * this.options.step;

                number = +number.toFixed(0);
            }

            if (abs) {
                number -= abs;
            }

            if (string) {
                result = +number.toFixed(string.length);
            } else {
                result = this.toFixed(number);
            }

            if (result < this.options.min) {
                result = this.options.min;
            } else if (result > this.options.max) {
                result = this.options.max;
            }

            return result;
        },

        /**
         * Round percent value with step
         *
         * @param percent {Number}
         * @returns percent {Number} rounded
         */
        calcWithStep: function (percent) {
            var rounded = Math.round(percent / this.coords.p_step) * this.coords.p_step;

            if (rounded > 100) {
                rounded = 100;
            }
            if (percent === 100) {
                rounded = 100;
            }

            return this.toFixed(rounded);
        },

        checkMinInterval: function (p_current, p_next, type) {
            var o = this.options,
                current,
                next;

            if (!o.min_interval) {
                return p_current;
            }

            current = this.convertToValue(p_current);
            next = this.convertToValue(p_next);

            if (type === "from") {

                if (next - current < o.min_interval) {
                    current = next - o.min_interval;
                }

            } else {

                if (current - next < o.min_interval) {
                    current = next + o.min_interval;
                }

            }

            return this.convertToPercent(current);
        },

        checkMaxInterval: function (p_current, p_next, type) {
            var o = this.options,
                current,
                next;

            if (!o.max_interval) {
                return p_current;
            }

            current = this.convertToValue(p_current);
            next = this.convertToValue(p_next);

            if (type === "from") {

                if (next - current > o.max_interval) {
                    current = next - o.max_interval;
                }

            } else {

                if (current - next > o.max_interval) {
                    current = next + o.max_interval;
                }

            }

            return this.convertToPercent(current);
        },

        checkDiapason: function (p_num, min, max) {
            var num = this.convertToValue(p_num),
                o = this.options;

            if (typeof min !== "number") {
                min = o.min;
            }

            if (typeof max !== "number") {
                max = o.max;
            }

            if (num < min) {
                num = min;
            }

            if (num > max) {
                num = max;
            }

            return this.convertToPercent(num);
        },

        toFixed: function (num) {
            num = num.toFixed(9);
            return +num;
        },

        _prettify: function (num) {
            if (!this.options.prettify_enabled) {
                return num;
            }

            if (this.options.prettify && typeof this.options.prettify === "function") {
                return this.options.prettify(num);
            } else {
                return this.prettify(num);
            }
        },

        prettify: function (num) {
            var n = num.toString();
            return n.replace(/(\d{1,3}(?=(?:\d\d\d)+(?!\d)))/g, "$1" + this.options.prettify_separator);
        },

        checkEdges: function (left, width) {
            if (!this.options.force_edges) {
                return this.toFixed(left);
            }

            if (left < 0) {
                left = 0;
            } else if (left > 100 - width) {
                left = 100 - width;
            }

            return this.toFixed(left);
        },

        validate: function () {
            var o = this.options,
                r = this.result,
                v = o.values,
                vl = v.length,
                value,
                i;

            if (typeof o.min === "string") o.min = +o.min;
            if (typeof o.max === "string") o.max = +o.max;
            if (typeof o.from === "string") o.from = +o.from;
            if (typeof o.to === "string") o.to = +o.to;
            if (typeof o.step === "string") o.step = +o.step;

            if (typeof o.from_min === "string") o.from_min = +o.from_min;
            if (typeof o.from_max === "string") o.from_max = +o.from_max;
            if (typeof o.to_min === "string") o.to_min = +o.to_min;
            if (typeof o.to_max === "string") o.to_max = +o.to_max;

            if (typeof o.keyboard_step === "string") o.keyboard_step = +o.keyboard_step;
            if (typeof o.grid_num === "string") o.grid_num = +o.grid_num;

            if (o.max < o.min) {
                o.max = o.min;
            }

            if (vl) {
                o.p_values = [];
                o.min = 0;
                o.max = vl - 1;
                o.step = 1;
                o.grid_num = o.max;
                o.grid_snap = true;


                for (i = 0; i < vl; i++) {
                    value = +v[i];

                    if (!isNaN(value)) {
                        v[i] = value;
                        value = this._prettify(value);
                    } else {
                        value = v[i];
                    }

                    o.p_values.push(value);
                }
            }

            if (typeof o.from !== "number" || isNaN(o.from)) {
                o.from = o.min;
            }

            if (typeof o.to !== "number" || isNaN(o.from)) {
                o.to = o.max;
            }

            if (o.type === "single") {

                if (o.from < o.min) {
                    o.from = o.min;
                }

                if (o.from > o.max) {
                    o.from = o.max;
                }

            } else {

                if (o.from < o.min || o.from > o.max) {
                    o.from = o.min;
                }
                if (o.to > o.max || o.to < o.min) {
                    o.to = o.max;
                }
                if (o.from > o.to) {
                    o.from = o.to;
                }

            }

            if (typeof o.step !== "number" || isNaN(o.step) || !o.step || o.step < 0) {
                o.step = 1;
            }

            if (typeof o.keyboard_step !== "number" || isNaN(o.keyboard_step) || !o.keyboard_step || o.keyboard_step < 0) {
                o.keyboard_step = 5;
            }

            if (typeof o.from_min === "number" && o.from < o.from_min) {
                o.from = o.from_min;
            }

            if (typeof o.from_max === "number" && o.from > o.from_max) {
                o.from = o.from_max;
            }

            if (typeof o.to_min === "number" && o.to < o.to_min) {
                o.to = o.to_min;
            }

            if (typeof o.to_max === "number" && o.from > o.to_max) {
                o.to = o.to_max;
            }

            if (r) {
                if (r.min !== o.min) {
                    r.min = o.min;
                }

                if (r.max !== o.max) {
                    r.max = o.max;
                }

                if (r.from < r.min || r.from > r.max) {
                    r.from = o.from;
                }

                if (r.to < r.min || r.to > r.max) {
                    r.to = o.to;
                }
            }

            if (typeof o.min_interval !== "number" || isNaN(o.min_interval) || !o.min_interval || o.min_interval < 0) {
                o.min_interval = 0;
            }

            if (typeof o.max_interval !== "number" || isNaN(o.max_interval) || !o.max_interval || o.max_interval < 0) {
                o.max_interval = 0;
            }

            if (o.min_interval && o.min_interval > o.max - o.min) {
                o.min_interval = o.max - o.min;
            }

            if (o.max_interval && o.max_interval > o.max - o.min) {
                o.max_interval = o.max - o.min;
            }
        },

        decorate: function (num, original) {
            var decorated = "",
                o = this.options;

            if (o.prefix) {
                decorated += o.prefix;
            }

            decorated += num;

            if (o.max_postfix) {
                if (o.values.length && num === o.p_values[o.max]) {
                    decorated += o.max_postfix;
                    if (o.postfix) {
                        decorated += " ";
                    }
                } else if (original === o.max) {
                    decorated += o.max_postfix;
                    if (o.postfix) {
                        decorated += " ";
                    }
                }
            }

            if (o.postfix) {
                decorated += o.postfix;
            }

            return decorated;
        },

        updateFrom: function () {
            this.result.from = this.options.from;
            this.result.from_percent = this.convertToPercent(this.result.from);
            if (this.options.values) {
                this.result.from_value = this.options.values[this.result.from];
            }
        },

        updateTo: function () {
            this.result.to = this.options.to;
            this.result.to_percent = this.convertToPercent(this.result.to);
            if (this.options.values) {
                this.result.to_value = this.options.values[this.result.to];
            }
        },

        updateResult: function () {
            this.result.min = this.options.min;
            this.result.max = this.options.max;
            this.updateFrom();
            this.updateTo();
        },

/*
        // =============================================================================================================
        // Grid
*/
        appendGrid: function () {
            if (!this.options.grid) {
                return;
            }

            var o = this.options,
                i, z,

                total = o.max - o.min,
                big_num = o.grid_num,
                big_p = 0,
                big_w = 0,

                small_max = 4,
                local_small_max,
                small_p,
                small_w = 0,

                result,
                html = '';



            this.calcGridMargin();

            if (o.grid_snap) {
                big_num = total / o.step;
                big_p = this.toFixed(o.step / (total / 100));
            } else {
                big_p = this.toFixed(100 / big_num);
            }

            if (big_num > 4) {
                small_max = 3;
            }
            if (big_num > 7) {
                small_max = 2;
            }
            if (big_num > 14) {
                small_max = 1;
            }
            if (big_num > 28) {
                small_max = 0;
            }

            for (i = 0; i < big_num + 1; i++) {
                local_small_max = small_max;

                big_w = this.toFixed(big_p * i);

                if (big_w > 100) {
                    big_w = 100;

                    local_small_max -= 2;
                    if (local_small_max < 0) {
                        local_small_max = 0;
                    }
                }
                this.coords.big[i] = big_w;

                small_p = (big_w - (big_p * (i - 1))) / (local_small_max + 1);

                for (z = 1; z <= local_small_max; z++) {
                    if (big_w === 0) {
                        break;
                    }

                    small_w = this.toFixed(big_w - (small_p * z));

                    html += '<span class="irs-grid-pol small" style="left: ' + small_w + '%"></span>';
                }

                html += '<span class="irs-grid-pol" style="left: ' + big_w + '%"></span>';

                result = this.convertToValue(big_w);
                if (o.values.length) {
                    result = o.p_values[result];
                } else {
                    result = this._prettify(result);
                }

                html += '<span class="irs-grid-text js-grid-text-' + i + '" style="left: ' + big_w + '%">' + result + '</span>';
            }
            this.coords.big_num = Math.ceil(big_num + 1);



            this.$cache.cont.addClass("irs-with-grid");
            this.$cache.grid.html(html);
            this.cacheGridLabels();
        },

        cacheGridLabels: function () {
            var $label, i,
                num = this.coords.big_num;

            for (i = 0; i < num; i++) {
                $label = this.$cache.grid.find(".js-grid-text-" + i);
                this.$cache.grid_labels.push($label);
            }

            this.calcGridLabels();
        },

        calcGridLabels: function () {
            var i, label, start = [], finish = [],
                num = this.coords.big_num;

            for (i = 0; i < num; i++) {
                this.coords.big_w[i] = this.$cache.grid_labels[i].outerWidth(false);
                this.coords.big_p[i] = this.toFixed(this.coords.big_w[i] / this.coords.w_rs * 100);
                this.coords.big_x[i] = this.toFixed(this.coords.big_p[i] / 2);

                start[i] = this.toFixed(this.coords.big[i] - this.coords.big_x[i]);
                finish[i] = this.toFixed(start[i] + this.coords.big_p[i]);
            }

            if (this.options.force_edges) {
                if (start[0] < -this.coords.grid_gap) {
                    start[0] = -this.coords.grid_gap;
                    finish[0] = this.toFixed(start[0] + this.coords.big_p[0]);

                    this.coords.big_x[0] = this.coords.grid_gap;
                }

                if (finish[num - 1] > 100 + this.coords.grid_gap) {
                    finish[num - 1] = 100 + this.coords.grid_gap;
                    start[num - 1] = this.toFixed(finish[num - 1] - this.coords.big_p[num - 1]);

                    this.coords.big_x[num - 1] = this.toFixed(this.coords.big_p[num - 1] - this.coords.grid_gap);
                }
            }

            this.calcGridCollision(2, start, finish);
            this.calcGridCollision(4, start, finish);

            for (i = 0; i < num; i++) {
                label = this.$cache.grid_labels[i][0];
                label.style.marginLeft = -this.coords.big_x[i] + "%";
            }
        },
/*
        // Collisions Calc Beta
        // TODO: Refactor then have plenty of time */
        calcGridCollision: function (step, start, finish) {
            var i, next_i, label,
                num = this.coords.big_num;

            for (i = 0; i < num; i += step) {
                next_i = i + (step / 2);
                if (next_i >= num) {
                    break;
                }

                label = this.$cache.grid_labels[next_i][0];

                if (finish[i] <= start[next_i]) {
                    label.style.visibility = "visible";
                } else {
                    label.style.visibility = "hidden";
                }
            }
        },

        calcGridMargin: function () {
            if (!this.options.grid_margin) {
                return;
            }

            this.coords.w_rs = this.$cache.rs.outerWidth(false);
            if (!this.coords.w_rs) {
                return;
            }

            if (this.options.type === "single") {
                this.coords.w_handle = this.$cache.s_single.outerWidth(false);
            } else {
                this.coords.w_handle = this.$cache.s_from.outerWidth(false);
            }
            this.coords.p_handle = this.toFixed(this.coords.w_handle  / this.coords.w_rs * 100);
            this.coords.grid_gap = this.toFixed((this.coords.p_handle / 2) - 0.1);

            this.$cache.grid[0].style.width = this.toFixed(100 - this.coords.p_handle) + "%";
            this.$cache.grid[0].style.left = this.coords.grid_gap + "%";
        },


/*
        // =============================================================================================================
        // Public methods
*/
        update: function (options) {
            if (!this.input) {
                return;
            }

            this.is_update = true;

            this.options.from = this.result.from;
            this.options.to = this.result.to;

            this.options = $.extend(this.options, options);
            this.validate();
            this.updateResult(options);

            this.toggleInput();
            this.remove();
            this.init(true);
        },

        reset: function () {
            if (!this.input) {
                return;
            }

            this.updateResult();
            this.update();
        },

        destroy: function () {
            if (!this.input) {
                return;
            }

            this.toggleInput();
            this.$cache.input.prop("readonly", false);
            $.data(this.input, "ionRangeSlider", null);

            this.remove();
            this.input = null;
            this.options = null;
        }
    };

    $.fn.ionRangeSlider = function (options) {
        return this.each(function() {
            if (!$.data(this, "ionRangeSlider")) {
                $.data(this, "ionRangeSlider", new IonRangeSlider(this, options, plugin_count++));
            }
        });
    };

/*

    // =================================================================================================================
    // http://paulirish.com/2011/requestanimationframe-for-smart-animating/
    // http://my.opera.com/emoller/blog/2011/12/20/requestanimationframe-for-smart-er-animating

    // requestAnimationFrame polyfill by Erik Möller. fixes from Paul Irish and Tino Zijdel

    // MIT license
*/
    (function() {
        var lastTime = 0;
        var vendors = ['ms', 'moz', 'webkit', 'o'];
        for(var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
            window.requestAnimationFrame = window[vendors[x]+'RequestAnimationFrame'];
            window.cancelAnimationFrame = window[vendors[x]+'CancelAnimationFrame']
                || window[vendors[x]+'CancelRequestAnimationFrame'];
        }

        if (!window.requestAnimationFrame)
            window.requestAnimationFrame = function(callback, element) {
                var currTime = new Date().getTime();
                var timeToCall = Math.max(0, 16 - (currTime - lastTime));
                var id = window.setTimeout(function() { callback(currTime + timeToCall); },
                    timeToCall);
                lastTime = currTime + timeToCall;
                return id;
            };

        if (!window.cancelAnimationFrame)
            window.cancelAnimationFrame = function(id) {
                clearTimeout(id);
            };
    }());

}));
$(document).ready(function() {
    $(document).on('click', '#accordian li span.acc-trigger', function() {
        var link = $(this);
        var closest_ul = link.siblings("ul");

        if (link.hasClass("is--active")) {
            closest_ul.slideUp();
            link.removeClass("is--active");
        } else {
            closest_ul.slideDown();
            link.addClass("is--active");
        }
    });

    /* for left filters  */
    $('.link__filter').click(function() {
        $(this).toggleClass("active");
        var el = $("body");
        if (el.hasClass('filter__show')) el.removeClass("filter__show");
        else el.addClass('filter__show');
        return false;
    });

    $('body').click(function() {
        if ($('body').hasClass('filter__show')) {
            $('.link__filter').removeClass("active");
            $('body').removeClass('filter__show');
        }
    });

    $('.filter__overlay').click(function() {
        if ($('body').hasClass('filter__show')) {
            $('.link__filter').removeClass("active");
            $('body').removeClass('filter__show');
        }
    });

    $('.productFilters-js').click(function(e) {
        e.stopPropagation();
    });

    /* for mobile toggle */
    if ($(window).width() < 1025) {
        $(".productFilters-js").on("click", ".filter-head-js", function() {
            if ($(this).hasClass('active')) {
                $(this).removeClass('active');
                $(this).next().slideUp();
            } else {
                $(this).addClass('active');
                $(this).next().slideDown();
            }
        });
    }
});
/*
     _ _      _       _
 ___| (_) ___| | __  (_)___
/ __| | |/ __| |/ /  | / __|
\__ \ | | (__|   < _ | \__ \
|___/_|_|\___|_|\_(_)/ |___/
                   |__/

 Version: 1.5.9
  Author: Ken Wheeler
 Website: http://kenwheeler.github.io
    Docs: http://kenwheeler.github.io/slick
    Repo: http://github.com/kenwheeler/slick
  Issues: http://github.com/kenwheeler/slick/issues

 */
!function(a){"use strict";"function"==typeof define&&define.amd?define(["jquery"],a):"undefined"!=typeof exports?module.exports=a(require("jquery")):a(jQuery)}(function(a){"use strict";var b=window.Slick||{};b=function(){function c(c,d){var f,e=this;e.defaults={accessibility:!0,adaptiveHeight:!1,appendArrows:a(c),appendDots:a(c),arrows:!0,asNavFor:null,prevArrow:'<button type="button" data-role="none" class="slick-prev" aria-label="Previous" tabindex="0" role="button">Previous</button>',nextArrow:'<button type="button" data-role="none" class="slick-next" aria-label="Next" tabindex="0" role="button">Next</button>',autoplay:!1,autoplaySpeed:3e3,centerMode:!1,centerPadding:"50px",cssEase:"ease",customPaging:function(a,b){return'<button type="button" data-role="none" role="button" aria-required="false" tabindex="0">'+(b+1)+"</button>"},dots:!1,dotsClass:"slick-dots",draggable:!0,easing:"linear",edgeFriction:.35,fade:!1,focusOnSelect:!1,infinite:!0,initialSlide:0,lazyLoad:"ondemand",mobileFirst:!1,pauseOnHover:!0,pauseOnDotsHover:!1,respondTo:"window",responsive:null,rows:1,rtl:!1,slide:"",slidesPerRow:1,slidesToShow:1,slidesToScroll:1,speed:500,swipe:!0,swipeToSlide:!1,touchMove:!0,touchThreshold:5,useCSS:!0,useTransform:!1,variableWidth:!1,vertical:!1,verticalSwiping:!1,waitForAnimate:!0,zIndex:1e3},e.initials={animating:!1,dragging:!1,autoPlayTimer:null,currentDirection:0,currentLeft:null,currentSlide:0,direction:1,$dots:null,listWidth:null,listHeight:null,loadIndex:0,$nextArrow:null,$prevArrow:null,slideCount:null,slideWidth:null,$slideTrack:null,$slides:null,sliding:!1,slideOffset:0,swipeLeft:null,$list:null,touchObject:{},transformsEnabled:!1,unslicked:!1},a.extend(e,e.initials),e.activeBreakpoint=null,e.animType=null,e.animProp=null,e.breakpoints=[],e.breakpointSettings=[],e.cssTransitions=!1,e.hidden="hidden",e.paused=!1,e.positionProp=null,e.respondTo=null,e.rowCount=1,e.shouldClick=!0,e.$slider=a(c),e.$slidesCache=null,e.transformType=null,e.transitionType=null,e.visibilityChange="visibilitychange",e.windowWidth=0,e.windowTimer=null,f=a(c).data("slick")||{},e.options=a.extend({},e.defaults,f,d),e.currentSlide=e.options.initialSlide,e.originalSettings=e.options,"undefined"!=typeof document.mozHidden?(e.hidden="mozHidden",e.visibilityChange="mozvisibilitychange"):"undefined"!=typeof document.webkitHidden&&(e.hidden="webkitHidden",e.visibilityChange="webkitvisibilitychange"),e.autoPlay=a.proxy(e.autoPlay,e),e.autoPlayClear=a.proxy(e.autoPlayClear,e),e.changeSlide=a.proxy(e.changeSlide,e),e.clickHandler=a.proxy(e.clickHandler,e),e.selectHandler=a.proxy(e.selectHandler,e),e.setPosition=a.proxy(e.setPosition,e),e.swipeHandler=a.proxy(e.swipeHandler,e),e.dragHandler=a.proxy(e.dragHandler,e),e.keyHandler=a.proxy(e.keyHandler,e),e.autoPlayIterator=a.proxy(e.autoPlayIterator,e),e.instanceUid=b++,e.htmlExpr=/^(?:\s*(<[\w\W]+>)[^>]*)$/,e.registerBreakpoints(),e.init(!0),e.checkResponsive(!0)}var b=0;return c}(),b.prototype.addSlide=b.prototype.slickAdd=function(b,c,d){var e=this;if("boolean"==typeof c)d=c,c=null;else if(0>c||c>=e.slideCount)return!1;e.unload(),"number"==typeof c?0===c&&0===e.$slides.length?a(b).appendTo(e.$slideTrack):d?a(b).insertBefore(e.$slides.eq(c)):a(b).insertAfter(e.$slides.eq(c)):d===!0?a(b).prependTo(e.$slideTrack):a(b).appendTo(e.$slideTrack),e.$slides=e.$slideTrack.children(this.options.slide),e.$slideTrack.children(this.options.slide).detach(),e.$slideTrack.append(e.$slides),e.$slides.each(function(b,c){a(c).attr("data-slick-index",b)}),e.$slidesCache=e.$slides,e.reinit()},b.prototype.animateHeight=function(){var a=this;if(1===a.options.slidesToShow&&a.options.adaptiveHeight===!0&&a.options.vertical===!1){var b=a.$slides.eq(a.currentSlide).outerHeight(!0);a.$list.animate({height:b},a.options.speed)}},b.prototype.animateSlide=function(b,c){var d={},e=this;e.animateHeight(),e.options.rtl===!0&&e.options.vertical===!1&&(b=-b),e.transformsEnabled===!1?e.options.vertical===!1?e.$slideTrack.animate({left:b},e.options.speed,e.options.easing,c):e.$slideTrack.animate({top:b},e.options.speed,e.options.easing,c):e.cssTransitions===!1?(e.options.rtl===!0&&(e.currentLeft=-e.currentLeft),a({animStart:e.currentLeft}).animate({animStart:b},{duration:e.options.speed,easing:e.options.easing,step:function(a){a=Math.ceil(a),e.options.vertical===!1?(d[e.animType]="translate("+a+"px, 0px)",e.$slideTrack.css(d)):(d[e.animType]="translate(0px,"+a+"px)",e.$slideTrack.css(d))},complete:function(){c&&c.call()}})):(e.applyTransition(),b=Math.ceil(b),e.options.vertical===!1?d[e.animType]="translate3d("+b+"px, 0px, 0px)":d[e.animType]="translate3d(0px,"+b+"px, 0px)",e.$slideTrack.css(d),c&&setTimeout(function(){e.disableTransition(),c.call()},e.options.speed))},b.prototype.asNavFor=function(b){var c=this,d=c.options.asNavFor;d&&null!==d&&(d=a(d).not(c.$slider)),null!==d&&"object"==typeof d&&d.each(function(){var c=a(this).slick("getSlick");c.unslicked||c.slideHandler(b,!0)})},b.prototype.applyTransition=function(a){var b=this,c={};b.options.fade===!1?c[b.transitionType]=b.transformType+" "+b.options.speed+"ms "+b.options.cssEase:c[b.transitionType]="opacity "+b.options.speed+"ms "+b.options.cssEase,b.options.fade===!1?b.$slideTrack.css(c):b.$slides.eq(a).css(c)},b.prototype.autoPlay=function(){var a=this;a.autoPlayTimer&&clearInterval(a.autoPlayTimer),a.slideCount>a.options.slidesToShow&&a.paused!==!0&&(a.autoPlayTimer=setInterval(a.autoPlayIterator,a.options.autoplaySpeed))},b.prototype.autoPlayClear=function(){var a=this;a.autoPlayTimer&&clearInterval(a.autoPlayTimer)},b.prototype.autoPlayIterator=function(){var a=this;a.options.infinite===!1?1===a.direction?(a.currentSlide+1===a.slideCount-1&&(a.direction=0),a.slideHandler(a.currentSlide+a.options.slidesToScroll)):(a.currentSlide-1===0&&(a.direction=1),a.slideHandler(a.currentSlide-a.options.slidesToScroll)):a.slideHandler(a.currentSlide+a.options.slidesToScroll)},b.prototype.buildArrows=function(){var b=this;b.options.arrows===!0&&(b.$prevArrow=a(b.options.prevArrow).addClass("slick-arrow"),b.$nextArrow=a(b.options.nextArrow).addClass("slick-arrow"),b.slideCount>b.options.slidesToShow?(b.$prevArrow.removeClass("slick-hidden").removeAttr("aria-hidden tabindex"),b.$nextArrow.removeClass("slick-hidden").removeAttr("aria-hidden tabindex"),b.htmlExpr.test(b.options.prevArrow)&&b.$prevArrow.prependTo(b.options.appendArrows),b.htmlExpr.test(b.options.nextArrow)&&b.$nextArrow.appendTo(b.options.appendArrows),b.options.infinite!==!0&&b.$prevArrow.addClass("slick-disabled").attr("aria-disabled","true")):b.$prevArrow.add(b.$nextArrow).addClass("slick-hidden").attr({"aria-disabled":"true",tabindex:"-1"}))},b.prototype.buildDots=function(){var c,d,b=this;if(b.options.dots===!0&&b.slideCount>b.options.slidesToShow){for(d='<ul class="'+b.options.dotsClass+'">',c=0;c<=b.getDotCount();c+=1)d+="<li>"+b.options.customPaging.call(this,b,c)+"</li>";d+="</ul>",b.$dots=a(d).appendTo(b.options.appendDots),b.$dots.find("li").first().addClass("slick-active").attr("aria-hidden","false")}},b.prototype.buildOut=function(){var b=this;b.$slides=b.$slider.children(b.options.slide+":not(.slick-cloned)").addClass("slick-slide"),b.slideCount=b.$slides.length,b.$slides.each(function(b,c){a(c).attr("data-slick-index",b).data("originalStyling",a(c).attr("style")||"")}),b.$slider.addClass("slick-slider"),b.$slideTrack=0===b.slideCount?a('<div class="slick-track"/>').appendTo(b.$slider):b.$slides.wrapAll('<div class="slick-track"/>').parent(),b.$list=b.$slideTrack.wrap('<div aria-live="polite" class="slick-list"/>').parent(),b.$slideTrack.css("opacity",0),(b.options.centerMode===!0||b.options.swipeToSlide===!0)&&(b.options.slidesToScroll=1),a("img[data-lazy]",b.$slider).not("[src]").addClass("slick-loading"),b.setupInfinite(),b.buildArrows(),b.buildDots(),b.updateDots(),b.setSlideClasses("number"==typeof b.currentSlide?b.currentSlide:0),b.options.draggable===!0&&b.$list.addClass("draggable")},b.prototype.buildRows=function(){var b,c,d,e,f,g,h,a=this;if(e=document.createDocumentFragment(),g=a.$slider.children(),a.options.rows>1){for(h=a.options.slidesPerRow*a.options.rows,f=Math.ceil(g.length/h),b=0;f>b;b++){var i=document.createElement("div");for(c=0;c<a.options.rows;c++){var j=document.createElement("div");for(d=0;d<a.options.slidesPerRow;d++){var k=b*h+(c*a.options.slidesPerRow+d);g.get(k)&&j.appendChild(g.get(k))}i.appendChild(j)}e.appendChild(i)}a.$slider.html(e),a.$slider.children().children().children().css({width:100/a.options.slidesPerRow+"%",display:"inline-block"})}},b.prototype.checkResponsive=function(b,c){var e,f,g,d=this,h=!1,i=d.$slider.width(),j=window.innerWidth||a(window).width();if("window"===d.respondTo?g=j:"slider"===d.respondTo?g=i:"min"===d.respondTo&&(g=Math.min(j,i)),d.options.responsive&&d.options.responsive.length&&null!==d.options.responsive){f=null;for(e in d.breakpoints)d.breakpoints.hasOwnProperty(e)&&(d.originalSettings.mobileFirst===!1?g<d.breakpoints[e]&&(f=d.breakpoints[e]):g>d.breakpoints[e]&&(f=d.breakpoints[e]));null!==f?null!==d.activeBreakpoint?(f!==d.activeBreakpoint||c)&&(d.activeBreakpoint=f,"unslick"===d.breakpointSettings[f]?d.unslick(f):(d.options=a.extend({},d.originalSettings,d.breakpointSettings[f]),b===!0&&(d.currentSlide=d.options.initialSlide),d.refresh(b)),h=f):(d.activeBreakpoint=f,"unslick"===d.breakpointSettings[f]?d.unslick(f):(d.options=a.extend({},d.originalSettings,d.breakpointSettings[f]),b===!0&&(d.currentSlide=d.options.initialSlide),d.refresh(b)),h=f):null!==d.activeBreakpoint&&(d.activeBreakpoint=null,d.options=d.originalSettings,b===!0&&(d.currentSlide=d.options.initialSlide),d.refresh(b),h=f),b||h===!1||d.$slider.trigger("breakpoint",[d,h])}},b.prototype.changeSlide=function(b,c){var f,g,h,d=this,e=a(b.target);switch(e.is("a")&&b.preventDefault(),e.is("li")||(e=e.closest("li")),h=d.slideCount%d.options.slidesToScroll!==0,f=h?0:(d.slideCount-d.currentSlide)%d.options.slidesToScroll,b.data.message){case"previous":g=0===f?d.options.slidesToScroll:d.options.slidesToShow-f,d.slideCount>d.options.slidesToShow&&d.slideHandler(d.currentSlide-g,!1,c);break;case"next":g=0===f?d.options.slidesToScroll:f,d.slideCount>d.options.slidesToShow&&d.slideHandler(d.currentSlide+g,!1,c);break;case"index":var i=0===b.data.index?0:b.data.index||e.index()*d.options.slidesToScroll;d.slideHandler(d.checkNavigable(i),!1,c),e.children().trigger("focus");break;default:return}},b.prototype.checkNavigable=function(a){var c,d,b=this;if(c=b.getNavigableIndexes(),d=0,a>c[c.length-1])a=c[c.length-1];else for(var e in c){if(a<c[e]){a=d;break}d=c[e]}return a},b.prototype.cleanUpEvents=function(){var b=this;b.options.dots&&null!==b.$dots&&(a("li",b.$dots).off("click.slick",b.changeSlide),b.options.pauseOnDotsHover===!0&&b.options.autoplay===!0&&a("li",b.$dots).off("mouseenter.slick",a.proxy(b.setPaused,b,!0)).off("mouseleave.slick",a.proxy(b.setPaused,b,!1))),b.options.arrows===!0&&b.slideCount>b.options.slidesToShow&&(b.$prevArrow&&b.$prevArrow.off("click.slick",b.changeSlide),b.$nextArrow&&b.$nextArrow.off("click.slick",b.changeSlide)),b.$list.off("touchstart.slick mousedown.slick",b.swipeHandler),b.$list.off("touchmove.slick mousemove.slick",b.swipeHandler),b.$list.off("touchend.slick mouseup.slick",b.swipeHandler),b.$list.off("touchcancel.slick mouseleave.slick",b.swipeHandler),b.$list.off("click.slick",b.clickHandler),a(document).off(b.visibilityChange,b.visibility),b.$list.off("mouseenter.slick",a.proxy(b.setPaused,b,!0)),b.$list.off("mouseleave.slick",a.proxy(b.setPaused,b,!1)),b.options.accessibility===!0&&b.$list.off("keydown.slick",b.keyHandler),b.options.focusOnSelect===!0&&a(b.$slideTrack).children().off("click.slick",b.selectHandler),a(window).off("orientationchange.slick.slick-"+b.instanceUid,b.orientationChange),a(window).off("resize.slick.slick-"+b.instanceUid,b.resize),a("[draggable!=true]",b.$slideTrack).off("dragstart",b.preventDefault),a(window).off("load.slick.slick-"+b.instanceUid,b.setPosition),a(document).off("ready.slick.slick-"+b.instanceUid,b.setPosition)},b.prototype.cleanUpRows=function(){var b,a=this;a.options.rows>1&&(b=a.$slides.children().children(),b.removeAttr("style"),a.$slider.html(b))},b.prototype.clickHandler=function(a){var b=this;b.shouldClick===!1&&(a.stopImmediatePropagation(),a.stopPropagation(),a.preventDefault())},b.prototype.destroy=function(b){var c=this;c.autoPlayClear(),c.touchObject={},c.cleanUpEvents(),a(".slick-cloned",c.$slider).detach(),c.$dots&&c.$dots.remove(),c.$prevArrow&&c.$prevArrow.length&&(c.$prevArrow.removeClass("slick-disabled slick-arrow slick-hidden").removeAttr("aria-hidden aria-disabled tabindex").css("display",""),c.htmlExpr.test(c.options.prevArrow)&&c.$prevArrow.remove()),c.$nextArrow&&c.$nextArrow.length&&(c.$nextArrow.removeClass("slick-disabled slick-arrow slick-hidden").removeAttr("aria-hidden aria-disabled tabindex").css("display",""),c.htmlExpr.test(c.options.nextArrow)&&c.$nextArrow.remove()),c.$slides&&(c.$slides.removeClass("slick-slide slick-active slick-center slick-visible slick-current").removeAttr("aria-hidden").removeAttr("data-slick-index").each(function(){a(this).attr("style",a(this).data("originalStyling"))}),c.$slideTrack.children(this.options.slide).detach(),c.$slideTrack.detach(),c.$list.detach(),c.$slider.append(c.$slides)),c.cleanUpRows(),c.$slider.removeClass("slick-slider"),c.$slider.removeClass("slick-initialized"),c.unslicked=!0,b||c.$slider.trigger("destroy",[c])},b.prototype.disableTransition=function(a){var b=this,c={};c[b.transitionType]="",b.options.fade===!1?b.$slideTrack.css(c):b.$slides.eq(a).css(c)},b.prototype.fadeSlide=function(a,b){var c=this;c.cssTransitions===!1?(c.$slides.eq(a).css({zIndex:c.options.zIndex}),c.$slides.eq(a).animate({opacity:1},c.options.speed,c.options.easing,b)):(c.applyTransition(a),c.$slides.eq(a).css({opacity:1,zIndex:c.options.zIndex}),b&&setTimeout(function(){c.disableTransition(a),b.call()},c.options.speed))},b.prototype.fadeSlideOut=function(a){var b=this;b.cssTransitions===!1?b.$slides.eq(a).animate({opacity:0,zIndex:b.options.zIndex-2},b.options.speed,b.options.easing):(b.applyTransition(a),b.$slides.eq(a).css({opacity:0,zIndex:b.options.zIndex-2}))},b.prototype.filterSlides=b.prototype.slickFilter=function(a){var b=this;null!==a&&(b.$slidesCache=b.$slides,b.unload(),b.$slideTrack.children(this.options.slide).detach(),b.$slidesCache.filter(a).appendTo(b.$slideTrack),b.reinit())},b.prototype.getCurrent=b.prototype.slickCurrentSlide=function(){var a=this;return a.currentSlide},b.prototype.getDotCount=function(){var a=this,b=0,c=0,d=0;if(a.options.infinite===!0)for(;b<a.slideCount;)++d,b=c+a.options.slidesToScroll,c+=a.options.slidesToScroll<=a.options.slidesToShow?a.options.slidesToScroll:a.options.slidesToShow;else if(a.options.centerMode===!0)d=a.slideCount;else for(;b<a.slideCount;)++d,b=c+a.options.slidesToScroll,c+=a.options.slidesToScroll<=a.options.slidesToShow?a.options.slidesToScroll:a.options.slidesToShow;return d-1},b.prototype.getLeft=function(a){var c,d,f,b=this,e=0;return b.slideOffset=0,d=b.$slides.first().outerHeight(!0),b.options.infinite===!0?(b.slideCount>b.options.slidesToShow&&(b.slideOffset=b.slideWidth*b.options.slidesToShow*-1,e=d*b.options.slidesToShow*-1),b.slideCount%b.options.slidesToScroll!==0&&a+b.options.slidesToScroll>b.slideCount&&b.slideCount>b.options.slidesToShow&&(a>b.slideCount?(b.slideOffset=(b.options.slidesToShow-(a-b.slideCount))*b.slideWidth*-1,e=(b.options.slidesToShow-(a-b.slideCount))*d*-1):(b.slideOffset=b.slideCount%b.options.slidesToScroll*b.slideWidth*-1,e=b.slideCount%b.options.slidesToScroll*d*-1))):a+b.options.slidesToShow>b.slideCount&&(b.slideOffset=(a+b.options.slidesToShow-b.slideCount)*b.slideWidth,e=(a+b.options.slidesToShow-b.slideCount)*d),b.slideCount<=b.options.slidesToShow&&(b.slideOffset=0,e=0),b.options.centerMode===!0&&b.options.infinite===!0?b.slideOffset+=b.slideWidth*Math.floor(b.options.slidesToShow/2)-b.slideWidth:b.options.centerMode===!0&&(b.slideOffset=0,b.slideOffset+=b.slideWidth*Math.floor(b.options.slidesToShow/2)),c=b.options.vertical===!1?a*b.slideWidth*-1+b.slideOffset:a*d*-1+e,b.options.variableWidth===!0&&(f=b.slideCount<=b.options.slidesToShow||b.options.infinite===!1?b.$slideTrack.children(".slick-slide").eq(a):b.$slideTrack.children(".slick-slide").eq(a+b.options.slidesToShow),c=b.options.rtl===!0?f[0]?-1*(b.$slideTrack.width()-f[0].offsetLeft-f.width()):0:f[0]?-1*f[0].offsetLeft:0,b.options.centerMode===!0&&(f=b.slideCount<=b.options.slidesToShow||b.options.infinite===!1?b.$slideTrack.children(".slick-slide").eq(a):b.$slideTrack.children(".slick-slide").eq(a+b.options.slidesToShow+1),c=b.options.rtl===!0?f[0]?-1*(b.$slideTrack.width()-f[0].offsetLeft-f.width()):0:f[0]?-1*f[0].offsetLeft:0,c+=(b.$list.width()-f.outerWidth())/2)),c},b.prototype.getOption=b.prototype.slickGetOption=function(a){var b=this;return b.options[a]},b.prototype.getNavigableIndexes=function(){var e,a=this,b=0,c=0,d=[];for(a.options.infinite===!1?e=a.slideCount:(b=-1*a.options.slidesToScroll,c=-1*a.options.slidesToScroll,e=2*a.slideCount);e>b;)d.push(b),b=c+a.options.slidesToScroll,c+=a.options.slidesToScroll<=a.options.slidesToShow?a.options.slidesToScroll:a.options.slidesToShow;return d},b.prototype.getSlick=function(){return this},b.prototype.getSlideCount=function(){var c,d,e,b=this;return e=b.options.centerMode===!0?b.slideWidth*Math.floor(b.options.slidesToShow/2):0,b.options.swipeToSlide===!0?(b.$slideTrack.find(".slick-slide").each(function(c,f){return f.offsetLeft-e+a(f).outerWidth()/2>-1*b.swipeLeft?(d=f,!1):void 0}),c=Math.abs(a(d).attr("data-slick-index")-b.currentSlide)||1):b.options.slidesToScroll},b.prototype.goTo=b.prototype.slickGoTo=function(a,b){var c=this;c.changeSlide({data:{message:"index",index:parseInt(a)}},b)},b.prototype.init=function(b){var c=this;a(c.$slider).hasClass("slick-initialized")||(a(c.$slider).addClass("slick-initialized"),c.buildRows(),c.buildOut(),c.setProps(),c.startLoad(),c.loadSlider(),c.initializeEvents(),c.updateArrows(),c.updateDots()),b&&c.$slider.trigger("init",[c]),c.options.accessibility===!0&&c.initADA()},b.prototype.initArrowEvents=function(){var a=this;a.options.arrows===!0&&a.slideCount>a.options.slidesToShow&&(a.$prevArrow.on("click.slick",{message:"previous"},a.changeSlide),a.$nextArrow.on("click.slick",{message:"next"},a.changeSlide))},b.prototype.initDotEvents=function(){var b=this;b.options.dots===!0&&b.slideCount>b.options.slidesToShow&&a("li",b.$dots).on("click.slick",{message:"index"},b.changeSlide),b.options.dots===!0&&b.options.pauseOnDotsHover===!0&&b.options.autoplay===!0&&a("li",b.$dots).on("mouseenter.slick",a.proxy(b.setPaused,b,!0)).on("mouseleave.slick",a.proxy(b.setPaused,b,!1))},b.prototype.initializeEvents=function(){var b=this;b.initArrowEvents(),b.initDotEvents(),b.$list.on("touchstart.slick mousedown.slick",{action:"start"},b.swipeHandler),b.$list.on("touchmove.slick mousemove.slick",{action:"move"},b.swipeHandler),b.$list.on("touchend.slick mouseup.slick",{action:"end"},b.swipeHandler),b.$list.on("touchcancel.slick mouseleave.slick",{action:"end"},b.swipeHandler),b.$list.on("click.slick",b.clickHandler),a(document).on(b.visibilityChange,a.proxy(b.visibility,b)),b.$list.on("mouseenter.slick",a.proxy(b.setPaused,b,!0)),b.$list.on("mouseleave.slick",a.proxy(b.setPaused,b,!1)),b.options.accessibility===!0&&b.$list.on("keydown.slick",b.keyHandler),b.options.focusOnSelect===!0&&a(b.$slideTrack).children().on("click.slick",b.selectHandler),a(window).on("orientationchange.slick.slick-"+b.instanceUid,a.proxy(b.orientationChange,b)),a(window).on("resize.slick.slick-"+b.instanceUid,a.proxy(b.resize,b)),a("[draggable!=true]",b.$slideTrack).on("dragstart",b.preventDefault),a(window).on("load.slick.slick-"+b.instanceUid,b.setPosition),a(document).on("ready.slick.slick-"+b.instanceUid,b.setPosition)},b.prototype.initUI=function(){var a=this;a.options.arrows===!0&&a.slideCount>a.options.slidesToShow&&(a.$prevArrow.show(),a.$nextArrow.show()),a.options.dots===!0&&a.slideCount>a.options.slidesToShow&&a.$dots.show(),a.options.autoplay===!0&&a.autoPlay()},b.prototype.keyHandler=function(a){var b=this;a.target.tagName.match("TEXTAREA|INPUT|SELECT")||(37===a.keyCode&&b.options.accessibility===!0?b.changeSlide({data:{message:"previous"}}):39===a.keyCode&&b.options.accessibility===!0&&b.changeSlide({data:{message:"next"}}))},b.prototype.lazyLoad=function(){function g(b){a("img[data-lazy]",b).each(function(){var b=a(this),c=a(this).attr("data-lazy"),d=document.createElement("img");d.onload=function(){b.animate({opacity:0},100,function(){b.attr("src",c).animate({opacity:1},200,function(){b.removeAttr("data-lazy").removeClass("slick-loading")})})},d.src=c})}var c,d,e,f,b=this;b.options.centerMode===!0?b.options.infinite===!0?(e=b.currentSlide+(b.options.slidesToShow/2+1),f=e+b.options.slidesToShow+2):(e=Math.max(0,b.currentSlide-(b.options.slidesToShow/2+1)),f=2+(b.options.slidesToShow/2+1)+b.currentSlide):(e=b.options.infinite?b.options.slidesToShow+b.currentSlide:b.currentSlide,f=e+b.options.slidesToShow,b.options.fade===!0&&(e>0&&e--,f<=b.slideCount&&f++)),c=b.$slider.find(".slick-slide").slice(e,f),g(c),b.slideCount<=b.options.slidesToShow?(d=b.$slider.find(".slick-slide"),g(d)):b.currentSlide>=b.slideCount-b.options.slidesToShow?(d=b.$slider.find(".slick-cloned").slice(0,b.options.slidesToShow),g(d)):0===b.currentSlide&&(d=b.$slider.find(".slick-cloned").slice(-1*b.options.slidesToShow),g(d))},b.prototype.loadSlider=function(){var a=this;a.setPosition(),a.$slideTrack.css({opacity:1}),a.$slider.removeClass("slick-loading"),a.initUI(),"progressive"===a.options.lazyLoad&&a.progressiveLazyLoad()},b.prototype.next=b.prototype.slickNext=function(){var a=this;a.changeSlide({data:{message:"next"}})},b.prototype.orientationChange=function(){var a=this;a.checkResponsive(),a.setPosition()},b.prototype.pause=b.prototype.slickPause=function(){var a=this;a.autoPlayClear(),a.paused=!0},b.prototype.play=b.prototype.slickPlay=function(){var a=this;a.paused=!1,a.autoPlay()},b.prototype.postSlide=function(a){var b=this;b.$slider.trigger("afterChange",[b,a]),b.animating=!1,b.setPosition(),b.swipeLeft=null,b.options.autoplay===!0&&b.paused===!1&&b.autoPlay(),b.options.accessibility===!0&&b.initADA()},b.prototype.prev=b.prototype.slickPrev=function(){var a=this;a.changeSlide({data:{message:"previous"}})},b.prototype.preventDefault=function(a){a.preventDefault()},b.prototype.progressiveLazyLoad=function(){var c,d,b=this;c=a("img[data-lazy]",b.$slider).length,c>0&&(d=a("img[data-lazy]",b.$slider).first(),d.attr("src",null),d.attr("src",d.attr("data-lazy")).removeClass("slick-loading").load(function(){d.removeAttr("data-lazy"),b.progressiveLazyLoad(),b.options.adaptiveHeight===!0&&b.setPosition()}).error(function(){d.removeAttr("data-lazy"),b.progressiveLazyLoad()}))},b.prototype.refresh=function(b){var d,e,c=this;e=c.slideCount-c.options.slidesToShow,c.options.infinite||(c.slideCount<=c.options.slidesToShow?c.currentSlide=0:c.currentSlide>e&&(c.currentSlide=e)),d=c.currentSlide,c.destroy(!0),a.extend(c,c.initials,{currentSlide:d}),c.init(),b||c.changeSlide({data:{message:"index",index:d}},!1)},b.prototype.registerBreakpoints=function(){var c,d,e,b=this,f=b.options.responsive||null;if("array"===a.type(f)&&f.length){b.respondTo=b.options.respondTo||"window";for(c in f)if(e=b.breakpoints.length-1,d=f[c].breakpoint,f.hasOwnProperty(c)){for(;e>=0;)b.breakpoints[e]&&b.breakpoints[e]===d&&b.breakpoints.splice(e,1),e--;b.breakpoints.push(d),b.breakpointSettings[d]=f[c].settings}b.breakpoints.sort(function(a,c){return b.options.mobileFirst?a-c:c-a})}},b.prototype.reinit=function(){var b=this;b.$slides=b.$slideTrack.children(b.options.slide).addClass("slick-slide"),b.slideCount=b.$slides.length,b.currentSlide>=b.slideCount&&0!==b.currentSlide&&(b.currentSlide=b.currentSlide-b.options.slidesToScroll),b.slideCount<=b.options.slidesToShow&&(b.currentSlide=0),b.registerBreakpoints(),b.setProps(),b.setupInfinite(),b.buildArrows(),b.updateArrows(),b.initArrowEvents(),b.buildDots(),b.updateDots(),b.initDotEvents(),b.checkResponsive(!1,!0),b.options.focusOnSelect===!0&&a(b.$slideTrack).children().on("click.slick",b.selectHandler),b.setSlideClasses(0),b.setPosition(),b.$slider.trigger("reInit",[b]),b.options.autoplay===!0&&b.focusHandler()},b.prototype.resize=function(){var b=this;a(window).width()!==b.windowWidth&&(clearTimeout(b.windowDelay),b.windowDelay=window.setTimeout(function(){b.windowWidth=a(window).width(),b.checkResponsive(),b.unslicked||b.setPosition()},50))},b.prototype.removeSlide=b.prototype.slickRemove=function(a,b,c){var d=this;return"boolean"==typeof a?(b=a,a=b===!0?0:d.slideCount-1):a=b===!0?--a:a,d.slideCount<1||0>a||a>d.slideCount-1?!1:(d.unload(),c===!0?d.$slideTrack.children().remove():d.$slideTrack.children(this.options.slide).eq(a).remove(),d.$slides=d.$slideTrack.children(this.options.slide),d.$slideTrack.children(this.options.slide).detach(),d.$slideTrack.append(d.$slides),d.$slidesCache=d.$slides,void d.reinit())},b.prototype.setCSS=function(a){var d,e,b=this,c={};b.options.rtl===!0&&(a=-a),d="left"==b.positionProp?Math.ceil(a)+"px":"0px",e="top"==b.positionProp?Math.ceil(a)+"px":"0px",c[b.positionProp]=a,b.transformsEnabled===!1?b.$slideTrack.css(c):(c={},b.cssTransitions===!1?(c[b.animType]="translate("+d+", "+e+")",b.$slideTrack.css(c)):(c[b.animType]="translate3d("+d+", "+e+", 0px)",b.$slideTrack.css(c)))},b.prototype.setDimensions=function(){var a=this;a.options.vertical===!1?a.options.centerMode===!0&&a.$list.css({padding:"0px "+a.options.centerPadding}):(a.$list.height(a.$slides.first().outerHeight(!0)*a.options.slidesToShow),a.options.centerMode===!0&&a.$list.css({padding:a.options.centerPadding+" 0px"})),a.listWidth=a.$list.width(),a.listHeight=a.$list.height(),a.options.vertical===!1&&a.options.variableWidth===!1?(a.slideWidth=Math.ceil(a.listWidth/a.options.slidesToShow),a.$slideTrack.width(Math.ceil(a.slideWidth*a.$slideTrack.children(".slick-slide").length))):a.options.variableWidth===!0?a.$slideTrack.width(5e3*a.slideCount):(a.slideWidth=Math.ceil(a.listWidth),a.$slideTrack.height(Math.ceil(a.$slides.first().outerHeight(!0)*a.$slideTrack.children(".slick-slide").length)));var b=a.$slides.first().outerWidth(!0)-a.$slides.first().width();a.options.variableWidth===!1&&a.$slideTrack.children(".slick-slide").width(a.slideWidth-b)},b.prototype.setFade=function(){var c,b=this;b.$slides.each(function(d,e){c=b.slideWidth*d*-1,b.options.rtl===!0?a(e).css({position:"relative",right:c,top:0,zIndex:b.options.zIndex-2,opacity:0}):a(e).css({position:"relative",left:c,top:0,zIndex:b.options.zIndex-2,opacity:0})}),b.$slides.eq(b.currentSlide).css({zIndex:b.options.zIndex-1,opacity:1})},b.prototype.setHeight=function(){var a=this;if(1===a.options.slidesToShow&&a.options.adaptiveHeight===!0&&a.options.vertical===!1){var b=a.$slides.eq(a.currentSlide).outerHeight(!0);a.$list.css("height",b)}},b.prototype.setOption=b.prototype.slickSetOption=function(b,c,d){var f,g,e=this;if("responsive"===b&&"array"===a.type(c))for(g in c)if("array"!==a.type(e.options.responsive))e.options.responsive=[c[g]];else{for(f=e.options.responsive.length-1;f>=0;)e.options.responsive[f].breakpoint===c[g].breakpoint&&e.options.responsive.splice(f,1),f--;e.options.responsive.push(c[g])}else e.options[b]=c;d===!0&&(e.unload(),e.reinit())},b.prototype.setPosition=function(){var a=this;a.setDimensions(),a.setHeight(),a.options.fade===!1?a.setCSS(a.getLeft(a.currentSlide)):a.setFade(),a.$slider.trigger("setPosition",[a])},b.prototype.setProps=function(){var a=this,b=document.body.style;a.positionProp=a.options.vertical===!0?"top":"left","top"===a.positionProp?a.$slider.addClass("slick-vertical"):a.$slider.removeClass("slick-vertical"),(void 0!==b.WebkitTransition||void 0!==b.MozTransition||void 0!==b.msTransition)&&a.options.useCSS===!0&&(a.cssTransitions=!0),a.options.fade&&("number"==typeof a.options.zIndex?a.options.zIndex<3&&(a.options.zIndex=3):a.options.zIndex=a.defaults.zIndex),void 0!==b.OTransform&&(a.animType="OTransform",a.transformType="-o-transform",a.transitionType="OTransition",void 0===b.perspectiveProperty&&void 0===b.webkitPerspective&&(a.animType=!1)),void 0!==b.MozTransform&&(a.animType="MozTransform",a.transformType="-moz-transform",a.transitionType="MozTransition",void 0===b.perspectiveProperty&&void 0===b.MozPerspective&&(a.animType=!1)),void 0!==b.webkitTransform&&(a.animType="webkitTransform",a.transformType="-webkit-transform",a.transitionType="webkitTransition",void 0===b.perspectiveProperty&&void 0===b.webkitPerspective&&(a.animType=!1)),void 0!==b.msTransform&&(a.animType="msTransform",a.transformType="-ms-transform",a.transitionType="msTransition",void 0===b.msTransform&&(a.animType=!1)),void 0!==b.transform&&a.animType!==!1&&(a.animType="transform",a.transformType="transform",a.transitionType="transition"),a.transformsEnabled=a.options.useTransform&&null!==a.animType&&a.animType!==!1},b.prototype.setSlideClasses=function(a){var c,d,e,f,b=this;d=b.$slider.find(".slick-slide").removeClass("slick-active slick-center slick-current").attr("aria-hidden","true"),b.$slides.eq(a).addClass("slick-current"),b.options.centerMode===!0?(c=Math.floor(b.options.slidesToShow/2),b.options.infinite===!0&&(a>=c&&a<=b.slideCount-1-c?b.$slides.slice(a-c,a+c+1).addClass("slick-active").attr("aria-hidden","false"):(e=b.options.slidesToShow+a,d.slice(e-c+1,e+c+2).addClass("slick-active").attr("aria-hidden","false")),0===a?d.eq(d.length-1-b.options.slidesToShow).addClass("slick-center"):a===b.slideCount-1&&d.eq(b.options.slidesToShow).addClass("slick-center")),b.$slides.eq(a).addClass("slick-center")):a>=0&&a<=b.slideCount-b.options.slidesToShow?b.$slides.slice(a,a+b.options.slidesToShow).addClass("slick-active").attr("aria-hidden","false"):d.length<=b.options.slidesToShow?d.addClass("slick-active").attr("aria-hidden","false"):(f=b.slideCount%b.options.slidesToShow,e=b.options.infinite===!0?b.options.slidesToShow+a:a,b.options.slidesToShow==b.options.slidesToScroll&&b.slideCount-a<b.options.slidesToShow?d.slice(e-(b.options.slidesToShow-f),e+f).addClass("slick-active").attr("aria-hidden","false"):d.slice(e,e+b.options.slidesToShow).addClass("slick-active").attr("aria-hidden","false")),"ondemand"===b.options.lazyLoad&&b.lazyLoad()},b.prototype.setupInfinite=function(){var c,d,e,b=this;if(b.options.fade===!0&&(b.options.centerMode=!1),b.options.infinite===!0&&b.options.fade===!1&&(d=null,b.slideCount>b.options.slidesToShow)){for(e=b.options.centerMode===!0?b.options.slidesToShow+1:b.options.slidesToShow,c=b.slideCount;c>b.slideCount-e;c-=1)d=c-1,a(b.$slides[d]).clone(!0).attr("id","").attr("data-slick-index",d-b.slideCount).prependTo(b.$slideTrack).addClass("slick-cloned");for(c=0;e>c;c+=1)d=c,a(b.$slides[d]).clone(!0).attr("id","").attr("data-slick-index",d+b.slideCount).appendTo(b.$slideTrack).addClass("slick-cloned");b.$slideTrack.find(".slick-cloned").find("[id]").each(function(){a(this).attr("id","")})}},b.prototype.setPaused=function(a){var b=this;b.options.autoplay===!0&&b.options.pauseOnHover===!0&&(b.paused=a,a?b.autoPlayClear():b.autoPlay())},b.prototype.selectHandler=function(b){var c=this,d=a(b.target).is(".slick-slide")?a(b.target):a(b.target).parents(".slick-slide"),e=parseInt(d.attr("data-slick-index"));return e||(e=0),c.slideCount<=c.options.slidesToShow?(c.setSlideClasses(e),void c.asNavFor(e)):void c.slideHandler(e)},b.prototype.slideHandler=function(a,b,c){var d,e,f,g,h=null,i=this;return b=b||!1,i.animating===!0&&i.options.waitForAnimate===!0||i.options.fade===!0&&i.currentSlide===a||i.slideCount<=i.options.slidesToShow?void 0:(b===!1&&i.asNavFor(a),d=a,h=i.getLeft(d),g=i.getLeft(i.currentSlide),i.currentLeft=null===i.swipeLeft?g:i.swipeLeft,i.options.infinite===!1&&i.options.centerMode===!1&&(0>a||a>i.getDotCount()*i.options.slidesToScroll)?void(i.options.fade===!1&&(d=i.currentSlide,c!==!0?i.animateSlide(g,function(){i.postSlide(d);
}):i.postSlide(d))):i.options.infinite===!1&&i.options.centerMode===!0&&(0>a||a>i.slideCount-i.options.slidesToScroll)?void(i.options.fade===!1&&(d=i.currentSlide,c!==!0?i.animateSlide(g,function(){i.postSlide(d)}):i.postSlide(d))):(i.options.autoplay===!0&&clearInterval(i.autoPlayTimer),e=0>d?i.slideCount%i.options.slidesToScroll!==0?i.slideCount-i.slideCount%i.options.slidesToScroll:i.slideCount+d:d>=i.slideCount?i.slideCount%i.options.slidesToScroll!==0?0:d-i.slideCount:d,i.animating=!0,i.$slider.trigger("beforeChange",[i,i.currentSlide,e]),f=i.currentSlide,i.currentSlide=e,i.setSlideClasses(i.currentSlide),i.updateDots(),i.updateArrows(),i.options.fade===!0?(c!==!0?(i.fadeSlideOut(f),i.fadeSlide(e,function(){i.postSlide(e)})):i.postSlide(e),void i.animateHeight()):void(c!==!0?i.animateSlide(h,function(){i.postSlide(e)}):i.postSlide(e))))},b.prototype.startLoad=function(){var a=this;a.options.arrows===!0&&a.slideCount>a.options.slidesToShow&&(a.$prevArrow.hide(),a.$nextArrow.hide()),a.options.dots===!0&&a.slideCount>a.options.slidesToShow&&a.$dots.hide(),a.$slider.addClass("slick-loading")},b.prototype.swipeDirection=function(){var a,b,c,d,e=this;return a=e.touchObject.startX-e.touchObject.curX,b=e.touchObject.startY-e.touchObject.curY,c=Math.atan2(b,a),d=Math.round(180*c/Math.PI),0>d&&(d=360-Math.abs(d)),45>=d&&d>=0?e.options.rtl===!1?"left":"right":360>=d&&d>=315?e.options.rtl===!1?"left":"right":d>=135&&225>=d?e.options.rtl===!1?"right":"left":e.options.verticalSwiping===!0?d>=35&&135>=d?"left":"right":"vertical"},b.prototype.swipeEnd=function(a){var c,b=this;if(b.dragging=!1,b.shouldClick=b.touchObject.swipeLength>10?!1:!0,void 0===b.touchObject.curX)return!1;if(b.touchObject.edgeHit===!0&&b.$slider.trigger("edge",[b,b.swipeDirection()]),b.touchObject.swipeLength>=b.touchObject.minSwipe)switch(b.swipeDirection()){case"left":c=b.options.swipeToSlide?b.checkNavigable(b.currentSlide+b.getSlideCount()):b.currentSlide+b.getSlideCount(),b.slideHandler(c),b.currentDirection=0,b.touchObject={},b.$slider.trigger("swipe",[b,"left"]);break;case"right":c=b.options.swipeToSlide?b.checkNavigable(b.currentSlide-b.getSlideCount()):b.currentSlide-b.getSlideCount(),b.slideHandler(c),b.currentDirection=1,b.touchObject={},b.$slider.trigger("swipe",[b,"right"])}else b.touchObject.startX!==b.touchObject.curX&&(b.slideHandler(b.currentSlide),b.touchObject={})},b.prototype.swipeHandler=function(a){var b=this;if(!(b.options.swipe===!1||"ontouchend"in document&&b.options.swipe===!1||b.options.draggable===!1&&-1!==a.type.indexOf("mouse")))switch(b.touchObject.fingerCount=a.originalEvent&&void 0!==a.originalEvent.touches?a.originalEvent.touches.length:1,b.touchObject.minSwipe=b.listWidth/b.options.touchThreshold,b.options.verticalSwiping===!0&&(b.touchObject.minSwipe=b.listHeight/b.options.touchThreshold),a.data.action){case"start":b.swipeStart(a);break;case"move":b.swipeMove(a);break;case"end":b.swipeEnd(a)}},b.prototype.swipeMove=function(a){var d,e,f,g,h,b=this;return h=void 0!==a.originalEvent?a.originalEvent.touches:null,!b.dragging||h&&1!==h.length?!1:(d=b.getLeft(b.currentSlide),b.touchObject.curX=void 0!==h?h[0].pageX:a.clientX,b.touchObject.curY=void 0!==h?h[0].pageY:a.clientY,b.touchObject.swipeLength=Math.round(Math.sqrt(Math.pow(b.touchObject.curX-b.touchObject.startX,2))),b.options.verticalSwiping===!0&&(b.touchObject.swipeLength=Math.round(Math.sqrt(Math.pow(b.touchObject.curY-b.touchObject.startY,2)))),e=b.swipeDirection(),"vertical"!==e?(void 0!==a.originalEvent&&b.touchObject.swipeLength>4&&a.preventDefault(),g=(b.options.rtl===!1?1:-1)*(b.touchObject.curX>b.touchObject.startX?1:-1),b.options.verticalSwiping===!0&&(g=b.touchObject.curY>b.touchObject.startY?1:-1),f=b.touchObject.swipeLength,b.touchObject.edgeHit=!1,b.options.infinite===!1&&(0===b.currentSlide&&"right"===e||b.currentSlide>=b.getDotCount()&&"left"===e)&&(f=b.touchObject.swipeLength*b.options.edgeFriction,b.touchObject.edgeHit=!0),b.options.vertical===!1?b.swipeLeft=d+f*g:b.swipeLeft=d+f*(b.$list.height()/b.listWidth)*g,b.options.verticalSwiping===!0&&(b.swipeLeft=d+f*g),b.options.fade===!0||b.options.touchMove===!1?!1:b.animating===!0?(b.swipeLeft=null,!1):void b.setCSS(b.swipeLeft)):void 0)},b.prototype.swipeStart=function(a){var c,b=this;return 1!==b.touchObject.fingerCount||b.slideCount<=b.options.slidesToShow?(b.touchObject={},!1):(void 0!==a.originalEvent&&void 0!==a.originalEvent.touches&&(c=a.originalEvent.touches[0]),b.touchObject.startX=b.touchObject.curX=void 0!==c?c.pageX:a.clientX,b.touchObject.startY=b.touchObject.curY=void 0!==c?c.pageY:a.clientY,void(b.dragging=!0))},b.prototype.unfilterSlides=b.prototype.slickUnfilter=function(){var a=this;null!==a.$slidesCache&&(a.unload(),a.$slideTrack.children(this.options.slide).detach(),a.$slidesCache.appendTo(a.$slideTrack),a.reinit())},b.prototype.unload=function(){var b=this;a(".slick-cloned",b.$slider).remove(),b.$dots&&b.$dots.remove(),b.$prevArrow&&b.htmlExpr.test(b.options.prevArrow)&&b.$prevArrow.remove(),b.$nextArrow&&b.htmlExpr.test(b.options.nextArrow)&&b.$nextArrow.remove(),b.$slides.removeClass("slick-slide slick-active slick-visible slick-current").attr("aria-hidden","true").css("width","")},b.prototype.unslick=function(a){var b=this;b.$slider.trigger("unslick",[b,a]),b.destroy()},b.prototype.updateArrows=function(){var b,a=this;b=Math.floor(a.options.slidesToShow/2),a.options.arrows===!0&&a.slideCount>a.options.slidesToShow&&!a.options.infinite&&(a.$prevArrow.removeClass("slick-disabled").attr("aria-disabled","false"),a.$nextArrow.removeClass("slick-disabled").attr("aria-disabled","false"),0===a.currentSlide?(a.$prevArrow.addClass("slick-disabled").attr("aria-disabled","true"),a.$nextArrow.removeClass("slick-disabled").attr("aria-disabled","false")):a.currentSlide>=a.slideCount-a.options.slidesToShow&&a.options.centerMode===!1?(a.$nextArrow.addClass("slick-disabled").attr("aria-disabled","true"),a.$prevArrow.removeClass("slick-disabled").attr("aria-disabled","false")):a.currentSlide>=a.slideCount-1&&a.options.centerMode===!0&&(a.$nextArrow.addClass("slick-disabled").attr("aria-disabled","true"),a.$prevArrow.removeClass("slick-disabled").attr("aria-disabled","false")))},b.prototype.updateDots=function(){var a=this;null!==a.$dots&&(a.$dots.find("li").removeClass("slick-active").attr("aria-hidden","true"),a.$dots.find("li").eq(Math.floor(a.currentSlide/a.options.slidesToScroll)).addClass("slick-active").attr("aria-hidden","false"))},b.prototype.visibility=function(){var a=this;document[a.hidden]?(a.paused=!0,a.autoPlayClear()):a.options.autoplay===!0&&(a.paused=!1,a.autoPlay())},b.prototype.initADA=function(){var b=this;b.$slides.add(b.$slideTrack.find(".slick-cloned")).attr({"aria-hidden":"true",tabindex:"-1"}).find("a, input, button, select").attr({tabindex:"-1"}),b.$slideTrack.attr("role","listbox"),b.$slides.not(b.$slideTrack.find(".slick-cloned")).each(function(c){a(this).attr({role:"option","aria-describedby":"slick-slide"+b.instanceUid+c})}),null!==b.$dots&&b.$dots.attr("role","tablist").find("li").each(function(c){a(this).attr({role:"presentation","aria-selected":"false","aria-controls":"navigation"+b.instanceUid+c,id:"slick-slide"+b.instanceUid+c})}).first().attr("aria-selected","true").end().find("button").attr("role","button").end().closest("div").attr("role","toolbar"),b.activateADA()},b.prototype.activateADA=function(){var a=this;a.$slideTrack.find(".slick-active").attr({"aria-hidden":"false"}).find("a, input, button, select").attr({tabindex:"0"})},b.prototype.focusHandler=function(){var b=this;b.$slider.on("focus.slick blur.slick","*",function(c){c.stopImmediatePropagation();var d=a(this);setTimeout(function(){b.isPlay&&(d.is(":focus")?(b.autoPlayClear(),b.paused=!0):(b.paused=!1,b.autoPlay()))},0)})},a.fn.slick=function(){var f,g,a=this,c=arguments[0],d=Array.prototype.slice.call(arguments,1),e=a.length;for(f=0;e>f;f++)if("object"==typeof c||"undefined"==typeof c?a[f].slick=new b(a[f],c):g=a[f].slick[c].apply(a[f].slick,d),"undefined"!=typeof g)return g;return a}});$(document).ready(function() {
	
	/* for navigation drop down */    
		$('.shop-navchild').hover(function() {
            var el = $("body");
            if($(window).width()>1025){
            $(this).toggleClass("active");
            el.toggleClass("shop_nav_show");
            }    
            return false; 
        });
		
		
		/* for mobile shop-navigations */	
          $('.shop_link__mobilenav').click(function(){

              if($(this).hasClass('active')){
                  $(this).removeClass('active');
                  $(this).siblings('.shop-navigations > li .subnav').slideUp();
                  return false;
              }
              $('.shop_link__mobilenav').removeClass('active');
              $(this).addClass("active");
              if($(window).width()<1025){
                  $('.shop-navigations > li .subnav').slideUp();
                  $(this).siblings('.shop-navigations > li .subnav').slideDown();
              }
              return;
          });
		  
		  
		   /* for mobile toggle navigation */    
		$('.shop_navs_toggle').click(function() {
            $(this).toggleClass("active");
			var el = $("body");
			if(el.hasClass('toggled_shop-nav')) el.removeClass("toggled_shop-nav");
			else el.addClass('toggled_shop-nav');
            return false; 
        });
		
		$('body').click(function(){
            if($('body').hasClass('toggled_shop-nav')){
                $('.shop_navs_toggle').removeClass("active");
                $('body').removeClass('toggled_shop-nav');
            }
        });
    
        $('.mobile__overlay').click(function(){
            if($('body').hasClass('toggled_shop-nav')){
                $('.shop_navs_toggle').removeClass("active");
                $('body').removeClass('toggled_shop-nav');
            }
        });
		
		
		$('.shop-nav,.section_primary').click(function(e){
            e.stopPropagation();
            //return false;
        });
      
});





           


 /*
 *  colourBrightness.js
 *
 *  Copyright 2013-2016, Jamie Brittain - http://jamiebrittain.com
 *  Released under the WTFPL license
 *  http://sam.zoy.org/wtfpl/
 *
 *  Github:  http://github.com/jamiebrittain/colourBrightness.js
 *  Version: 1.2
 */
!function(r){r.fn.colourBrightness=function(){function r(r){for(var t="";"html"!=r[0].tagName.toLowerCase()&&(t=r.css("background-color"),"rgba(0, 0, 0, 0)"==t||"transparent"==t);)r=r.parent();return t}var t,a,s,e,n=r(this);return n.match(/^rgb/)?(n=n.match(/rgba?\(([^)]+)\)/)[1],n=n.split(/ *, */).map(Number),t=n[0],a=n[1],s=n[2]):"#"==n[0]&&7==n.length?(t=parseInt(n.slice(1,3),16),a=parseInt(n.slice(3,5),16),s=parseInt(n.slice(5,7),16)):"#"==n[0]&&4==n.length&&(t=parseInt(n[1]+n[1],16),a=parseInt(n[2]+n[2],16),s=parseInt(n[3]+n[3],16)),e=(299*t+587*a+114*s)/1e3,125>e?this.removeClass("light").addClass("dark"):this.removeClass("dark").addClass("light"),this}}(jQuery);
$(document).ready(function(){
	// Target your element
	//searchProducts( document.frmProductSearch );	
	if($(".shop-navigations").length){
		$('.shop-navigations').colourBrightness();
	}

	if($(".input-field").length){
		$('.input-field').colourBrightness();
	}
});
