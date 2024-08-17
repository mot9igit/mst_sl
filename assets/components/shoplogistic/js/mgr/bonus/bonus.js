shopLogistic.page.Bonus = function (config) {
    config = config || {};
    Ext.apply(config, {
        formpanel: 'shoplogistic-panel-bonus',
        cls: 'container',
        buttons: this.getButtons(),
        components: [{
            xtype: 'shoplogistic-panel-bonus'
        }]
    });
    shopLogistic.page.Bonus.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.page.Bonus, MODx.Component, {
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
Ext.reg('shoplogistic-gift-bonus', shopLogistic.page.Bonus);