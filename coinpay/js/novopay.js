/* jshint browser: true, strict: false, maxlen: false, maxstatements: false */


	var NovoPay = {
		iframe : null,
		warn : function() {
			if (window.console && window.console.warn) {
				window.console.warn.apply(window.console, arguments);
			}
		},
		log : function(msg) {
			if (window.console && window.console.log) {
				window.console.log(msg);
			}
		},
		onModalWillEnterMethod : function() {
		},
		onModalWillLeaveMethod : function() {
		},
		showInvoice : function(params) {
			this.appendFrame();
			var invoiceUrl = 'http://localhost:8080/coinpay/invoice.php';
			var querystring = [];
			for (var k in params) {
				querystring.push(k +'='+ params[k]);
			}
			invoiceUrl += '?'+ querystring.join('&');
			this.iframe.src = invoiceUrl;
		},
		showPayForm : function(params) {
			this.showInvoice(params);
			this.showFrame();
		},
		hidePayForm : function() {
			this.iframe.style.display = 'none';
		},
		showFrame : function() {
			this.appendFrame();
			this.onModalWillEnterMethod();
			this.iframe.style.display = 'block';
		},
		appendFrame : function() {
			if (window.document.getElementsByName('novopay').length === 0) {
				window.document.body.appendChild(this.iframe);
			}
		},
		hideFrame : function() {
			this.onModalWillLeaveMethod();
			this.iframe.style.display = 'none';
			//window.document.body.removeChild(iframe);
		},
		initIFrame : function() {
			this.iframe = document.createElement('iframe');
			this.iframe.name = 'novopay';
			this.iframe.class = 'novopay';
			this.iframe.setAttribute('allowtransparency', 'true');
			this.iframe.style.display = 'none';
			this.iframe.style.border = 0;
			this.iframe.style.position = 'fixed';
			this.iframe.style.top = 0;
			this.iframe.style.left = 0;
			this.iframe.style.height = '100%';
			this.iframe.style.width = '100%';
			this.iframe.style.zIndex = '2147483647';
		},
		onModalWillEnter : function(customOnModalWillEnter) {
			this.onModalWillEnterMethod = customOnModalWillEnter;
		},
		onModalWillLeave : function(customOnModalWillLeave) {
			this.onModalWillLeaveMethod = customOnModalWillLeave;
		},
		init : function() {
			this.initEnv();
			this.initIFrame();
		},
		initEnv : function() {
		},
		receiveMessage : function(event) {
			console.log(event);
		}
	}
	window.addEventListener('load', function load() {
		NovoPay.init();
		window.addEventListener("message", NovoPay.receiveMessage, false);
	});
