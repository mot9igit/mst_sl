shopLogistic.grid.ParserdataCats = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-parserdata-cats';
    }
    // console.log(config.record)
    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/parserdata/categories/getlist',
            sort: 'id',
            dir: 'desc',
            service_id: config.record.id
        },
        stateful: true
    });
    shopLogistic.grid.ParserdataCats.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.ParserdataCats, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'name', 'description', 'export_name', 'export_parents', 'cat_id', 'cat', 'service_id', 'check', 'properties', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_parserdata_cats_name'),
                width: 50,
                dataIndex: 'name'
            },
            {
                header: _('shoplogistic_parserdata_cats_export_parents'),
                dataIndex: 'export_parents',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_parserdata_cats_cat_id'),
                dataIndex: 'cat',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_parserdata_cats_check'),
                dataIndex: 'check',
                sortable: true,
                width: 100,
                renderer: shopLogistic.utils.renderBoolean
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
                this.updateCat(grid, e, row);
            },
        };
    },

    updateCat: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-parserdata-cats-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-parserdata-cats-update',
            id: 'shoplogistic-window-parserdata-cats-updater',
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
Ext.reg('shoplogistic-grid-parserdata-cats', shopLogistic.grid.ParserdataCats);