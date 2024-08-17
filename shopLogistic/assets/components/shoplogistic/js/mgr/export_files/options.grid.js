shopLogistic.grid.ExportFileCatOptions = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-export-file-cat-options';
    }
    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/export_files_options/getlist',
            limit: 10,
            sort: 'id',
            dir: 'desc',
            cat_id: config.record.id,
            category_id: config.record.cat_id
        },
        save_action: 'mgr/export_files_options/updatefromgrid',
        autosave: true,
        save_callback: this.updateRow,
        stateful: true
    });
    shopLogistic.grid.ExportFileCatOptions.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.ExportFileCatOptions, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'name', 'examples', 'to_field', 'description', 'option_id', 'filter', 'cat_id', 'opt', 'ignore', 'createdon', 'updatedon', 'updatedby', 'properties', 'actions'];
    },

    getColumns: function (config) {
        // console.log(config)
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                sortable: true,
                width: 20
            },
            {
                header: _('shoplogistic_export_file_cat_options_to_field'),
                width: 50,
                dataIndex: 'to_field',
                editor: {xtype: 'textfield'}
            },
            {
                header: _('shoplogistic_export_file_cat_options_name'),
                width: 50,
                dataIndex: 'name',
                editor: {xtype: 'textfield'}
            },
            {
                header: _('shoplogistic_export_file_cat_options_option_id'),
                dataIndex: 'opt',
                sortable: true,
                width: 100,
                editor: {
                    xtype: 'shoplogistic-combo-options',
                    hiddenName: 'option_id',
                    baseParams: {
                        action: 'mgr/system/options/getlist',
                        category: config.baseParams.category_id,
                        combo: 1
                    }
                }
            },
            {
                header: _('shoplogistic_export_file_cat_options_ignore'),
                dataIndex: 'ignore',
                sortable: true,
                width: 100,
                editor: {xtype: 'combo-boolean', renderer: 'boolean'}
            },
            {
                header: _('ms2_actions'),
                dataIndex: 'actions',
                id: 'actions',
                width: 50,
                renderer: shopLogistic.utils.renderActions
            }
        ];
    },

    getTopBar: function () {
        return [];
    },

    updateRow: function () {
        this.refresh();
    },

    updateOptions: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-export-file-options-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-export-file-options-update',
            id: 'shoplogistic-window-export-file-options-update',
            record: this.menu.record,
            title: this.menu.record['name'],
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.fp.getForm().reset();
        w.fp.getForm().setValues(this.menu.record);
        w.show(e.target);
    }
});
Ext.reg('shoplogistic-grid-export-file-cat-options', shopLogistic.grid.ExportFileCatOptions);