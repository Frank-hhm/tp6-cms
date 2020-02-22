/*
* @Author: 小明
* @Date:   2020-01-05 21:56:13
* @Last Modified by:   根子科技
* @Last Modified time: 2020-01-05 21:56:42
*/
;(function ($, window, document, undefined) {

    /**
     * 文件库模块
     * @param trigger
     * @param options
     * @constructor
     */
    function FileLibrary(trigger, options) {
        // 配置项
        var defaults = {
            type: 'image',
            layerId: 'file-library'
            , layerSkin: 'file-library'
        };
        this.options = $.extend({}, defaults, options);
        // 触发对象
        this.$trigger = trigger;
        this.$touch = null; // 当前触发元素
        // 容器元素
        this.$element = null;
        // 初始化对象事件
        this.init();
    }

    FileLibrary.prototype = {

        data: {
            selectedList: []
        },

        /**
         * 初始化
         */
        init: function () {
            var _this = this;
            // 打开文件库事件
            _this.triggerEvent();
        },

        /**
         * 打开文件库事件
         */
        triggerEvent: function () {
            var _this = this;
            if (_this.$trigger !== false) {
                // 点击开启文件库弹窗
                _this.$trigger.unbind().click(function () {
                    _this.$touch = $(this);
                    _this.showLibraryModal();
                });
            } else {
                _this.showLibraryModal();
            }
        },

        /**
         * 显示文件库弹窗
         */
        showLibraryModal: function () {
            var _this = this;
            _this.getJsonData({group_id: -1}, function (data) {
                data.is_default = true;
                // 捕获页
                layer.open({
                    type: 1
                    , id: _this.options.layerId
                    , title: '图片库'
                    , skin: _this.options.layerSkin
                    , area: '840px'
                    , offset: 'auto'
                    , anim: 1
                    , closeBtn: 1
                    , shade: 0.3
                    , btn: ['确定', '取消']
                    , content: template('tpl-file-library', data)
                    , success: function (layero) {
                        // 初始化文件库弹窗
                        _this.initModal(layero);
                    }
                    , yes: function (index) {
                        // 确认回调
                        _this.done();
                        layer.close(index);
                    }
                });
            });
        },


        /**
         * 确认回调
         */
        done: function () {
            var selectedList = this.getSelectedFiles();
            selectedList.length > 0 && typeof this.options.done === 'function'
            && this.options.done(selectedList, this.$touch);
        }

    };

    // 在Jquery插件中使用FileLibrary对象
    $.fn.fileLibrary = function (options) {
        new FileLibrary(this, options);
    };

    // 在Jquery插件中使用FileLibrary对象
    $.fileLibrary = function (options) {
        new FileLibrary(false, options);
    };

})(jQuery, window, document);
