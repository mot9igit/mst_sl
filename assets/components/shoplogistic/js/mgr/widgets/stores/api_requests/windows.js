shopLogistic.window.UpdateAPIRequest = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_apirequest_update'),
        baseParams: {
            action: 'mgr/store/apirequest/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateAPIRequest.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateAPIRequest,  shopLogistic.window.Default, {
    getFields: function (config) {
        const default_fields = [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'hidden',
            name: 'store_id',
            id: config.id + '-store_id',
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_apirequest_method'),
            name: 'method',
            anchor: '99%',
            id: config.id + '-key'
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_apirequest_createdon'),
            name: 'createdon',
            anchor: '99%',
            id: config.id + '-key'
        }, {
            xtype: 'modx-texteditor',
            fieldLabel: _('shoplogistic_apirequest_request'),
            name: 'request_content',
            height: '300',
            anchor: '99%',
            id: config.id + '-request_content'
        }, {
            xtype: 'modx-texteditor',
            fieldLabel: _('shoplogistic_apirequest_response'),
            name: 'response_content',
            height: '300',
            id: config.id + '-response_content',
            anchor: '99%'
        }, {
            xtype: 'shoplogistic-combo-apirequest-status',
            fieldLabel: _('shoplogistic_apirequest_status'),
            name: 'status',
            id: config.id + '-status',
            anchor: '99%'
        }];
        return default_fields;
    }
});
Ext.reg('shoplogistic-window-store-apirequest-update', shopLogistic.window.UpdateAPIRequest);