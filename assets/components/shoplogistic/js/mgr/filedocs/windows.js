shopLogistic.window.CreateFilesDoc = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_files_docs_create'),
        width: 600,
        baseParams: {
            action: 'mgr/docs/create',
        },
    });
    shopLogistic.window.CreateFilesDoc.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateFilesDoc, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'shoplogistic-combo-stores',
            fieldLabel: _('shoplogistic_doc_store_id'),
            name: 'store_ids',
            anchor: '99%',
            id: config.id + '-store_ids'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_doc_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'shoplogistic-combo-docstatus',
            fieldLabel: _('shoplogistic_doc_status'),
            name: 'status',
            anchor: '99%',
            id: config.id + '-status'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_stores_docs_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }, {
            xtype: 'modx-combo-browser',
            fieldLabel: _('shoplogistic_doc_file'),
            name: 'file',
            id: config.id + '-file',
            anchor: '99%'
        }, {
            xtype: 'xdatetime',
            fieldLabel: _('shoplogistic_doc_date'),
            name: 'date',
            id: config.id + '-date',
            anchor: '99%'
        },{
            xtype: 'xcheckbox',
            id: config.id + '-global',
            boxLabel: _('shoplogistic_doc_global'),
            name: 'global',
            checked: parseInt(config.record['global']),
        }];
    },
});
Ext.reg('shoplogistic-window-files-docs-create', shopLogistic.window.CreateFilesDoc);


shopLogistic.window.UpdateFilesDoc = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_files_docs_update'),
        baseParams: {
            action: 'mgr/docs/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateFilesDoc.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateFilesDoc, shopLogistic.window.CreateFilesDoc, {

    getFields: function (config) {
        return shopLogistic.window.CreateFilesDoc.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-files-docs-update', shopLogistic.window.UpdateFilesDoc);