shopLogistic.grid.ExportFiles = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-export-files';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/export_files/getlist',
            sort: 'id',
            dir: 'desc'
        },
        stateful: true
    });
    shopLogistic.grid.ExportFiles.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.ExportFiles, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'store_id', 'name', 'categories', 'products', 'file', 'global', 'status', 'status_name', 'color', 'date', 'description', 'createdon', 'createdby', 'updatedon', 'updatedby', 'properties', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_export_files_name'),
                width: 50,
                dataIndex: 'name'
            },
            {
                header: _('shoplogistic_export_files_description'),
                dataIndex: 'description',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_export_files_categories'),
                dataIndex: 'categories',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_export_files_products'),
                dataIndex: 'products',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_export_files_status'),
                dataIndex: 'status_name',
                sortable: true,
                width: 100,
                renderer: function (val, cell, row) {
                    return shopLogistic.utils.renderBadge(val, cell, row);
                }
            },
            {
                header: _('shoplogistic_export_files_file'),
                dataIndex: 'file',
                width: 50
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
        return [{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_export_files_create'),
            handler: this.createFile,
            scope: this
        }];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateDoc(grid, e, row);
            },
        };
    },

    createFile: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-export-files-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-export-files-create',
            id: 'shoplogistic-window-export-files-create',
            record: this.menu.record,
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.fp.getForm().reset();
        w.fp.getForm().setValues({});
        w.show(e.target);
    },

    updateFile: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-export-files-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-export-files-update',
            id: 'shoplogistic-window-export-files-updater',
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
Ext.reg('shoplogistic-grid-export-files', shopLogistic.grid.ExportFiles);