shopLogistic.window.UpdateWarehouseDoc = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-window-warehouse-docs-update';
    }
    Ext.applyIf(config, {
        title: _('shoplogistic_stores_docs_update'),
        width: 550,
        autoHeight: true,
        url: shopLogistic.config.connector_url,
        action: 'mgr/warehouse_docs/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    shopLogistic.window.UpdateWarehouseDoc.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateWarehouseDoc, MODx.Window, {

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
                    name: 'warehouse_id',
                    id: config.id + '-warehouse_id',
                }, {
                    xtype: 'statictextfield',
                    fieldLabel: _('shoplogistic_stores_docs_guid'),
                    name: 'guid',
                    id: config.id + '-guid',
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
                    xtype: 'shoplogistic-grid-warehouse-docs-products',
                    record: config.record,
                }]
            }]
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('shoplogistic-window-warehouse-docs-update', shopLogistic.window.UpdateWarehouseDoc);