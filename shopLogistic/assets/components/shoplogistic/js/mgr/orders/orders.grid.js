shopLogistic.grid.Orders = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-orders';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/orders/getlist',
            sort: 'id',
            dir: 'desc',
        },
        multi_select: true,
        changed: false,
        stateful: true,
        stateId: config.id,
    });
    shopLogistic.grid.Orders.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.Orders, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id','user_id','customer','num','status','color','status_name','cart_cost','delivery_cost','payment','delivery','store_id','warehouse_id','warehouse_name','store_name','actions'];
    },

    getColumns: function () {
        return [{
            header: _('shoplogistic_id'),
            dataIndex: 'id',
            sortable: true,
            width: 70
        }, {
            header: _('shoplogistic_order_num'),
            dataIndex: 'num',
            sortable: true,
            width: 200,
        }, {
            header: _('shoplogistic_order_status'),
            dataIndex: 'status_name',
            sortable: true,
            width: 200,
            renderer: function (val, cell, row) {
                return shopLogistic.utils.renderBadge(val, cell, row);
            }
        }, {
            header: _('shoplogistic_order_customer'),
            dataIndex: 'customer',
            sortable: true,
            width: 200,
            renderer: function (val, cell, row) {
                return shopLogistic.utils.userLink(val, row.data['user_id'], true);
            }
        },{
            header: _('shoplogistic_order_cart_cost'),
            dataIndex: 'cart_cost',
            sortable: true,
            width: 200,
        },{
            header: _('shoplogistic_order_delivery_cost'),
            dataIndex: 'delivery_cost',
            sortable: true,
            width: 200
        }, {
            header: _('shoplogistic_order_payment'),
            dataIndex: 'payment',
            sortable: false,
            width: 250,
        }, {
            header: _('shoplogistic_order_delivery'),
            dataIndex: 'delivery',
            sortable: false,
            width: 250,
        }, {
            header: _('shoplogistic_order_store'),
            dataIndex: 'store_name',
            sortable: false,
            width: 250,
        }, {
            header: _('shoplogistic_order_warehouse'),
            dataIndex: 'warehouse_name',
            sortable: false,
            width: 250,
        }, {
            header: _('shoplogistic_grid_actions'),
            dataIndex: 'actions',
            renderer: shopLogistic.utils.renderActions,
            sortable: false,
            width: 100,
            id: 'actions'
        }];
    },

    getTopBar: function () {
        return [];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateOrder(grid, e, row);
            },
            afterrender: function (grid) {
                var params = shopLogistic.utils.Hash.get();
                var order = params['order'] || '';
                if (order) {
                    this.updateOrder(grid, Ext.EventObject, {data: {id: order}});
                }
            },
        };
    },

    orderAction: function (method) {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/orders/multiple',
                method: method,
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        //noinspection JSUnresolvedFunction
                        this.refresh();
                    }, scope: this
                },
                failure: {
                    fn: function (response) {
                        MODx.msg.alert(_('error'), response.message);
                    }, scope: this
                },
            }
        })
    },

    updateOrder: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        var id = this.menu.record.id;

        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/orders/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = Ext.getCmp('shoplogistic-window-order-update');
                        if (w) {
                            w.close();
                        }

                        w = MODx.load({
                            xtype: 'shoplogistic-window-order-update',
                            id: 'shoplogistic-window-order-update',
                            record: r.object,
                            listeners: {
                                success: {
                                    fn: function () {
                                        this.refresh();
                                    }, scope: this
                                },
                                hide: {
                                    fn: function () {
                                        shopLogistic.utils.Hash.remove('order');
                                        if (shopLogistic.grid.Orders.changed === true) {
                                            Ext.getCmp('shoplogistic-grid-orders').getStore().reload();
                                            shopLogistic.grid.Orders.changed = false;
                                        }
                                    }
                                },
                                afterrender: function () {
                                    shopLogistic.utils.Hash.add('order', r.object['id']);
                                }
                            }
                        });
                        w.fp.getForm().reset();
                        w.fp.getForm().setValues(r.object);
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },

    removeOrder: function () {
        var ids = this._getSelectedIds();

        Ext.MessageBox.confirm(
            _('shoplogistic_menu_remove_title'),
            ids.length > 1
                ? _('shoplogistic_menu_remove_multiple_confirm')
                : _('shoplogistic_menu_remove_confirm'),
            function (val) {
                if (val == 'yes') {
                    this.orderAction('remove');
                }
            },
            this
        );
    },

    _renderCost: function (val, idx, rec) {
        return rec.data['type'] != undefined && rec.data['type'] == 1
            ? '-' + val
            : val;
    },

});
Ext.reg('shoplogistic-grid-orders', shopLogistic.grid.Orders);