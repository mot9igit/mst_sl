shopLogistic.window.CreateCatsExportFile = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_export_file_cats_create'),
        width: 900,
        baseParams: {
            action: 'mgr/export_files_cats/create'
        },
    });
    shopLogistic.window.CreateCatsExportFile.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateCatsExportFile, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        },{
            xtype: 'hidden',
            name: 'file_id',
            id: config.id + '-file_id',
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_export_file_cats_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_export_file_cats_export_id'),
            name: 'export_id',
            id: config.id + '-export_id',
            anchor: '99%'
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_export_file_cats_export_parent_id'),
            name: 'export_parent_id',
            id: config.id + '-export_parent_id',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_export_file_status_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        },{
            xtype: 'shoplogistic-combo-category',
            boxLabel: _('shoplogistic_export_file_status_cat_id'),
            name: 'cat_id',
            hiddenName: 'cat_id',
            id: config.id + '-cat_id'
        }];
    },
});
Ext.reg('shoplogistic-window-export-file-cats-create', shopLogistic.window.CreateCatsExportFile);


shopLogistic.window.UpdateCatsExportFile = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_export_file_cats_update'),
        width: 900,
        maxHeight: 400,
        baseParams: {
            action: 'mgr/export_files_cats/update'
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateCatsExportFile.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateCatsExportFile, shopLogistic.window.CreateCatsExportFile, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            autoHeight: true,
            deferredRender: false,
            forceLayout: true,
            width: '98%',
            items: [{
                title: _('shoplogistic_export_file_cats_update'),
                layout: 'form',
                items: shopLogistic.window.CreateCatsExportFile.prototype.getFields.call(this, config)
            }, {
                title: _('shoplogistic_export_file_cat_options'),
                items: [{
                    html: 'Будьте внимательны при заполнений соответствий. Если не отмечена галочка "Игнорировать" и не стоит соответствие, опция будет создана.',
                    cls: 'panel-desc'
                },{
                    xtype: 'shoplogistic-grid-export-file-cat-options',
                    record: config.record,
                }]
            }],
            listeners: {
                'tabchange': {fn: function(panel) {
                    panel.doLayout();
                    },
                    scope: this
                }
            }
        }]
    }

});
Ext.reg('shoplogistic-window-export-file-cats-update', shopLogistic.window.UpdateCatsExportFile);