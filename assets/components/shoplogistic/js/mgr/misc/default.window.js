shopLogistic.window.Default = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: '',
        url: shopLogistic.config['connector_url'],
        cls: 'shoplogistic-window' || config['cls'],
        width: 600,
        autoHeight: true,
        allowDrop: false,
        record: {},
        baseParams: {},
        fields: this.getFields(config),
        keys: this.getKeys(config),
        buttons: this.getButtons(config),
        listeners: this.getListeners(config),
    });
    shopLogistic.window.Default.superclass.constructor.call(this, config);

    this.on('hide', function () {
        var w = this;
        window.setTimeout(function () {
            w.close();
        }, 200);
    });
};
Ext.extend(shopLogistic.window.Default, MODx.Window, {

    getFields: function () {
        return [];
    },

    getButtons: function (config) {
        return [{
            text: config.cancelBtnText || _('shoplogistic_cancel'),
            scope: this,
            handler: function () {
                config.closeAction !== 'close'
                    ? this.hide()
                    : this.close();
            }
        }, {
            text: config.saveBtnText || _('shoplogistic_save'),
            cls: 'primary-button',
            scope: this,
            handler: this.submit,
        }];
    },

    getKeys: function () {
        return [{
            key: Ext.EventObject.ENTER,
            shift: true,
            fn: function () {
                this.submit();
            }, scope: this
        }];
    },

    getListeners: function () {
        return {};
    },

});
Ext.reg('shoplogistic-window-default', shopLogistic.window.Default);