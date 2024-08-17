shopLogistic.page.Parserdata = function (config) {
    config = config || {};
    Ext.apply(config, {
        formpanel: 'shoplogistic-panel-parserdata',
        cls: 'container',
        buttons: this.getButtons(config),
        components: [{
            xtype: 'shoplogistic-panel-parserdata'
        }]
    });
    shopLogistic.page.Parserdata.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.page.Parserdata, MODx.Component, {
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
Ext.reg('shoplogistic-page-parserdata', shopLogistic.page.Parserdata);