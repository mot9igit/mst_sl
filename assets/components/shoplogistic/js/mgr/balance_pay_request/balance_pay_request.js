shopLogistic.page.BalancePayRequestPage = function (config) {
    config = config || {};
    Ext.apply(config, {
        formpanel: 'shoplogistic-panel-balance-pay-request',
        cls: 'container',
        buttons: this.getButtons(config),
        components: [{
            xtype: 'shoplogistic-panel-balance-pay-request'
        }]
    });
    shopLogistic.page.BalancePayRequestPage.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.page.BalancePayRequestPage, MODx.Component, {
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
Ext.reg('shoplogistic-page-balance-pay-request', shopLogistic.page.BalancePayRequestPage);