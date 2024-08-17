shopLogistic.page.CardRequestPage = function (config) {
    config = config || {};
    Ext.apply(config, {
        formpanel: 'shoplogistic-panel-card-request',
        cls: 'container',
        buttons: this.getButtons(config),
        components: [{
            xtype: 'shoplogistic-panel-card-request'
        }]
    });
    shopLogistic.page.CardRequestPage.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.page.CardRequestPage, MODx.Component, {
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
Ext.reg('shoplogistic-page-card-request', shopLogistic.page.CardRequestPage);