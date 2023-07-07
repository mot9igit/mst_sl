shopLogistic.grid.VendorBrands = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-vendorbrands';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/vendorbrands/getlist',
            sort: 'id',
            dir: 'asc',
            store_id: config.record.object.id
        },
        stateful: true,
        stateId: config.record.object.id,
    });
    shopLogistic.grid.VendorBrands.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.VendorBrands, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'store_id', 'brand_id', 'store', 'vendor', 'description', 'actions'];
    },

    getColumns: function () {
        return [
            {header: _('shoplogistic_vendorbrands_id'), dataIndex: 'id', width: 20},
            {header: _('shoplogistic_vendorbrands_store'), width: 50, dataIndex: 'store'},
            {header: _('shoplogistic_vendorbrands_brand'), width: 50, dataIndex: 'vendor'},
            {header: _('shoplogistic_vendorbrands_description'), dataIndex: 'description', width: 50},
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_vendorbrands_create'),
            handler: this.createVendorBrands,
            scope: this
        }];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateVendorBrands(grid, e, row);
            },
        };
    },

    createVendorBrands: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-vendorbrands-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-vendorbrands-create',
            id: 'shoplogistic-window-vendorbrands-create',
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
        w.fp.getForm().setValues({
            store_id: this.config.record.object.id
        });
        w.show(e.target);
    },

    updateVendorBrands: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-vendorbrands-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-vendorbrands-update',
            id: 'shoplogistic-window-vendorbrands-updater',
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
    },

    removeVendorBrands: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_vendorbrands_remove')
                : _('shoplogistic_vendorbrands_remove'),
            text: ids.length > 1
                ? _('shoplogistic_vendorbrands_remove_confirm')
                : _('shoplogistic_vendorbrands_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/vendorbrands/remove',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        return true;
    },
});
Ext.reg('shoplogistic-grid-vendorbrands', shopLogistic.grid.VendorBrands);