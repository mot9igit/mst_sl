shopLogistic.window.CreateOptionsExportFile = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_export_file_options_create'),
        width: 600,
        baseParams: {
            action: 'mgr/export_files_options/create',
        },
    });
    shopLogistic.window.CreateOptionsExportFile.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateOptionsExportFile, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_export_file_cat_options_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_export_file_cat_options_filter'),
            name: 'filter',
            id: config.id + '-filter',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_export_file_cat_options_to_field'),
            name: 'to_field',
            id: config.id + '-to_field',
            anchor: '99%'
        }, {
            xtype: 'shoplogistic-combo-options',
            fieldLabel: _('shoplogistic_export_file_cat_options_option_id'),
            name: 'option_id',
            hiddenName: "option_id",
            id: config.id + '-option_id',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_export_file_cat_options_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_export_file_cat_options_examples'),
            name: 'examples',
            id: config.id + '-examples',
            anchor: '99%'
        }];
    },
});
Ext.reg('shoplogistic-window-export-file-options-create', shopLogistic.window.CreateOptionsExportFile);


shopLogistic.window.UpdateOptionsExportFile = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/export_files_options/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateOptionsExportFile.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateOptionsExportFile, shopLogistic.window.CreateOptionsExportFile, {

    getFields: function (config) {
        return shopLogistic.window.CreateOptionsExportFile.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-export-file-options-update', shopLogistic.window.UpdateOptionsExportFile);