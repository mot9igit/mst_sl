shopLogistic.grid.StoresDocs = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-stores-docs';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/stores_docs/getlist',
            sort: 'id',
            dir: 'desc',
            store_id: config.record.object.id
        },
        stateful: true,
        stateId: config.record.object.id,
    });
    shopLogistic.grid.StoresDocs.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.StoresDocs, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'store_id', 'guid', 'doc_number', 'date', 'description', 'createdon', 'properties', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_stores_docs_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_stores_docs_guid'),
                width: 50,
                dataIndex: 'guid'
            },
            {
                header: _('shoplogistic_stores_docs_createdon'),
                dataIndex: 'date',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_stores_docs_doc_number'),
                dataIndex: 'doc_number',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_stores_docs_description'),
                dataIndex: 'description',
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

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateStoresDoc(grid, e, row);
            },
        };
    },

    updateStoresDoc: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-stores-docs-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-stores-docs-update',
            id: 'shoplogistic-window-stores-docs-updater',
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
Ext.reg('shoplogistic-grid-stores-docs', shopLogistic.grid.StoresDocs);