shopLogistic.window.UpdateStoreDoc = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-window-stores-docs-update';
    }
    Ext.applyIf(config, {
        title: _('shoplogistic_stores_docs_update'),
        width: 550,
        autoHeight: true,
        url: shopLogistic.config.connector_url,
        action: 'mgr/stores_docs/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    shopLogistic.window.UpdateStoreDoc.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateStoreDoc, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_stores_docs_update'),
                layout: 'form',
                items: [{
                    xtype: 'hidden',
                    name: 'id',
                    id: config.id + '-id',
                },{
                    xtype: 'hidden',
                    name: 'store_id',
                    id: config.id + '-store_id',
                }, {
                    xtype: 'statictextfield',
                    fieldLabel: _('shoplogistic_stores_docs_guid'),
                    name: 'guid',
                    id: config.id + '-guid',
                    anchor: '99%'
                }, {
                    xtype: 'statictextfield',
                    fieldLabel: _('shoplogistic_stores_docs_phone'),
                    name: 'phone',
                    id: config.id + '-phone',
                    anchor: '99%'
                }, {
                    xtype: 'statictextfield',
                    fieldLabel: _('shoplogistic_stores_docs_base_guid'),
                    name: 'base_guid',
                    id: config.id + '-base_guid',
                    anchor: '99%'
                }, {
                    xtype: 'statictextfield',
                    fieldLabel: _('shoplogistic_stores_docs_createdon'),
                    name: 'createdon',
                    id: config.id + '-createdon',
                    anchor: '99%'
                }, {
                    xtype: 'statictextfield',
                    fieldLabel: _('shoplogistic_stores_docs_doc_number'),
                    name: 'doc_number',
                    id: config.id + '-doc_number',
                    anchor: '99%'
                }, {
                    xtype: 'textarea',
                    fieldLabel: _('shoplogistic_stores_docs_description'),
                    name: 'description',
                    id: config.id + '-description',
                    anchor: '99%'
                }]
            },{
                title: _('shoplogistic_stores_docs_products'),
                items: [{
                    xtype: 'shoplogistic-grid-stores-docs-products',
                    record: config.record,
                }]
            }]
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('shoplogistic-window-stores-docs-update', shopLogistic.window.UpdateStoreDoc);