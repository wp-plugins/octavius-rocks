(function( $ ) {
	'use strict';

	/**
	 * octavius to websocket
	 */
	var OctaviusAdmin  =  function(){
		/**
		 * quick access references
		 */
		var oc = null;
		var config = null;
		var is_ready  = this.is_ready = false;
		var socket = this.socket = null;

		this.init = function(octavius){
			var self = this;
			oc = octavius;
			config = octavius.config;
			oc.admincore = this;
			/**
			 * register octavius socket and listen to connection
			 */
			socket = this.socket = Octavius.socket = io(config.service);
			socket.on('connected', function (data) {
				socket.emit('init',{ api_key: oc.api_key });
			});
			/**
			 * get ready sign
			 * 
			 */
			socket.on("ready", function(data){
				// TODO: init all scripts that use OctaviusAdmin Socket connection
				self.is_ready = true;
			});
			/**
			 * if we get disconnected from octavius
			 */
			socket.on('disconnect', function(info){
				console.error("Connection lost");
				console.error([info]);
				self.is_ready = false;
			});
			this.init_modules(octavius);
		}
		var _modules = [];
		this.add_module = function(module){
			_modules.push(module);
		}
		this.init_modules = function(octavius){
			for( var i = 0; i < _modules.length; i++){
				_modules[i].init(octavius);
			}
		}

	}
	var octavius_admin = window.octavius_admin = new OctaviusAdmin();

	/** 
	 * try to init
	 */
	var init_timeout = null;
	var times = 1;
	function try_init(){
		init_timeout = setTimeout(function(){
			// if not available yet try another time
			if(typeof Octavius == typeof undefined){
				try_init();
				return;
			}
			// init oactavius admin core
			octavius_admin.init(Octavius);
		},500*(times++));
	}
	try_init();


	

})( jQuery );

