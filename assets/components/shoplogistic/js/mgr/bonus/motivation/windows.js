shopLogistic.window.CreateMotivation = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_motivation_create'),
        width: 600,
        baseParams: {
            action: 'mgr/bonus/motivation/create',
        },
    });
    shopLogistic.window.CreateMotivation.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateMotivation, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_motivation_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'modx-combo-browser',
            fieldLabel: _('shoplogistic_motivation_image'),
            name: 'image',
            id: config.id + '-image',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_motivation_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_motivation_description_gifts'),
            name: 'description_gifts',
            id: config.id + '-description_gifts',
            anchor: '99%'
        }, {
            xtype: 'shoplogistic-combo-gift',
            fieldLabel: _('shoplogistic_motivation_gift'),
            name: 'gifts',
            id: config.id + '-gifts',
            anchor: '99%'
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_motivation_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        },{
            title: _('shoplogistic_motivation_available'),
            cls: 'def-panel',
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'shoplogistic-xdatetime',
                    fieldLabel: _('shoplogistic_motivation_date_from'),
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
                    fieldLabel: _('shoplogistic_motivation_date_to'),
                    name: 'date_to',
                    id: config.id + '-date_to',
                    anchor: '99%'
                }]
            }]
        },
        {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_motivation_percent'),
            name: 'percent',
            id: config.id + '-percent',
            anchor: '99%'
        },
        {
            title: _('shoplogistic_motivation_available_store'),
            cls: 'def-panel',
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                items: [{
                    xtype: 'shoplogistic-combo-stores',
                    fieldLabel: _('shoplogistic_motivation_stores'),
                    name: 'store_ids',
                    id: config.id + '-store_ids',
                    value: config.record['store_ids'],
                    anchor: '99%'
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('shoplogistic_motivation_global'),
                    name: 'global',
                    id: config.id + '-global',
                    anchor: '99%'
                }]
            }]
        },
        ];
    },
});
Ext.reg('shoplogistic-window-motivation-create', shopLogistic.window.CreateMotivation);


shopLogistic.window.UpdateMotivation = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_motivation_update'),
        baseParams: {
            action: 'mgr/bonus/motivation/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateMotivation.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateMotivation, shopLogistic.window.CreateMotivation, {

    getFields: function (config) {
        return shopLogistic.window.CreateMotivation.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-motivation-update', shopLogistic.window.UpdateMotivation);