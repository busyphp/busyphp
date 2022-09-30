;(function ($, window) {
	/**
	 * 模态框
	 */
	$.dialog = $.dialog || {
			alert   : function (message, callback) {
				alert(message);
				if (typeof callback === "function") {
					callback();
				}
			},
			confirm : function (message, success, error) {
				if (confirm(message)) {
					if (typeof success === "function") {
						success();
					}
				} else {
					if (typeof error === "function") {
						error();
					}
				}
			},
			pending : function (message) {
				message      = message || '请稍后...';
				var $element = $('#dialog_pending');
				if ($element.length) {
					return $element;
				}
				$('body').append(
					'<div id="dialog_pending" class="dialog-pending" style="position: fixed; width: 100%; height: 100%; left: 0; top: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, .5); z-index: 9999; text-align: center;">' +
					'<div class="dialog-pending-bg"></div>' +
					'<div class="dialog-pending-msg" style="position: absolute; color: #FFF; text-align: center; height: 46px; top: 50%; margin-top: -23px; width: 200px; text-align: left; left: 50%; margin-left: -100px; border-radius: 3px; line-height: 46px; box-sizing: border-box; padding-left: 46px; background: rgba(0, 0, 0, .8) url(data:image/gif;base64,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) no-repeat 10px center / 26px 26px;">' + message + '</div>' +
					'</div>');
				return $('#dialog_pending');
			}
		};

	/**
	 * 全选反选组件
	 * @param {Object} options 配置
	 * @returns {selectCheckBox}
	 * @example
	 * new selectCheckBox({
	 * 		control : '.checkbox-all', 	// 全选或者反选的checkbox
	 * 		queue	: '.checkbox-list', // 被选择的checkbox列队
	 * 		singleSelection : function (element) {},  // 单个被选中回调
	 * 		singleCancelled : function (element) {},  // 单个被取消回调
	 * 		allSelected 	: function () {}, // 全部选中回调
	 * 		allCancelled 	: function () {}, // 全部取消回调
	 * });
	 */
	var selectCheckBox       = function (options) {
		var me              = this,
			options         = options || {},
			defaults        = {
				// selector 全选的元素
				control : null

				// selector 被选择的列队
				, queue : null

				// 以下是回调方法，其中内容对象 this 指向 {selectCheckBox}
				// 列队中单个item被选择回调，element 当前被操作的item
				, singleSelection : function (element) {
				}

				// 列队中单个item被取消选中回调，element 当前被操作的item
				, singleCancelled : function (element) {
				}

				// 所有列队被选中回调
				, allSelected : function () {
				}

				// 所有列队被取消选择回调
				, allCancelled : function () {
				}
			};
		// 配置
		me.settings         = $.extend({}, defaults, options);
		// 对象
		me.settings.control = typeof me.settings.control === "string" ? $(me.settings.control) : me.settings.control;
		me.settings.queue   = typeof me.settings.queue === "string" ? $(me.settings.queue) : me.settings.queue;
		// 当前已选中数
		me.checkedTotal     = 0;
		// 初始化
		me.init();
		return me;
	};
	selectCheckBox.prototype = {
		/**
		 * 初始化
		 * @returns {selectCheckBox}
		 */
		init          : function () {
			var me = this;
			// 绑定控制选择事件
			me.settings.control.data('checkbox_control', true).each(function () {
				var $this = $(this);
				$this.on('click', function () {
					if ($this.prop('checked')) {
						me.settings.queue.prop('checked', true);
						me.checkedTotal = me.settings.queue.length;
					} else {
						me.settings.queue.prop('checked', false);
						me.checkedTotal = 0;
					}
					me.indeterminate();
				});
			});
			// 绑定列队选择事件
			me.settings.queue.each(function () {
				var $this = $(this);
				// 默认选中项
				if (this.checked) {
					me.checkedTotal++;
					me.indeterminate();
				}
				// 选择事件
				$this.on('click', function () {
					if ($this.prop('checked')) {
						if (typeof me.settings.singleSelection === "function") {
							me.settings.singleSelection.call(me, this);
						}
						me.checkedTotal++;
					} else {
						if (typeof me.settings.singleCancelled === "function") {
							me.settings.singleCancelled.call(me, this);
						}
						me.checkedTotal--;
					}
					me.indeterminate();
				});
			});
			return me;
		},
		/**
		 * 控制器的状态设置
		 * @returns {selectCheckBox}
		 */
		indeterminate : function () {
			var me = this;
			if (me.checkedTotal === me.settings.queue.length) {
				me.settings.control.prop('checked', true);
				me.settings.control.each(function () {
					this.indeterminate = false;
				});
				if (typeof me.settings.allSelected === "function") {
					me.settings.allSelected.call(me);
				}
			} else if (me.checkedTotal) {
				me.settings.control.prop('checked', false);
				me.settings.control.each(function () {
					this.indeterminate = true;
				});
			} else {
				me.settings.control.prop('checked', false);
				me.settings.control.each(function () {
					this.indeterminate = false;
				});
				if (typeof me.settings.allCancelled === "function") {
					me.settings.allCancelled.call(me);
				}
			}
			return me;
		},
		/**
		 * 获取当前列队选中状态，如果有选中则返回true，否则返回false
		 * @param {Boolean} entirely 列队只有在全部选中时才会返回false
		 * @returns {boolean}
		 */
		getStatus     : function (entirely) {
			entirely = typeof entirely === "boolean" ? entirely : false;
			if (entirely && this.checkedTotal === this.settings.queue.length) {
				return true;
			} else if (false === entirely && this.checkedTotal) {
				return true;
			}
			return false;
		},
		/**
		 * 获取当前列队选中项的属性
		 * @param {String} type 要获得的选中项的属性，默认value属性，elements为获取对象本身，其它为获取定义属性
		 * @returns {Array}
		 */
		getChecked    : function (type) {
			var type  = type || 'value',
				value = [],
				me    = this;
			me.settings.queue.each(function () {
				if (false === this.checked) {
					return true;
				}
				switch (type) {
					// 值
					case 'value' :
						value.push(this.value);
						break;
					// 对象
					case 'elements' :
						value.push(this);
						break;
					// 自定义属性
					default :
						value.push(this.getAttribute(type));
				}
			});
			return value;
		}
	};
	/**
	 * 全选反选jQuery扩展
	 * @param {Object} options 配置，请参考 selectCheckBox 说明
	 * @returns {selectCheckBox}
	 */
	$.fn.selectCheckBox = function (options) {
		options.control = $(this);
		return new selectCheckBox(options);
	};
	/**
	 * 获取DATA属性值
	 * @param {String} role 名称
	 * @returns {String}
	 */
	$.fn.getDataValue = function (name) {
		return $(this).attr('data-' + name) || '';
	};
	/**
	 * 获取Role对象
	 * @param {String} role 名称
	 * @returns {$.fn}
	 */
	$.fn.getRole = function (name) {
		return $(this).find("[role='" + name + "']");
	};
	/**
	 * 获取功能属性值
	 * @param {String} role 名称
	 * @returns {String}
	 */
	$.fn.getRoleValue = function (name) {
		return $(this).attr('role-' + name) || '';
	};
	/**
	 * 获取NAME对象
	 */
	$.fn.getName = function (name) {
		return $(this).find("[name='" + name + "']");
	};

	/**
	 * 处理服务器返回的错误消息并执行对应出错字段焦点
	 * @param {Object} data 服务器返回的错误数据
	 * @return {$.fn}
	 */
	$.fn.parseFormError = function (data) {
		var $this = $(this), name = data.name;
		if (name) {
			var $field = $this.getName(name);
			if ($field.length) {
				$field[0].focus();
			}
			return this;
		}
		return this;
	};
	/**
	 * 处理返回，是指通过AJAX返回的JSON信息
	 * @param {json} json AJAX返回的JSON数据
	 * @param {function} 处理完成公共错误后的回调函数，参数1代表返回的JSON数据
	 */
	$.parseCode = function (json, callback) {
		if (JSON.stringify(json) === '{}') {
			$.dialog.alert('未知错误！');
			return false;
		}
		// 需要跳转
		switch (parseInt(json.data)) {
			// 直接跳转
			case 9999 :
				top.location.href = json.url;
				return false;

			// 弹出成功的警告框后跳转
			case 9998 :
				$.dialog.alert(json.info, function () {
					top.location.href = json.url;
				}, 'succeed');
				return false;

			// 弹出失败的警告框后跳转
			case 9997 :
				$.dialog.alert(json.info, function () {
					top.location.href = json.url;
				});
				return false;
		}
		// 其他错误直接回调
		if (typeof callback === 'function') {
			callback.call(this, json);
		}
	};
	/**
	 * POST提交
	 * @param {String} actionURL AJAX请求地址
	 * @param {Object} options 请求参数
	 * @param {Function} success 成功回调函数
	 * @param {Function} error 失败回调函数
	 */
	$.postInfo = function (actionURL, options, success, error, complete) {
		var actionURL = actionURL || document.URL,
			options   = options || {},
			success   = success || function () {
				},
			error     = error || function () {
				},
			complete  = complete || function () {
				};
		$.post(actionURL, options, function (data) {
			data = typeof data !== "object" ? {} : data;
			if (data.status) {
				if (typeof success === "function") {
					success(data);
				}
			} else {
				$.parseCode(data, function () {
					if (typeof error === "function") {
						error(data);
					}
				});
			}
			if (typeof complete === "function") {
				complete(data);
			}
		})
	};
	/**
	 * GET提交
	 * @param {String} actionURL AJAX请求地址
	 * @param {Function} success 成功回调函数
	 * @param {Function} error 失败回调函数
	 * @param {Function} complete 执行完成回调
	 */
	$.getInfo = function (actionURL, success, error, complete) {
		var actionURL = actionURL || document.URL,
			success   = success || function () {
				},
			error     = error || function () {
				},
			complete  = complete || function () {
				};
		if (actionURL.indexOf('?') === -1) {
			actionURL += '?___t=' + Math.random();
		} else {
			actionURL += '&___t=' + Math.random();
		}
		$.get(actionURL, function (data) {
			data = typeof data !== "object" ? {} : data;
			if (data.status) {
				if (typeof success === "function") {
					success(data);
				}
			} else {
				$.parseCode(data, function () {
					if (typeof error === "function") {
						error(data);
					}
				});
			}
			if (typeof complete === "function") {
				complete(data);
			}
		});
	};
	/**
	 * 解析URL
	 * @param url
	 * @returns {{source: *, protocol: string, host: (*|string), port: (*|string|Function), query: (number|*|string), params, file: *, hash: string, path: string, relative: *, segments: Array}}
	 */
	$.parseURL = function (url) {
		var a  = document.createElement('a');
		a.href = url;
		return {
			source   : url,
			protocol : a.protocol.replace(':', ''),
			host     : a.hostname,
			port     : a.port,
			query    : a.search,
			params   : (function () {
				var ret                 = {},
					seg                 = a.search.replace(/^\?/, '').split('&'),
					len = seg.length, i = 0, s;
				for (; i < len; i++) {
					if (!seg[i]) {
						continue;
					}
					s                             = seg[i].split('=');
					ret[decodeURIComponent(s[0])] = decodeURIComponent(s[1]);
				}
				return ret;
			})(),
			file     : (a.pathname.match(/\/([^\/?#]+)$/i) || [, ''])[1],
			hash     : a.hash.replace('#', ''),
			path     : a.pathname.replace(/^([^\/])/, '/$1'),
			relative : (a.href.match(/tps?:\/\/[^\/]+(.+)/) || [, ''])[1],
			segments : a.pathname.replace(/^\//, '').split('/')
		};
	};

	/**
	 * AJAX提交表单
	 * @param {Object} element 表单对象
	 * @param {Object} options
	 *        @object {Function} before 提交前的回调方法，返回FALSE为不提交
	 *        @object {Function} success 提交成功的回调方法，
	 *        @object {Function} error 提交失败的回调方法
	 *        @object {Function} after 提交完成的回调方法
	 * @returns {submitForm}
	 */
	var submitForm       = function (element, options) {
		var defaults  = {
			before  : function () {
			},
			success : function () {
			},
			error   : function () {
			},
			after   : function () {
			}
		};
		this.element  = element.removeAttr('onsubmit');
		this.settings = $.extend({}, defaults, options || {});
		this.params   = {};
		this.init();
		return this;
	};
	submitForm.prototype = {
		// 初始化
		init : function () {
			var me       = this;
			// 提交按钮
			me.submitBtn = me.element.find("[type='submit']").prop('disabled', false);
			// 绑定提交事件
			me.element.data('state', false).on('submit', function () {
				// 参数序列化
				me.params = me.element.serializeArray();
				// 提交前回调
				if (typeof me.settings.before === "function" && false === me.settings.before.call(me)) {
					return false;
				}
				// 提交状态
				if (me.element.data('state') === true) {
					return false;
				}
				me.element.data('state', true);
				// 提交
				var actionURL = me.element.attr('action') || document.URL;
				$.postInfo(actionURL, me.params, function (data) {
					if (typeof me.settings.success === "function") {
						me.settings.success.call(me, data);
					}
				}, function (data) {
					if (typeof me.settings.error === "function") {
						me.settings.error.call(me, data);
					}
				}, function (data) {
					me.element.data('state', false);
					me.submitBtn.prop('disabled', false);
					if (typeof me.settings.after === "function") {
						me.settings.after.call(me, data);
					}
				});
				return false;
			});
		}
	};
	/**
	 * AJAX提交表单
	 * @param options
	 * @returns {submitForm}
	 */
	$.fn.submitForm = function (options) {
		return new submitForm($(this), options);
	};
	/**
	 * 切换验证码
	 */
	$.fn.changeVerify = function () {
		$(this).each(function () {
			var $this = $(this);
			$this.data('src', $this.attr('src'));
		});
		$(this).on('click', function () {
			var $this = $(this),
				src   = $this.data('src');
			if (src.indexOf('?') !== -1) {
				src += '&___t=' + Math.random();
			} else {
				src += '?___t=' + Math.random();
			}
			$this.attr('src', src);
		});
		return this;
	};

	/**
	 * 滚动加载
	 * @param element
	 * @param url
	 * @param options
	 */
	var scrollLoadData = function (element, url, options) {
		this.element = $(element);
		this.url     = url || document.URL;

		var options   = options || {};
		var defaults  = {
			scrollTarget       : window, // 滚动容器对象
			documentTarget     : document, // 滚动文档对象
			defaultPage        : 0,   	// 默认分页码
			varPage            : 'p',  	// 默认分页标识
			moreTarget         : null,  // 更多显示容器对象
			morePendingMessage : '<div style="line-height: 40px; text-align: center; color: #999; font-size: 14px;">加载中，请稍后...</div>',
			moreEmptyMessage   : '<div style="line-height: 40px; text-align: center; color: #999; font-size: 14px;">没有更多内容了！</div>',
			dataFirstMessage   : '<div style="padding: 60px 0; text-align: center; color: #999; font-size: 14px;">正在加载中，请稍后...</div>',
			dataEmptyMessage   : '<div style="padding: 60px 0; text-align: center; color: #999; font-size: 14px;">暂无相关内容</div>',
			params             : {},
			timeout            : 0, 	// 滚动延迟执行时间
			parseDataCallback  : null   // 自定义数据解析回调
		};
		this.settings = $.extend({}, defaults, options);
		this.timer    = null;

		// 显示更多-容器对象
		if (typeof this.settings.moreTarget === 'string') {
			settings['moreTarget'] = $(this.settings.moreTarget);
		} else if (this.settings.moreTarget === null || !this.settings.moreTarget.length || undefined === this.settings.moreTarget) {
			$moreTarget = $('<div class="scroll-load-data-more" />');
			this.element.after($moreTarget);
			this.settings['moreTarget'] = $moreTarget;
		}

		// 布局状态
		this.nextLoadData = true;
		this.nextPage     = this.settings.defaultPage;
		this.pending      = false;
		this.settings.moreTarget.hide().html();
		var me = this;

		// 绑定滚动事件
		$(this.settings.scrollTarget).scroll(function (e) {
			clearTimeout(me.timer);
			var scrollTop    = $(this).scrollTop();
			var scrollHeight = $(me.settings.documentTarget).height();
			var windowHeight = $(this).height();
			if (scrollTop + windowHeight >= scrollHeight) {
				me.timer = setTimeout(function () {
					me.load(me.nextPage, false);
				}, me.settings.timeout);
			}
		});

		// 初始化数据
		this.load(this.nextPage, true);
	};

	scrollLoadData.prototype = {
		/**
		 * 初始化数据
		 * @param page 分页码
		 * @param isInit 是否初始化
		 */
		load : function (page, isInit) {
			var me = this;

			// 正在加载
			if (me.pending === true) {
				return false;
			}

			// 首次加载
			if (isInit) {
				me.nextPage     = me.settings.defaultPage;
				me.nextLoadData = true;
				me.element.html(me.settings.dataFirstMessage);
			} else {
				// 不再继续加载
				if (me.nextLoadData === false) {
					return false;
				}
				me.settings.moreTarget.show().html(me.settings.morePendingMessage);
			}

			// 开始加载
			me.pending                              = true;
			me.settings.params[me.settings.varPage] = me.nextPage;
			$.postInfo(me.url, me.settings.params, function (json) {
				var list;
				if (typeof me.settings.parseDataCallback === "function") {
					list = me.settings.parseDataCallback.call(me, json);
				} else {
					list = json.data.list;
				}

				// 分页参数+1
				page++;
				me.nextPage = page;

				// 首次加载
				if (isInit) {
					if (!$.trim(list)) {
						me.element.html(me.settings.dataEmptyMessage);
						me.nextLoadData = false;
						me.settings.moreTarget.hide().html('');
						return;
					} else {
						me.element.html(list);
					}
				} else {
					me.element.append(list);
				}

				// 不再继续加载
				if (!list) {
					me.nextLoadData = false;
					me.settings.moreTarget.show().html(me.settings.moreEmptyMessage);
					return;
				}

				// 按照总页数算出不再继续加载的状态
				var totalPage = parseInt(json.data.total_page);
				if (totalPage && page >= (totalPage + me.settings.defaultPage)) {
					me.nextLoadData = false;
					me.settings.moreTarget.show().html(me.settings.moreEmptyMessage);
				}

			}, function (json) {
				$.dialog.alert(json.info);
			}, function (json) {
				me.pending = false;
			});
		},

		/**
		 * 添加请求参数
		 * @param key
		 * @param value
		 */
		addParam : function (key, value) {
			this.settings.params[key] = value;
		}
	};

	/**
	 * 滚动加载
	 * @param url
	 * @param options
	 * @param value
	 */
	$.fn.scrollLoadData = function (url, options, value) {
		if (url == 'reload') {
			$(this).data('scrollLoadData').load(0, true);
		}
		else if (url == 'param') {
			$(this).data('scrollLoadData').addParam(options, value);
		}
		else {
			$(this).data('scrollLoadData', new scrollLoadData(this, url, options));
		}
		return true;
	};
})((window.jQuery || window.Zepto), window);