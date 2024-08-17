shopLogistic.page.Orders = function (config) {
    config = config || {};
    Ext.apply(config, {
        formpanel: 'shoplogistic-panel-orders',
        cls: 'container',
        buttons: this.getButtons(config),
        components: [{
            xtype: 'shoplogistic-panel-orders'
        }]
    });
    shopLogistic.page.Orders.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.page.Orders, MODx.Component, {
    getButtons: function (config) {
        var b = [];

        /*if (MODx.perm.mssetting_list) {
            b.push({
                text: _('ms2_settings')
                ,id: 'ms2-abtn-settings'
                ,handler: function () {
                    MODx.loadPage('?', 'a=mgr/settings&namespace=shoplogistic');
                }
            });
        }*/

        return b;
    }
});
Ext.reg('shoplogistic-page-orders', shopLogistic.page.Orders);