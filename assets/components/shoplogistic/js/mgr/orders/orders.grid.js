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
        return shopLogistic.config['order_grid_fields'];
    },

    getColumns: function () {
        var all = {
            id: {width: 35},
            customer: {width: 100, renderer: function (val, cell, row) {
                    return shopLogistic.utils.userLink(val, row.data['user_id'], true);
                }},
            num: {width: 50},
            receiver: {width: 100},
            createdon: {width: 75, renderer: shopLogistic.utils.formatDate},
            updatedon: {width: 75, renderer: shopLogistic.utils.formatDate},
            cost: {width: 50, renderer: this._renderCost},
            cart_cost: {width: 50},
            delivery_cost: {width: 75},
            weight: {width: 50},
            status: {width: 75, renderer: shopLogistic.utils.renderBadge},
            delivery: {width: 75},
            payment: {width: 75},
            //address: {width: 50},
            context: {width: 50},
            actions: {width: 75, id: 'actions', renderer: shopLogistic.utils.renderActions, sortable: false},
        };

        //var fields = this.getFields();
        var fields = 'id,num,customer,status,cost,weight,delivery,payment,createdon,updatedon,comment';
        var columns = [];
        for (var i = 0; i < fields.length; i++) {
            var field = fields[i];
            if (all[field]) {
                Ext.applyIf(all[field], {
                    header: _('ms2_' + field),
                    dataIndex: field,
                    sortable: true,
                });
                columns.push(all[field]);
            }
        }

        return columns;
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