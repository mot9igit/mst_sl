shopLogistic.grid.Gift = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-gift';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/bonus/gift/getlist',
            sort: 'id',
            dir: 'desc',
            // store_id: config.record.object.id
        },
        stateful: true,
        // stateId: config.record.object.id,
    });
    shopLogistic.grid.Gift.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.Gift, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'description', 'name', 'image', 'active'];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_gift_create'),
            handler: this.createGift,
            scope: this
        }];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_gift_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_gift_name'),
                dataIndex: 'name',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_gift_description'),
                dataIndex: 'description',
                width: 100
            },
            {
                header: _('shoplogistic_gift_image'),
                dataIndex: 'image',
                width: 100
            },{
                header: _('shoplogistic_gift_active'),
                dataIndex: 'active',
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_grid_gift'),
                dataIndex: 'actions',
                renderer: shopLogistic.utils.renderActions,
                sortable: false,
                width: 100,
                id: 'actions'
            }
        ];
    },


    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateGift(grid, e, row);
            },
        };
    },

    createGift: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-gift-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-gift-create',
            id: 'shoplogistic-window-gift-create',
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

    removeGift: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_actions_remove')
                : _('shoplogistic_action_remove'),
            text: ids.length > 1
                ? _('shoplogistic_actions_remove_confirm')
                : _('shoplogistic_action_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/bonus/gift/remove',
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

    disableGift: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/bonus/gift/disable',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        })
    },

    enableGift: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/bonus/gift/enable',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        })
    },

    updateGift: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-gift-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-gift-update',
            id: 'shoplogistic-window-gift-updater',
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
Ext.reg('shoplogistic-grid-gift', shopLogistic.grid.Gift);