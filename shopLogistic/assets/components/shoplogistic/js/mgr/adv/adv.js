shopLogistic.page.Adv = function (config) {
    config = config || {};
    Ext.apply(config, {
        formpanel: 'shoplogistic-panel-adv',
        cls: 'container',
        buttons: this.getButtons(),
        components: [{
            xtype: 'shoplogistic-panel-adv'
        }]
    });
    shopLogistic.page.Adv.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.page.Adv, MODx.Component, {
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
Ext.reg('shoplogistic-page-adv', shopLogistic.page.Adv);