/**
 * Axis
 *
 * This file is part of Axis.
 *
 * Axis is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Axis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Axis.  If not, see <http://www.gnu.org/licenses/>.
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

(function($){
    $.fn.lightzoom = function(options){
        var settings = {
            zoom_stage_width: 250,                          //visible width of large image
            zoom_stage_height: 250,                         //visible height of large image
            zoom_stage_position: 'right',
            zoom_stage_offset_x: 10,
            zoom_stage_offset_y: 0,
            zoom_source_width: 250,                         //medium image width
            zoom_source_height: 250,                        //medium image height
            zoom_lens_opacity: 0.7,                         //css property
            zoom_cursor: 'crosshair',                       //css property
            zoom_on_trigger: 'mouseenter',                  //event
            zoom_off_trigger: 'mouseleave',                 //event

            lightbox_collection: '.more-views-list a',      //selector
            lightbox_trigger: 'click',                      //event
            lightbox_resize_speed: 800,                     //ms
            lightbox_fade_speed: 300,                       //ms
            lightbox_mask_opacity: 0.8,                     //css property
            lightbox_label_begin: 'Begin',
            lightbox_label_prev: 'Prev',
            lightbox_label_next: 'Next',
            lightbox_label_end: 'End',
            lightbox_label_close: 'Close',
            lightbox_label_title: 'Image %s of %s',

            switch_image_trigger: 'click',                  //event

            message_loading: 'Loading',
            message_loading_error: 'Loading error'
        };

        options = options || {};
        $.extend(settings, options);

        return this.each(function(){
            var link = {
                el: this,
                offset: {
                    left: $(this).offset().left,
                    top: $(this).offset().top
                }
            };
            var collection = new Collection();
            collection.set($(settings.lightbox_collection));
            var messenger = new Messenger();

            //zoom bind
            if (settings.zoom_on_trigger != 'none') {
                var zoomer = new Zoomer();
                $(this).css({
                    'position': 'relative',
                    'display': 'block'
                });
                $(this).bind(settings.zoom_on_trigger, function(){
                    zoomer.show(collection.get($(this).attr('href')));
                    return false;
                });
                $(this).bind(settings.zoom_off_trigger, function(){
                    zoomer.hide();
                    return false;
                });
            }

            //lightbox bind
            if (settings.lightbox_trigger != 'none') {
                var lightbox = new Lightbox();
                $(this).add(settings.lightbox_collection).bind(settings.lightbox_trigger, function(){
                    lightbox.set(collection.get($(this).attr('href'))).show();
                    return false;
                });
                //lightbox controls
                $('.begin', lightbox.container).click(function(){
                    lightbox.begin();
                });
                $('.prev', lightbox.container).click(function(){
                    lightbox.prev()
                });
                $('.next', lightbox.container).click(function(){
                    lightbox.next()
                });
                $('.end', lightbox.container).click(function(){
                    lightbox.end();
                });
                $('.close', lightbox.container).click(function(){
                    lightbox.hide();
                });
                $('.popup', lightbox.container).click(function(e){
                    e.preventDefault();
                    lightbox.popup();
                });
                $('.lightbox-mask').click(function(){
                    lightbox.hide();
                });
            }

            //switch image bind
            if (settings.switch_image_trigger != 'none') {
                var self = this;
                if (settings.switch_image_trigger == settings.lightbox_trigger) {
                    $(settings.lightbox_collection).unbind(settings.lightbox_trigger);
                }
                if (settings.switch_image_trigger != 'click') {
                    $(settings.lightbox_collection).bind('click', function(){
                        return false;
                    });
                }
                $(settings.lightbox_collection).bind(settings.switch_image_trigger, function(e){
                    switchImage(e);
                    return false;
                });
            }

            $(settings.lightbox_collection).hover(
                function(e) {
                    var el = e.target;
                    while (el.tagName != 'LI') {
                        el = $(el).parent().get(0);
                    }
                    $(el).addClass('over');
                },
                function(e) {
                    var el = e.target;
                    while (el.tagName != 'LI') {
                        el = $(el).parent().get(0);
                    }
                    $(el).removeClass('over');
                }
            );

            switchImage = function(e){
                var el = e.target;
                while (el.tagName != 'A') {
                    el = $(el).parent().get(0);
                }
                $(el).parent('li').siblings('li').removeClass('active');
                $(el).parent('li').addClass('active');
                $(link.el).attr({
                    'href'  : $(el).attr('href'),
                    'title' : el.title
                });
                var img = $(el).find('.image-medium').get(0);
                $(link.el).children('img').attr({
                    'src'   : $(img).attr('src'),
                    'title' : img.title,
                    'alt'   : img.alt
                });
                if (zoomer) {
                    zoomer.hide();
                }
            }

            function Zoomer(){
                this.lens = new Lens();
                this.stage = new Stage();

                this.show = function(image){
                    if (image.ready) {
                        this.process(image);
                    } else {
                        image.load({scope: this, method: this.process});
                    }
                }

                this.hide = function(){
                    this.lens.hide();
                    this.stage.hide();
                    $(link.el).unbind('mousemove');
                }

                this.process = function(image, scope){
                    var self = scope || this;
                    $(link.el).mousemove(function(e){
                        self.move.call(self, e);
                    });
                    self.lens.activate(image);
                    self.stage.activate(image);
                }

                this.move = function(e){
                    var container = $(this.lens.container);
                    var containerWidth = container.width()
                        + parseInt(container.css('borderLeftWidth'))
                        + parseInt(container.css('borderRightWidth'));
                    var containerHeight = container.height()
                        + parseInt(container.css('borderTopWidth'))
                        + parseInt(container.css('borderBottomWidth'));

                    var x = e.pageX;
                    var y = e.pageY;
                    var top = left = 0;
                    link.width = $(link.el).width();
                    link.height = $(link.el).height();

                    if ((y - containerHeight / 2) < link.offset.top) {
                        top = 0;
                    } else if ((y + containerHeight / 2) > link.offset.top + link.height) {
                        top = link.height - containerHeight;
                    } else {
                        top = y - link.offset.top - containerHeight / 2;
                    }

                    if ((x - containerWidth / 2) < link.offset.left) {
                        left = 0;
                    } else if ((x + containerWidth / 2) > link.offset.left + link.width) {
                        left = link.width - containerWidth;
                    } else {
                        left = x - link.offset.left - containerWidth / 2;
                    }

                    this.lens.move(left, top);
                    this.stage.move(left, top);
                    this.lens.show();
                    this.stage.show();
                }

                function Lens(){
                    this.container = document.createElement('div');
                    $(this.container).hide().css({
                        'position': 'absolute',
                        'left': 0,
                        'top': 0,
                        'opacity': settings.zoom_lens_opacity
                    }).addClass('lightzoom-lens');
                    $(link.el).append(this.container);

                    this.width = this.height = 0;

                    this.activate = function(image){
                        var self = this;
                        this.width = Math.round(settings.zoom_stage_width / (image.image.width / settings.zoom_source_width));
                        this.height = Math.round(settings.zoom_stage_height / (image.image.height / settings.zoom_source_height));
                        $(this.container).css({
                            'width': this.width > settings.zoom_stage_width ?
                                (settings.zoom_stage_width - parseInt($(this.container).css('borderLeftWidth')) - parseInt($(this.container).css('borderRightWidth'))) : this.width,
                            'height': this.height > settings.zoom_stage_height ?
                                (settings.zoom_stage_height - parseInt($(this.container).css('borderTopWidth')) - parseInt($(this.container).css('borderBottomWidth'))) : this.height,
                            'cursor': settings.zoom_cursor,
                            'left': 0,
                            'top': 0
                        });
                    };

                    this.show = function(){
                        $(this.container).show();
                    };

                    this.hide = function(){
                        $(this.container).hide();
                    };

                    this.move = function(left, top){
                        $(this.container).css({
                            'top': top,
                            'left': left
                        });
                    };
                }

                function Stage() {
                    this.container = document.createElement('div');
                    $(this.container).hide()
                        .css({
                            'position': 'absolute',
                            'left': 0,
                            'top': 0,
                            'width': settings.zoom_stage_width,
                            'height': settings.zoom_stage_height,
                            'overflow': 'hidden'
                        })
                        .html('<img alt="" style="position: absolute; top: 0; left: 0;">')
                        .addClass('lightzoom-stage');
                    document.body.appendChild(this.container);

                    var left = (settings.zoom_stage_position == 'left') ?
                        link.offset.left - settings.zoom_stage_width - settings.zoom_stage_offset_x :
                        link.offset.left + settings.zoom_source_width + settings.zoom_stage_offset_x
                    $(this.container).css({
                        'left': left,
                        'top': link.offset.top + settings.zoom_stage_offset_y
                             - parseInt($(this.container).css('borderTopWidth'))
                    });


                    this.image = $('img', this.container);

                    this.activate = function(image){
                        this.image.attr({
                            'src': image.src,
                            'alt': image.title
                        });
                    };

                    this.show = function(){
                        $(this.container).show();
                    };

                    this.hide = function(){
                        this.image.removeAttr('src');
                        this.image.attr({
                            'alt': ''
                        });
                        $(this.container).hide();
                    };

                    this.move = function(left, top){
                        this.image.css({
                            'top': - Math.round(top * this.image.height() / settings.zoom_source_height),
                            'left': - Math.round(left * this.image.width() / settings.zoom_source_width)
                        })
                    }
                }
            }

            function Lightbox(){
                this.template =
                    '<div class="lightbox-image">'+
                        '<img alt=""/>'+
                    '</div>'+
                    '<div class="lightbox-panel">'+
                        '<h4></h4>'+
                        '<p class="paging"></p>'+
                        '<div class="controls">'+
                            '<a href="javascript:void(0)" class="popup" title="'+settings.lightbox_label_popup+'">'+settings.lightbox_label_popup+'</a>'+
                            '<a href="javascript:void(0)" class="close" title="'+settings.lightbox_label_close+'">'+settings.lightbox_label_close+'</a>'+
                        '</div>'+
                    '</div>'+
                    '<div class="lightbox-controls">'+
                        '<a href="javascript:void(0)" class="begin" title="'+settings.lightbox_label_begin+'">'+settings.lightbox_label_begin+'</a>'+
                        '<a href="javascript:void(0)" class="prev" title="'+settings.lightbox_label_prev+'">'+settings.lightbox_label_prev+'</a>'+
                        '<a href="javascript:void(0)" class="next" title="'+settings.lightbox_label_next+'">'+settings.lightbox_label_next+'</a>'+
                        '<a href="javascript:void(0)" class="end" title="'+settings.lightbox_label_end+'">'+settings.lightbox_label_end+'</a>'+
                    '</div>';

                this.pageSize = BrowserWindow.getPageSize();
                this.viewportSize = BrowserWindow.getViewportSize();

                this.container = document.createElement('div');
                var left = this.viewportSize.width / 2 - 100,
                    top = 80;
                $(this.container).hide()
                    .css({
                        'position': 'absolute',
                        'left': left,
                        'top': top
                     })
                    .html(this.template)
                    .addClass('lightbox');
                document.body.appendChild(this.container);

                this.mask = document.createElement('div');
                $(this.mask).hide()
                    .css({
                        'width': this.pageSize.width,
                        'height': this.pageSize.height,
                        'opacity': 0
                    }).addClass('lightbox-mask');
                document.body.appendChild(this.mask);

                this.image = $('.lightbox-image img', this.container);

                this.show = function(){
                    this.setObserveKeyboard(true);
                    $('select, object, embed').addClass('lightbox-hidden');
                    $(this.container).show();
                    $(this.mask).show().fadeTo(settings.lightbox_fade_speed, settings.lightbox_mask_opacity);
                };

                this.hide = function(){
                    var self = this;
                    this.setObserveKeyboard(false);
                    this.image.fadeOut(settings.lightbox_fade_speed);
                    $(this.container).hide();
                    $(this.mask).fadeOut(settings.lightbox_fade_speed, function(){
                        $(self.mask).css({
                            'opacity': 0
                        }).hide();
                        $('select, object, embed').removeClass('lightbox-hidden');
                    })
                };

                this.popup = function(){
                    var src = this.image.attr('src');
                    window.open(src, 'lightzoompopup_' + src.replace(/[^a-z]/g, '_'));
                };

                this.set = function(image){
                    var self = this;
                    this.image.fadeOut(settings.lightbox_fade_speed, function(){
                        self.image.removeAttr('src');
                    });
                    if (image.ready) {
                        this.process(image);
                    } else {
                        image.load({scope: this, method: this.process});
                    }
                    return this;
                };

                this.process = function(image, scope){
                    var self         = scope || this,
                        scrollOffset = BrowserWindow.getScrollOffset(),
                        viewportSize = {
                            width : $(window).width(), // width, excluding scrollbar width
                            height: $(window).height()
                        };

                    // preventing the oversized image
                    var lightbox = self.image.parent().parent(),
                        offsetX  = parseInt(lightbox.css('padding-left'))
                                    + parseInt(lightbox.css('padding-right')),
                        offsetY  = parseInt(lightbox.css('padding-top'))
                                    + parseInt(lightbox.css('padding-bottom'))
                                    + 37 // control panel. It's hidden first time with height = 0;
                                    + 15,
                        width    = image['image'].width,
                        height   = image['image'].height,
                        ratio    = width / height,
                        topGap   = 15,
                        sideGap  = 0,
                        heightMode = false;

                    // fix width
                    if ((width + offsetX) >= viewportSize.width) {
                        sideGap = topGap * 2;
                        offsetX += sideGap;
                        width  = viewportSize.width - offsetX;
                        height = parseInt(width / ratio);
                    }

                    // fix height
                    if ((height + offsetY) >= viewportSize.height) {
                        heightMode = true;
                        offsetY += topGap;
                        height = viewportSize.height - offsetY;
                        width  = parseInt(height * ratio);
                    }

                    var top  = scrollOffset.top + viewportSize.height / 2
                                - (height + offsetY) / 2 - 100,
                        left = viewportSize.width / 2 - (width + offsetX) / 2;

                    top  = top < scrollOffset.top ? (scrollOffset.top + topGap) : top;
                    left = left <= 0 ? sideGap / 2 : left

                    self.image.parent().animate({
                        'width' : width,
                        'height': height
                    }, {
                        queue: false,
                        duration: settings.lightbox_resize_speed,
                        complete: function() {
                            var prop  = 'width',
                                value = width;
                            if (heightMode) {
                                prop  = 'height';
                                value = height;
                            }
                            self.image.css({
                                width : 'auto',
                                height: 'auto'
                            });
                            self.image.css(prop, value);

                            self.image.attr('src', image.src);
                            self.image.attr('alt', image.title);
                            self.image.fadeIn(settings.lightbox_fade_speed);
                            self.updateMask.apply(self);
                            self.updateWindow.apply(self);
                        }
                    });
                    $(self.container).animate({
                        'left': left,
                        'top': top
                    }, {
                        queue: false,
                        duration: settings.lightbox_resize_speed
                    });
                };

                this.updateWindow = function(){
                    $('.begin,.end,.prev,.next', this.container).removeClass('disabled');

                    $('.popup', this.container).attr('href', this.image.attr('src'));

                    if (collection.size <= 1) {
                        $('.begin,.end,.prev,.next', this.container).addClass('disabled');
                    } else if (collection.index == 0) {
                        $('.begin,.prev', this.container).addClass('disabled');
                    } else if (collection.index == (collection.size - 1)) {
                        $('.end,.next', this.container).addClass('disabled');
                    }

                    $('h4', this.container).html(collection.current().title);
                    $('.paging', this.container).html(
                        sprintf(
                            settings.lightbox_label_title,
                            collection.index + 1, collection.size
                        )
                    );
                };

                this.updateMask = function(){
                    $(this.mask).css({
                        'width': this.pageSize.width,
                        'height': this.pageSize.height
                    });
                    var pageSize = BrowserWindow.getPageSize();
                    $(this.mask).css({
                        'width': pageSize.width,
                        'height': pageSize.height
                    });
                };

                this.setObserveKeyboard = function(flag){
                    if (flag) {
                        $(document).keyup(function(e) {
                            var keyCode = e.keyCode;
                            if (keyCode == 27) {        // esc
                                lightbox.hide();
                            } else if (keyCode == 37) { // left arrow
                                if (e.ctrlKey) {
                                    lightbox.begin();
                                } else {
                                    lightbox.prev();
                                }
                            } else if (keyCode == 39) { // right arrow
                                if (e.ctrlKey) {
                                    lightbox.end();
                                } else {
                                    lightbox.next();
                                }
                            }
                        });
                    } else {
                        $(document).unbind('keydown');
                    }
                }

                this.begin = function(){
                    if (collection.index == 0) {
                        return this;
                    }
                    this.set(collection.begin());
                };
                this.next = function(){
                    if (collection.index == (collection.size - 1)) {
                        return this;
                    }
                    this.set(collection.next());
                };
                this.prev = function(){
                    if (collection.index == 0) {
                        return this;
                    }
                    this.set(collection.prev());
                };
                this.end = function(){
                    if (collection.index == (collection.size - 1)) {
                        return this;
                    }
                    this.set(collection.end());
                };
            }

            function Collection(){
                this.collection = [];
                this.size = 0;
                this.index = 0;

                this.get = function(key){
                    if (typeof key == 'indefined') {
                        return this.collection;
                    }
                    if (!this.collection[key]) {
                        this.add(key, key);
                    }
                    this.index = this.find(key);
                    return this.collection[key];
                };

                this.getAt = function(index){
                    index = (index > (this.size - 1)) ? this.size - 1 : (index < 0 ? 0 : index);
                    var j = 0;
                    this.index = index;
                    for (i in this.collection) {
                        if (index == j) {
                            break;
                        }
                        j++;
                    }
                    return this.collection[i];
                }

                this.find = function(key){
                    var j = 0;
                    for (i in this.collection) {
                        if (key == i) {
                            break;
                        }
                        j++;
                    }
                    return j;
                }

                this.set = function(collection){
                    var self = this;
                    collection.each(function(i, el){
                        self.add($(el).attr('href'), $(el).attr('href'), el.title);
                    })
                };

                this.add = function(key, value, title){
                    if (this.collection[key]) {
                        return this;
                    }
                    this.size++;
                    this.collection[key] = new _Image(value, title);
                    return this;
                };

                this.remove = function(key){
                    if (this.collection[key]) {
                        this.size--;
                        delete this.collection[key];
                    }
                    return this;
                };

                this.current = function(){
                    return this.getAt(this.index);
                }
                this.begin = function(){
                    return this.getAt(0);
                }
                this.prev = function(){
                    return this.getAt(--this.index);
                }
                this.next = function(){
                    return this.getAt(++this.index);
                }
                this.end = function(){
                    return this.getAt(this.size - 1);
                }
            }

            function _Image(src, title){
                this.src   = src;
                this.title = title;
                this.ready = false;
                this.image = new Image();
                this.image.alt = this.image.title = title;

                this.load = function(callback){
                    messenger.set(settings.message_loading, 'loading').show();
                    var self = this;
                    this.image.onload = function(){
                        self.onload(self, callback);
                    };
                    this.image.onerror = function(){
                        self.onerror(self);
                    };
                    this.image.src = this.src;
                };
                this.onload = function(self, callback){
                    messenger.hide();
                    self.ready = true;
                    callback.method(this, callback.scope);
                };
                this.onerror = function(self){
                    messenger.set(
                        settings.message_loading_error +
                        ':<br/>' +
                        self.src, 'error').show();
                    self.ready = false;
                }
            }

            function Messenger(){
                this.container = document.createElement('div');
                $(this.container).hide()
                    .css({
                        'position': 'absolute',
                        'left': link.offset.left + 20,
                        'top': link.offset.top + 20
                     })
                    .html('<p></p>')
                    .addClass('lightzoom-messenger');
                document.body.appendChild(this.container);

                this.show = function(){
                    $(this.container).show();
                };
                this.hide = function(){
                    $(this.container).hide();
                };
                this.set = function(message, type){
                    $('p', this.container).removeClass();
                    if (typeof type == 'string') {
                        $('p', this.container).addClass(type);
                    }
                    $('p', this.container).html(message);
                    return this;
                };
            }
        })
    }
})(jQuery);

function sprintf(){
    var args = Array.prototype.slice.call(arguments);
    var input = args.shift();
    var i = 0;
    while (args[i]) {
        input = input.replace(/%[s]/, args[i++]);
    }
    return input;
}