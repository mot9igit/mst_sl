shopLogistic.page.Params = function (config) {
    config = config || {};
    Ext.apply(config, {
        formpanel: 'shoplogistic-panel-params',
        cls: 'container',
        buttons: this.getButtons(),
        components: [{
            xtype: 'shoplogistic-panel-params'
        }]
    });
    shopLogistic.page.Params.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.page.Params, MODx.Component, {
    getButtons: function (config) {
        var b = [];

        if (MODx.perm.msorder_list) {
            b.push({
                text: _('ms2_shoplogistic'),
                id: 'sl-btn-orders',
                cls: 'primary-button',
                handler: function () {
                    MODx.loadPage('?', 'a=home&namespace=shoplogistic');
                }
            });
        }

        return b;
    }
});
Ext.reg('shoplogistic-page-params', shopLogistic.page.Params);