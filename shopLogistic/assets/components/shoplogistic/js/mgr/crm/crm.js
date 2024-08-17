shopLogistic.page.CRM = function (config) {
    config = config || {};
    Ext.apply(config, {
        formpanel: 'shoplogistic-panel-crm',
        cls: 'container',
        buttons: this.getButtons(),
        components: [{
            xtype: 'shoplogistic-panel-crm'
        }]
    });
    shopLogistic.page.CRM.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.page.CRM, MODx.Component, {
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
Ext.reg('shoplogistic-page-crm', shopLogistic.page.CRM);