(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() : typeof define === 'function' && define.amd ? define(factory) : (global = global || self, global.gwm = factory());
}(this, (function () {
    'use strict';

    var Watermark = (function () {
        function Watermark(options) {
            options          = options || {};
            this.txt         = options.txt || new Date().toLocaleDateString() + " Top secret";
            this.width       = options.width || 158;
            this.height      = options.height || 100;
            this.x           = options.x || 0;
            this.y           = options.y || 0;
            this.font        = options.font || 'sans-serif';
            this.fontSize    = options.fontSize || 12;
            this.color       = options.color || '#000000';
            this.alpha       = options.alpha || 0.1;
            this.angle       = options.angle || 0;
            this.image       = options.image || '';
            this.imageX      = options.imageX || 0;
            this.imageY      = options.imageY || 0;
            this.imageWidth  = options.imageWidth || 200;
            this.imageHeight = options.imageHeight || 50;
        }

        return Watermark;
    }());

    var SvgWay = (function () {
        function SvgWay(watermark) {
            this.watermark = watermark;
        }

        SvgWay.prototype.render = function () {
            var config = this.watermark;
            var image  = '';
            if (config.image) {
                image = '<image opacity="' + config.alpha + '" transform="rotate(' + config.angle + ',' + config.imageX + ',' + config.imageY + ')" x="' + config.imageX + '" y="' + config.imageY + '" width="' + config.imageWidth + '" height="' + config.imageHeight + '" href="' + config.image + '"></image>';
            }
            var svgStr = '<svg xmlns="http://www.w3.org/2000/svg" width="' + config.width + '" height="' + config.height + '">\
<foreignObject opacity="' + config.alpha + '" x="' + config.x + '" y="' + config.y + '" width="' + config.width + '" height="' + config.height + '" transform="rotate(' + config.angle + ',' + config.x + ',' + config.y + ')">\
    <div xmlns="http://www.w3.org/1999/xhtml" style="text-align: center; font-size: ' + config.fontSize + 'px; font-family: ' + config.font + '; color: ' + config.color + '">' + config.txt + '</div>\
</foreignObject>\
' + image + '\
</svg>'
            return "data:image/svg+xml;base64," + window.btoa(unescape(encodeURIComponent(svgStr)));
        };
        return SvgWay;
    }());

    var CanvasWay = (function () {
        function CanvasWay(watermark) {
            this.watermark = watermark;
            var width      = watermark.width, height = watermark.height;
            this.canvas    = document.createElement('canvas');
            this.canvas.setAttribute('width', "" + width);
            this.canvas.setAttribute('height', "" + height);
        }

        CanvasWay.prototype.render = function () {
            var config = this.watermark;
            var ctx    = this.canvas.getContext('2d');
            if (ctx === null) {
                throw new Error('getContext error');
            }
            ctx.clearRect(0, 0, config.width, config.height);
            ctx.textBaseline = 'top';
            ctx.textAlign    = 'left';
            ctx.fillStyle    = config.color;
            ctx.globalAlpha  = config.alpha;
            ctx.font         = config.fontSize + "px " + config.font;
            ctx.translate(config.x, config.y);
            ctx.rotate((Math.PI / 180) * config.angle);
            ctx.translate(-config.x, -config.y - config.fontSize);
            ctx.fillText(config.txt, config.x, config.y + config.fontSize);
            if (config.image) {
                ctx.drawImage(config.image, config.imageX, config.imageY, config.imageWidth, config.imageHeight);
            }
            return this.canvas.toDataURL();
        };
        return CanvasWay;
    }());

    /*! *****************************************************************************
    Copyright (c) Microsoft Corporation. All rights reserved.
    Licensed under the Apache License, Version 2.0 (the "License"); you may not use
    this file except in compliance with the License. You may obtain a copy of the
    License at http://www.apache.org/licenses/LICENSE-2.0

    THIS CODE IS PROVIDED ON AN *AS IS* BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
    KIND, EITHER EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION ANY IMPLIED
    WARRANTIES OR CONDITIONS OF TITLE, FITNESS FOR A PARTICULAR PURPOSE,
    MERCHANTABLITY OR NON-INFRINGEMENT.

    See the Apache Version 2.0 License for specific language governing permissions
    and limitations under the License.
    ***************************************************************************** */

    function __spreadArrays() {
        for (var s = 0, i = 0, il = arguments.length; i < il; i++) s += arguments[i].length;
        for (var r = Array(s), k = 0, i = 0; i < il; i++) for (var a = arguments[i], j = 0, jl = a.length; j < jl; j++, k++) r[k] = a[j];
        return r;
    }

    var humpToLine = function (name) {
        return name.replace(/([A-Z])/g, "_$1").toLowerCase();
    };

    var isSupport   = function (attribute) {
        return attribute in document.documentElement.style;
    };
    var assignStyle = function () {
        var args = [];
        for (var _i = 0; _i < arguments.length; _i++) {
            args[_i] = arguments[_i];
        }
        var _a       = args.filter(function (item) {
            return item;
        }), oldStyle = _a[0], newStyles = _a.slice(1);
        return Object.assign.apply(null, __spreadArrays([oldStyle], newStyles));
    };
    var bindCSS     = (function (elem, css, priority) {
        assignStyle(elem.style, css);

        for (var key in css) {
            if (priority === 'normal') {
                elem.style[key] = css[key];
                continue;
            }
            if (elem.style.setProperty) {
                elem.style.setProperty(key, css[key], 'important');
                continue;
            }
            var oldStyle = elem.getAttribute('style');
            var newStyle = [humpToLine(key), css[key] + "!important;"].join(':');
            elem.setAttribute('style', [oldStyle, newStyle].join(' ').trim());
        }
        return elem;
    });

    var ElementWay = (function () {
        function ElementWay(watermark) {
            this.watermark = watermark;
        }

        ElementWay.prototype.createItem = function () {
            var config = this.watermark;
            var item   = document.createElement('div');
            bindCSS(item, {
                position      : 'relative',
                width         : config.width + 'px',
                height        : config.height + 'px',
                flex          : "0 0 " + config.width + "px",
                overflow      : 'hidden',
                pointerEvents : 'none',
            }, 'normal');
            var span       = document.createElement('div');
            span.innerHTML = config.txt;
            bindCSS(span, {
                position        : 'absolute',
                top             : config.y + "px",
                left            : config.x + "px",
                fontFamily      : config.font,
                fontSize        : config.fontSize + "px",
                color           : config.color,
                lineHeight      : 1.5,
                opacity         : config.alpha,
                fontWeight      : 400,
                transform       : "rotate(" + config.angle + "deg)",
                transformOrigin : '0 0',
                userSelect      : 'none',
                whiteSpace      : 'nowrap',
                overflow        : 'hidden',
                textAlign       : 'center',
            }, 'normal');
            item.appendChild(span);
            if (config.image) {
                var image = document.createElement('img');
                image.src = config.image;
                bindCSS(image, {
                    position        : 'absolute',
                    top             : config.imageY + "px",
                    left            : config.imageX + "px",
                    width           : config.imageWidth + "px",
                    height          : config.imageHeight + "px",
                    opacity         : config.alpha,
                    transform       : "rotate(" + config.angle + "deg)",
                    transformOrigin : '0 0',
                    userSelect      : 'none',
                    whiteSpace      : 'nowrap',
                    overflow        : 'hidden',
                });
                item.appendChild(image);
            }
            return item;
        };
        ElementWay.prototype.render     = function () {
            var i            = 0;
            var config       = this.watermark;
            var _b           = document.documentElement || document.body;
            var clientWidth  = _b.clientWidth;
            var clientHeight = _b.clientHeight;
            var column       = Math.ceil(clientWidth / config.width);
            var rows         = Math.ceil(clientHeight / config.height);
            var wrap         = document.createElement('div');
            bindCSS(wrap, {
                display  : 'flex',
                flexWrap : 'wrap',
                width    : config.width * column + "px",
                height   : config.height * rows + "px",
            }, 'normal');
            for (; i < column * rows; i++) {
                wrap.appendChild(this.createItem());
            }
            return wrap;
        };
        return ElementWay;
    }());

    var mutationObserver = MutationObserver || WebKitMutationObserver || MozMutationObserver;

    function bindMutationEvent(target, container, callback) {
        var eventList = [
            'DOMAttrModified', 'DOMAttributeNameChanged', 'DOMCharacterDataModified', 'DOMElementNameChanged',
            'DOMNodeInserted', 'DOMNodeInsertedIntoDocument', 'DOMNodeRemoved', 'DOMNodeRemovedFromDocument',
            'DOMSubtreeModified',
        ];
        eventList.map(function (eventName) {
            return target.addEventListener(eventName, function () {
                return callback();
            }, false);
        });
        document.body.addEventListener('DOMSubtreeModified', function () {
            return callback();
        }, false);
        return {
            containerObserver : {
                disconnect : function () {
                    return container.removeEventListener('DOMSubtreeModified', function () {
                        return callback();
                    }, false);
                },
            },
            targetObserver    : {
                disconnect : function () {
                    return eventList.map(function (eventName) {
                        return target.removeEventListener(eventName, function () {
                            return callback();
                        }, false);
                    });
                },
            },
        };
    }

    var observer   = function (target, container, callback) {
        if (!mutationObserver) {
            return bindMutationEvent(target, container, callback);
        }
        var containerObserver = new mutationObserver(function (mutationsList) {
            mutationsList.forEach(function (mutation) {
                mutation.removedNodes.forEach(function (item) {
                    if (item === target) {
                        callback();
                    }
                });
            });
        });
        containerObserver.observe(container, {childList : true});
        var targetObserver = new MutationObserver(callback);
        targetObserver.observe(target, {
            characterData : true,
            attributes    : true,
            childList     : true,
            subtree       : true
        });
        return {
            containerObserver : containerObserver,
            targetObserver    : targetObserver
        };
    };
    var disconnect = function (currentObserver) {
        var containerObserver = currentObserver.containerObserver, targetObserver = currentObserver.targetObserver;
        containerObserver.disconnect();
        targetObserver.disconnect();
    };
    var creator    = (function (gwm) {
        var gwmDom = gwm.gwmDom;
        var css    = gwm.opts.css || {};
        if (gwmDom) {
            gwmDom.remove();
        }
        var gwmDiv = document.createElement('div');
        if (isSupport('pointerEvents')) {
            css.pointerEvents = 'none';
            css.zIndex        = parseInt("" + css.zIndex, 10) > 0 ? css.zIndex : '2147483647';
        }
        bindCSS(gwmDiv, css);
        return gwmDiv;
    });

    var DEFAULT_STYLE = {
        position         : 'fixed',
        top              : 0,
        right            : 0,
        bottom           : 0,
        left             : 0,
        overflow         : 'hidden',
        zIndex           : -10,
        backgroundRepeat : 'no-repeat',
        display          : 'block',
        opacity          : '1',
    };

    var WatermarkType;
    (function (WatermarkType) {
        WatermarkType["CANVAS"]  = "canvas";
        WatermarkType["SVG"]     = "svg";
        WatermarkType["ELEMENT"] = "element";
    })(WatermarkType || (WatermarkType = {}));

    var wayFactory = function (mode, wm) {
        switch (mode) {
            case WatermarkType.CANVAS:
                return new CanvasWay(wm);
            case WatermarkType.SVG:
                return new SvgWay(wm);
            default:
                return new ElementWay(wm);
        }
    };
    var getElement = function (container) {
        if (typeof container === 'string') {
            var dom = document.querySelector(container);
            if (dom) {
                return dom;
            }
            return document.body;
        }
        return container;
    };

    var GenerateWatermark = (function () {
        function GenerateWatermark() {
        }

        GenerateWatermark.prototype.creation  = function (opts) {
            var me        = this;
            this.opts     = opts;
            this.opts.css = assignStyle(DEFAULT_STYLE, opts.css);
            this.cancel();

            var mode     = opts.mode;
            var watch    = opts.watch;
            var observer = function () {
                if (watch === true) {
                    me.observer = me.observing();
                }
            }
            var way      = function () {
                if (mode === WatermarkType.CANVAS && me.opts.image) {
                    var image    = new Image();
                    image.src    = me.opts.image;
                    image.onload = function () {
                        opts.image                 = this;
                        me.gwmDom.style.background = "url(\"" + wayFactory(WatermarkType.CANVAS, new Watermark(opts)).render() + "\")";
                        observer();
                    }
                } else if (mode === WatermarkType.SVG && me.opts.image) {
                    var image    = new Image();
                    image.src    = me.opts.image;
                    image.onload = function () {
                        var canvas    = document.createElement("canvas");
                        canvas.width  = this.width;
                        canvas.height = this.height;

                        var ctx = canvas.getContext("2d");
                        ctx.drawImage(this, 0, 0, this.width, this.height);
                        var ext    = this.src.substring(this.src.lastIndexOf(".") + 1).toLowerCase();
                        opts.image = canvas.toDataURL("image/" + ext);

                        me.gwmDom.style.background = "url(\"" + wayFactory(WatermarkType.SVG, new Watermark(opts)).render() + "\")";
                        observer();
                    }
                } else {
                    var impl   = wayFactory(mode || WatermarkType.SVG, new Watermark(opts));
                    var result = impl.render();
                    if (mode === WatermarkType.ELEMENT) {
                        me.gwmDom.appendChild(result);
                    } else {
                        me.gwmDom.style.background = "url(\"" + result + "\")";
                    }
                    observer();
                }
            }

            this.wrap = getElement(opts.container || document.body);
            if (this.wrap !== document.body) {
                this.opts.css.position = 'absolute';
                bindCSS(this.wrap, {
                    position : 'relative'
                });
            }

            this.gwmDom = creator(this);
            way();

            var first = this.wrap.firstChild;
            if (first) {
                this.wrap.insertBefore(this.gwmDom, first);
            } else {
                this.wrap.appendChild(this.gwmDom);
            }
        };
        GenerateWatermark.prototype.observing = function () {
            var _this = this;
            return observer(this.gwmDom, this.wrap, function () {
                return _this.creation(_this.opts);
            });
        };
        GenerateWatermark.prototype.cancel    = function () {
            if (this.observer) {
                disconnect(this.observer);
            }
            if (this.gwmDom) {
                try {
                    this.wrap.removeChild(this.gwmDom);
                } catch (e) {
                }
            }
        };
        return GenerateWatermark;
    }());

    return new GenerateWatermark();
})));