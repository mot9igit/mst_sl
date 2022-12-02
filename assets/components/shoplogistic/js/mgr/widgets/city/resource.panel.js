shopLogistic.panel.Resource = function (config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'shoplogistic-panel-resource',
        autoHeight: true,
        layout: 'form',
        anchor: '99%',
        items: [{
            xtype: 'shoplogistic-grid-resource',
            cls: 'main-wrapper',
            record: config.record
        }]
    });
    shopLogistic.panel.Resource.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.Resource, MODx.Panel);
Ext.reg('shoplogistic-panel-resource', shopLogistic.panel.Resource);
