shopLogistic.window.CreateAction = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_action_create'),
        width: 900,
        baseParams: {
            action: 'mgr/actions/items/create',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.CreateAction.superclass.constructor.call(this, config);
};

Ext.extend(shopLogistic.window.CreateAction, shopLogistic.window.Default, {
    getFields: function (config) {
        console.log(config)
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_action_create'),
                layout: 'form',
                items: shopLogistic.window.CreateAction.prototype.getFormFields.call(this, config)
            }]
        }]
    },
    getFormFields: function (config) {
        // console.log(config)
        var default_fields = [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_action_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'modx-combo-browser',
            fieldLabel: _('shoplogistic_action_image'),
            name: 'image',
            id: config.id + '-image',
            anchor: '99%',
            allowBlank: true,
        }, {
            xtype: 'modx-combo-browser',
            fieldLabel: _('shoplogistic_action_image_inner'),
            name: 'image_inner',
            id: config.id + '-image_inner',
            anchor: '99%',
            allowBlank: true,
        }, {
            xtype: 'shoplogistic-combo-store',
            fieldLabel: _('shoplogistic_action_store_name'),
            name: 'store_id',
            id: config.id + '-store_id',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_action_global'),
            name: 'global',
            id: config.id + '-global',
            checked: false,
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_action_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        },{
            title: _('shoplogistic_action_available'),
            cls: 'def-panel',
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'shoplogistic-xdatetime',
                    fieldLabel: _('shoplogistic_action_date_from'),
                    name: 'date_from',
                    id: config.id + '-date_from',
                    anchor: '99%'
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'shoplogistic-xdatetime',
                    fieldLabel: _('shoplogistic_action_date_to'),
                    name: 'date_to',
                    id: config.id + '-date_to',
                    anchor: '99%'
                }]
            }]
        },{
            title: _('shoplogistic_action_available_for'),
            cls: 'def-panel',
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                cls: 'no-margin',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'shoplogistic-combo-regions',
                    fieldLabel: _('shoplogistic_action_regions'),
                    name: 'regions',
                    id: 'shoplogistic-actions-regions',
                    anchor: '99%'
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'shoplogistic-combo-cities',
                    fieldLabel: _('shoplogistic_action_cities'),
                    name: 'cities',
                    id: 'shoplogistic-actions-cities',
                    anchor: '99%'
                }]
            }]
        },{
            xtype: 'shoplogistic-combo-resource',
            fieldLabel: _('shoplogistic_action_resource'),
            name: 'resource',
            hiddenName: "resource",
            id: config.id + '-resource',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_action_description'),
            name: 'description',
            id: config.id + '-description',
            height: 150,
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_action_content'),
            name: 'content',
            cls: 'modx-richtext',
            id: 'shoplogistic-actions-window-content',
            height: 150,
            anchor: '99%',
            listeners: {
                render: function () {
                    MODx.loadRTE("shoplogistic-actions-window-content"); // id поля
                }
            }
        }];
        return default_fields
    },
});
Ext.reg('shoplogistic-actions-window-create', shopLogistic.window.CreateAction);

shopLogistic.window.UpdateAction = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            title: _('shoplogistic_store_update'),
            width: 900,
            action: 'mgr/actions/items/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateAction.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateAction, shopLogistic.window.CreateAction, {

    getFields: function (config) {
        var title = _('shoplogistic_action_update');
        var default_tabs = [{
            title: title,
            layout: 'form',
            id: "action-update",
            items: shopLogistic.window.CreateAction.prototype.getFormFields.call(this, config),
        }, {
            title: _('shoplogistic_actions_products'),
            items: [{
                xtype: 'shoplogistic-actions-products-grid',
                record: config.record,
            }]
        }, {
            title: _('shoplogistic_actions_stores'),
            items: [{
                xtype: 'shoplogistic-actions-stores-grid',
                record: config.record,
            }]
        }];
        return [{
            xtype: 'modx-tabs',
            items: default_tabs
        }];
    }

});
Ext.reg('shoplogistic-actions-window-update', shopLogistic.window.UpdateAction);
