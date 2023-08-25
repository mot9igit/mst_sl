shopLogistic.window.CreateExportFile = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_files_docs_create'),
        width: 600,
        baseParams: {
            action: 'mgr/export_files/create',
        },
    });
    shopLogistic.window.CreateExportFile.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateExportFile, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'shoplogistic-combo-store',
            fieldLabel: _('shoplogistic_export_files_store_id'),
            name: 'store_id',
            anchor: '99%',
            id: config.id + '-store_id'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_export_files_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'shoplogistic-combo-exportfilestatus',
            fieldLabel: _('shoplogistic_export_files_status'),
            name: 'status',
            anchor: '99%',
            id: config.id + '-status'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_export_files_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_export_files_file'),
            name: 'file',
            id: config.id + '-file',
            anchor: '99%'
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_export_files_categories'),
            name: 'categories',
            id: config.id + '-categories',
            anchor: '99%'
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_export_files_products'),
            name: 'products',
            id: config.id + '-products',
            anchor: '99%'
        }];
    },
});
Ext.reg('shoplogistic-window-export-files-create', shopLogistic.window.CreateExportFile);


shopLogistic.window.UpdateExportFile = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_files_docs_update'),
        baseParams: {
            action: 'mgr/export_files/update',
            width: 900,
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateExportFile.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateExportFile, shopLogistic.window.CreateExportFile, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_files_docs_update'),
                layout: 'form',
                items: shopLogistic.window.CreateExportFile.prototype.getFields.call(this, config),
            }, {
                title: _('shoplogistic_export_file_cats'),
                items: [{
                    xtype: 'shoplogistic-grid-export-file-cats',
                    record: config.record,
                }]
            }]
        }]
    }

});
Ext.reg('shoplogistic-window-export-files-update', shopLogistic.window.UpdateExportFile);