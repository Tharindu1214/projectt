siteConstants.userWebRoot = (siteConstants.rewritingEnabled)?siteConstants.webroot:siteConstants.webroot_traditional;
var pageReloading = false;
var fcom = {
		ajaxRequestLog: [],
		logAjaxRequest: function(url, data, res, ajaxLoopHandler) {
			
			var d = (new Date()).getTime();
			var last = d - 20000;
			var obj = {url: url, 
					data: (typeof data == "object")?JSON.stringify(data):data, 
					res: (typeof res == "object")?JSON.stringify(res):res, 
					t: d};
			
			var repeatCount = 0;
			
			for (var i = fcom.ajaxRequestLog.length - 1; i >= 0; i--) {
				var oldObj = fcom.ajaxRequestLog[i];
				if (oldObj.t < last) {
					fcom.ajaxRequestLog.splice(i, 1);
					continue;
				}
				
				if (oldObj.url == obj.url && oldObj.data == obj.data && oldObj.res == obj.res) {
					repeatCount ++;
				}
			}
			
			fcom.ajaxRequestLog.push(obj);
			
			if (repeatCount >= 5 && !pageReloading) {
				if (confirm('This page seems to be stuck with some ajax call loop.\nDo you want to reload the page?')) {
					pageReloading = true;
					location.reload();
				}
			}
			
			if (ajaxLoopHandler && repeatCount >= 2) {
				console.log('Executing ajaxLoopHandler for url: ' + url + ', data: ' + obj.data + ', res: ' + obj.res);
				ajaxLoopHandler();
			}
			
			return repeatCount;
		},
		
		ajax: function(url, data, fn, options) {
			var o = $.extend(true, {fOutMode:'html', timeout: null, maxRetry: 0, retryNumber: 0, ajaxLoopHandler: null}, options);
			if ( "string" == $.type(data) ) {
				data += '&fOutMode=' + o.fOutMode + '&fIsAjax=1';
			}
			
			if ( "object" == $.type(data) ) {
				var data = $.extend(true, {}, data);
				if ( !data.isAjax ) data.fIsAjax = 1;
				if ( !data.fOutMode ) data.fOutMode = o.fOutMode;
			}
			
			var dbmsg = o.dbmsg||'<img src="img/loading.gif" alt="Processing..">';
			var dvdebug = $('<div />').append(dbmsg);
			dvdebug.appendTo($('#dv-bg-processes'));
			
			$.ajax({
				method: "POST",
				url: url,
				data: data,
				dataType: o.fOutMode,
				success: function (t) {
					dvdebug.remove();
					var repeatCount = fcom.logAjaxRequest(url, data, t, o.ajaxLoopHandler);
					if (o.fOutMode == 'json') {
						if (t.status == -1) {
							alert(t.msg);
							if (options.errorFn) {
								options.errorFn();
							}
							return ;
						}
					}
					if (repeatCount >= 2 && o.ajaxLoopHandler) {
						setTimeout(function() {
							fn(t);
						}, 1000);
					} 
					else {
						fn(t);
					}
				},
				error: function(jqXHR, textStatus, error) {
					dvdebug.remove();
					if(textStatus == "parsererror" && jqXHR.statusText == "OK") {
				        alert('Seems some json error.' + jqXHR.responseText);
				        return ;
				    }
					
					
					o.retryNumber++;
					if (o.retryNumber <= o.maxRetry) {
						setTimeout(function() {
							fcom.ajax(url, data, fn, o)
						}, 3000);
					}
					else {
						if (!options.errorFn) {
							console.log('Http Error: ' + textStatus);
							/* alert('Http Error: ' + textStatus); */
						}
					}
					
					console.log( "Ajax Request " + url + " error: " + textStatus + " -- " + error);
					if (options.errorFn) {
						options.errorFn();
					}
				},
				timeout: o.timeout
			});
		},
		
		updateWithAjax: function(url, data, fn, options) {
			$.mbsmessage('Processing...');
			var o = $.extend(true, {fOutMode:'json'}, options);
			this.ajax(url, data, function(ans) {
				if (ans.status != 1) {
					$(document).trigger('close.mbsmessage');
					alert(ans.msg);
					return ;
				}
				$.mbsmessage(ans.msg, true);
				fn (ans);
			}, o);
		},
		
		camel2dashed: function(str) {
			return str.replace(/([a-zA-Z])(?=[A-Z])/g, '$1-').toLowerCase();
		},
		
		breakUrl: function(url) {
			url = url.substring(siteConstants.userWebRoot.length);
			var arr = url.split('/');
			var obj = {controller: arr[0], action:'', others: []};
			arr.shift();
			if (!arr.length) return obj;
			
			obj.action = arr[0];
			arr.shift();
			
			obj.others = arr;
			
			return obj;
		},
		
		makeUrl: function(controller, action, others, use_root_url, urlRewritingEnabled) {
			if ( typeof urlRewritingEnabled === 'undefined' ) {
				urlRewritingEnabled = (siteConstants.rewritingEnabled == 1);
			}
			if (!use_root_url) {
				use_root_url = (urlRewritingEnabled)?siteConstants.webroot:siteConstants.webroot_traditional;			
			}
			var url;
			if (!controller) controller = '';
			if (!action) action = '';
			
			controller = this.camel2dashed(controller);
			action = this.camel2dashed(action);
			
			if (!others) others = [];
			if ('' == action && others.length) action = 'index';
			
			url = use_root_url + controller;
			
			if ('' != action) url += '/' + action;
			if (others.length){
				for (x in others) others[x] = encodeURIComponent(others[x]);
				url += '/' + others.join('/');
			}
			return url;
		},
		frmData: function(frm) {
			var disabled = $(frm).find(':input:disabled').removeAttr('disabled');
			var out = $(frm).serialize();
			disabled.attr('disabled','disabled');
			return out;
		},
		qStringToObject: function(q) {
			var args = new Object(); 
			var pairs = q.split("&"); 
			for(var i = 0; i < pairs.length; i++) { 
				var pos = pairs[i].indexOf('='); 
				if (pos == -1) continue; 
				var argname = pairs[i].substring(0,pos); 
				var value = pairs[i].substring(pos+1); 
				args[argname] = unescape(value); 
			} 
			return args;
		},
		urlWrittenQueryObject: function() {
			var url = location.pathname;
			url = url.substring(siteConstants.userWebRoot.length);
			var arr = url.split('/');
			if (arr.length <= 2) return {};
			arr.shift();
			arr.shift();
			var obj = {};
			for (var i = 0; i < arr.length; i+=2) {
				obj[arr[i]] = arr[i+1];
			}
			
			return obj;
		}
};

$.fn.selectRange = function(start, end) {
    if(!end) end = start; 
    return this.each(function() {
        if (this.setSelectionRange) {
        	if (!$(this).is(':visible')) return;
            this.focus();
            this.setSelectionRange(start, end);
        } else if (this.createTextRange) {
            var range = this.createTextRange();
            range.collapse(true);
            range.moveEnd('character', end);
            range.moveStart('character', start);
            range.select();
        }
    });
};