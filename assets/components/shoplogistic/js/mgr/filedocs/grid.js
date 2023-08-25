shopLogistic.grid.FileDocs = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-files-docs';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/docs/getlist',
            sort: 'id',
            dir: 'desc'
        },
        stateful: true
    });
    shopLogistic.grid.FileDocs.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.FileDocs, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'store_id', 'store_ids', 'name', 'file', 'global', 'status', 'status_name', 'color', 'date', 'description', 'createdon', 'createdby', 'updatedon', 'updatedby', 'properties', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_stores_docs_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_doc_name'),
                width: 50,
                dataIndex: 'name'
            },
            {
                header: _('shoplogistic_doc_date'),
                dataIndex: 'date',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_doc_status'),
                dataIndex: 'status_name',
                sortable: true,
                width: 100,
                renderer: function (val, cell, row) {
                    return shopLogistic.utils.renderBadge(val, cell, row);
                }
            },
            {
                header: _('shoplogistic_doc_file'),
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_doc_create'),
            handler: this.createDoc,
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

    createDoc: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-files-docs-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-files-docs-create',
            id: 'shoplogistic-window-files-docs-create',
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

    updateDoc: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-files-docs-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-files-docs-update',
            id: 'shoplogistic-window-files-docs-updater',
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
Ext.reg('shoplogistic-grid-files-docs', shopLogistic.grid.FileDocs);