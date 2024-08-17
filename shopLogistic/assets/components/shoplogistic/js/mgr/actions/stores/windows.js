shopLogistic.window.CreateActionStore = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_action_store_create'),
        width: 900,
        baseParams: {
            action: 'mgr/actions/stores/create',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.CreateActionStore.superclass.constructor.call(this, config);
};

Ext.extend(shopLogistic.window.CreateActionStore, shopLogistic.window.Default, {
    getFields: function (config) {
        return shopLogistic.window.CreateActionStore.prototype.getFormFields.call(this, config);
    },
    getFormFields: function (config) {
        // console.log(config)
        var default_fields = [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id'
        }, {
            xtype: 'hidden',
            name: 'action_id',
            id: config.id + '-action_id'
        }, {
            xtype: 'shoplogistic-combo-store',
            fieldLabel: _('shoplogistic_action_store_store_name'),
            name: 'store_id',
            id: config.id + '-store_id',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_action_store_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_action_store_manual'),
            name: 'manual',
            id: config.id + '-manual',
            checked: true,
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_action_store_description'),
            name: 'description',
            id: config.id + '-description',
            height: 150,
            anchor: '99%'
        }];
        return default_fields
    },
});
Ext.reg('shoplogistic-actions-stores-window-create', shopLogistic.window.CreateActionStore);

shopLogistic.window.UpdateActionStore = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            title: _('shoplogistic_store_update'),
            width: 900,
            action: 'mgr/actions/stores/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateActionStore.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateActionStore, shopLogistic.window.CreateActionStore, {

    getFields: function (config) {
        return shopLogistic.window.CreateActionStore.prototype.getFormFields.call(this, config);
    }

});
Ext.reg('shoplogistic-actions-stores-window-update', shopLogistic.window.UpdateActionStore);
