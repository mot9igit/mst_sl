shopLogistic.window.CreateRequests = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_request_create'),
        width: 600,
        baseParams: {
            action: 'mgr/adv/requests/create',
        },
    });
    shopLogistic.window.CreateRequests.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateRequests, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_request_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'htmleditor',
            fieldLabel: _('shoplogistic_request_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        },{
            xtype: 'dart-image-field',
            fieldLabel: _('shoplogistic_request_image'),
            name: 'image',
            id: config.id + '-image',
            anchor: '99%'
        },{
            xtype: 'dart-image-field',
            fieldLabel: _('shoplogistic_request_image_inner'),
            name: 'image_inner',
            id: config.id + '-image_inner',
            anchor: '99%'
        },{
            xtype: 'dart-image-field',
            fieldLabel: _('shoplogistic_request_image_small'),
            name: 'image_small',
            id: config.id + '-image_small',
            anchor: '99%'
        }, {
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
        },{
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_request_store_name'),
            name: 'store',
            id: config.id + '-store',
            anchor: '99%'
        },
        {
            xtype: 'shoplogistic-combo-adv-places',
            fieldLabel: _('shoplogistic_request_places'),
            name: 'page_places',
            id: config.id + '-page_places',
            anchor: '99%'
        }, {
            xtype: 'numberfield',
            fieldLabel: _('shoplogistic_request_position'),
            name: 'page_place_position',
            id: config.id + '-page_place_position',
            anchor: '99%'
        },{
            xtype: 'shoplogistic-combo-action-status',
            fieldLabel: _('shoplogistic_request_action_group'),
            hiddenName: 'status',
            anchor: '99%',
            id: config.id + '-status'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_request_comment'),
            name: 'moderator_comment',
            id: config.id + '-moderator_comment',
            anchor: '99%'
        }];
    },
});
Ext.reg('shoplogistic-window-request-create', shopLogistic.window.CreateRequests);


shopLogistic.window.UpdateRequests = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_request_update'),
        baseParams: {
            action: 'mgr/adv/requests/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateRequests.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateRequests, shopLogistic.window.CreateRequests, {

    getFields: function (config) {
        return shopLogistic.window.CreateRequests.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-request-update', shopLogistic.window.UpdateRequests);