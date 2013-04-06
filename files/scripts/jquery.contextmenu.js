/*
 *
 * jQuery Context Menu Plugin
 * Developed by James Brooks (http://me.itslimetime.com) 2011 
 * ukjbrooks[at]gmail[dot]com
 *
 */

(function($) {
	var menu, shadow, trigger, content, settings, currentTarget;

	var defaults = {
		menuID: 'ctxtMnu',
		menuStyle: {
			listStyle: 'none',
			padding: '1px',
			margin: '0px',
			backgroundColor: '#FFF',
			border: '1px solid #AAA',
			mozBoxShadow: '0px 0px 5px 2px #AAAAAA',
			webKitBoxShadow: '0px 0px 7px #AAAAAA',
			boxShadow: '0px 0px 5px 2px #AAAAAA'
		},
		itemStyle: {
			margin: '0px',
			color: '#000',
			display: 'block',
			cursor: 'default',
			padding: '3px',
			border: '1px solid #FFF',
			backgroundColor: 'transparent'
		},
		itemHoverStyle: {
			border: '1px solid #0A246A',
			backgroundColor: '#B6BDD2'
		},
		eventPosX: 'pageX',
		eventPosY: 'pageY',
		onContextMenu: null,
		onShowMenu: null,
		onCloseMenu: null,
		onItemClick: null
	};

	var method = {};

	$.fn.contextMenu = function() {
		methods = {
			init : function(id, options) {
				settings = settings || [];
				settings.push({
					id: id,
					menuStyle: $.extend({}, defaults.menuStyle, options.menuStyle || {}),
					itemStyle: $.extend({}, defaults.itemStyle, options.itemStyle || {}),
					itemHoverStyle: $.extend({}, defaults.itemHoverStyle, options.itemHoverStyle || {}),
					bindings: options.bindings || {},
					onContextMenu: options.onContextMenu || defaults.onContextMenu,
					onShowMenu: options.onShowMenu || defaults.onShowMenu,
					onCloseMenu: options.onCloseMenu || defaults.onCloseMenu,
					onItemClick: options.onItemClick || defaults.onItemClick,
					eventPosX: options.eventPosX || defaults.eventPosX,
					eventPosY: options.eventPosY || defaults.eventPosY
				});

				if(!menu) {
					menu = $('<div id="jQContextMenu"></div>')
							.hide()
							.css({
								position: 'absolute',
								zIndex: '500'
							})
							.appendTo('body')
							.bind('click', function(e) {
								e.stopPropagation()
							});
				}

				var index = settings.length - 1;
				$(this).bind('contextmenu', function(e) {
					var bShowContext = (!!settings[index].onContextMenu) ? settings[index].onContextMenu(e) : true;
					if(bShowContext) helpers.create(index, this, e, options);
					return false;
				});

				return this;
			}
		}

		var helpers = {
			create: function(index, trigger, e, options) {
				var cur = settings[index];
				content = $("#" + cur.id).find('ul:first').clone(true);
				content.css(cur.menuStyle).find('li').css(cur.itemStyle).hover(function() {
					$(this).css(cur.itemHoverStyle);
				}, function() {
					$(this).css(cur.itemStyle);
				}).find('img').css({verticalAlign : 'middle', paddingRight: '2px'});

				menu.html(content);

				if(!!cur.onShowMenu) menu = cur.onShowMenu(e, menu);

				$.each(cur.bindings, function(id, func) {
					$("#" + id, menu).bind('click', function(e) {
						if(!!cur.onItemClick) cur.onItemClick();
						helpers.hideMenu();
						func(trigger, currentTarget);
					});
				});

				menu.css({left : e[cur.eventPosX], top : e[cur.eventPosY]}).show();
				$(document).one('click', function() {
					if(!!cur.onCloseMenu) cur.onCloseMenu(e, menu);
					helpers.hideMenu();
				});
			},
			hideMenu: function() {
				menu.hide();
			}
		}

		$.contextMenu = {
			defaults: function(userDefaults) {
				$.each(userDefaults, function(i, val) {
					if(typeof val == 'object' && defaults[i]) {
						$.extend(defaults[i], val);
					}else{
						defaults[i] = val;
					}
				});
			}
		};

		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method "' +  method + '" does not exist in contextMenu plugin!');
		}
	}
})(jQuery);