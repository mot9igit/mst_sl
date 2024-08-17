shopLogistic.window.CreateAssociation = function (config) {
    config = config || {};
    this.ident = config.ident || 'mecitem' + Ext.id();
    Ext.applyIf(config, {
        title: _('shoplogistic_menu_create'),
        width: 800,
        baseParams: {
            action: 'mgr/settings/association/create',
        },
    });
    shopLogistic.window.CreateAssociation.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateAssociation, shopLogistic.window.Default, {

    getFields: function (config) {
        return [
            {xtype: 'hidden', name: 'id', id: config.id + '-id'},
            {
                xtype: 'textfield',
                id: config.id + '-association',
                fieldLabel: _('shoplogistic_brand_association_association'),
                name: 'association',
                anchor: '99%',
            },{
                xtype: 'shoplogistic-combo-vendor',
                id: config.id + '-brand_id',
                fieldLabel: _('shoplogistic_brand_association_brand'),
                name: 'brand_id',
                anchor: '99%',
            },{
                xtype: 'textarea',
                id: config.id + '-description',
                fieldLabel: _('shoplogistic_brand_association_description'),
                name: 'description',
                anchor: '99%',
            }
        ];
    }

});
Ext.reg('shoplogistic-window-association-create', shopLogistic.window.CreateAssociation);


shopLogistic.window.UpdateAssociation = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_menu_update'),
        baseParams: {
            action: 'mgr/settings/association/update',
        },
    });
    shopLogistic.window.UpdateAssociation.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateAssociation, shopLogistic.window.CreateAssociation);
Ext.reg('shoplogistic-window-association-update', shopLogistic.window.UpdateAssociation);