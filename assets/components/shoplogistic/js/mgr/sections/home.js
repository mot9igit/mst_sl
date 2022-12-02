shopLogistic.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'shoplogistic-panel-home',
            renderTo: 'shoplogistic-panel-home-div'
        }]
    });
    shopLogistic.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.page.Home, MODx.Component);
Ext.reg('shoplogistic-page-home', shopLogistic.page.Home);