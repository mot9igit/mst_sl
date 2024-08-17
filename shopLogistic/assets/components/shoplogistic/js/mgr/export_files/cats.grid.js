shopLogistic.grid.ExportFileCats = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-export-file-cats';
    }
    // console.log(config.record)
    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/export_files_cats/getlist',
            sort: 'id',
            dir: 'desc',
            file_id: config.record.id
        },
        stateful: true
    });
    shopLogistic.grid.ExportFileCats.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.ExportFileCats, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'name', 'description', 'export_id', 'export_parent_id', 'cat_id', 'cat', 'file_id', 'properties', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_export_file_cats_name'),
                width: 50,
                dataIndex: 'name'
            },
            {
                header: _('shoplogistic_export_file_cats_export_id'),
                dataIndex: 'export_id',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_export_file_cats_export_parent_id'),
                dataIndex: 'export_parent_id',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_export_file_cats_cat_id'),
                dataIndex: 'cat',
                sortable: true,
                width: 100,
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

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateCats(grid, e, row);
            },
        };
    },

    updateCats: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-export-file-cats-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-export-file-cats-update',
            id: 'shoplogistic-window-export-file-cats-updater',
            record: this.menu.record,
            title: this.menu.record['store'],
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
Ext.reg('shoplogistic-grid-export-file-cats', shopLogistic.grid.ExportFileCats);