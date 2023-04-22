shopLogistic.grid.StoresDocsProducts = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-stores-docs-products';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/stores_docs_products/getlist',
            sort: 'id',
            dir: 'asc',
            doc_id: config.record.id
        },
        stateful: true,
        stateId: config.record.id,
    });
    shopLogistic.grid.StoresDocsProducts.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.StoresDocsProducts, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'doc_id', 'remain_id', 'type', 'article', 'count', 'price', 'description', 'createdon', 'properties', 'product_article', 'product_guid', 'product_name'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_stores_docs_products_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_stores_docs_products_article'),
                width: 50,
                dataIndex: 'product_article'
            },
            {
                header: _('shoplogistic_stores_docs_products_remain_id'),
                width: 50,
                dataIndex: 'product_name'
            },
            {
                header: _('shoplogistic_stores_docs_products_type'),
                width: 50,
                dataIndex: 'type'
            },
            {
                header: _('shoplogistic_stores_docs_products_count'),
                dataIndex: 'count',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_stores_docs_products_price'),
                dataIndex: 'price',
                sortable: true,
                width: 100,
            }
        ];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateStoresDocProd(grid, e, row);
            },
        };
    },

    updateStoresDocProd: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-grid-stores-docs-products-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'sshoplogistic-grid-stores-docs-products-update',
            id: 'shoplogistic-grid-stores-docs-products-updater',
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
Ext.reg('shoplogistic-grid-stores-docs-products', shopLogistic.grid.StoresDocsProducts);