shopLogistic.grid.Motivation = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-motivation';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/bonus/motivation/getlist',
            sort: 'id',
            dir: 'desc',
            // store_id: config.record.object.id
        },
        stateful: true,
        // stateId: config.record.object.id,
    });
    shopLogistic.grid.Motivation.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.Motivation, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'description', 'name', 'image', 'active', 'percent', 'date_to', 'date_from', 'stores', 'stores_ids', 'store_ids', 'global', 'actions', 'gifts', 'gift_ids', 'description_gifts'];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_motivation_create'),
            handler: this.createMotivation,
            scope: this
        }];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_motivation_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_motivation_name'),
                dataIndex: 'name',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_motivation_description'),
                dataIndex: 'description',
                width: 100
            },
            {
                header: _('shoplogistic_motivation_percent'),
                dataIndex: 'percent',
                width: 100
            },
            {
                header: _('shoplogistic_motivation_image'),
                dataIndex: 'image',
                width: 100
            },
            {
                header: _('shoplogistic_motivation_active'),
                dataIndex: 'active',
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_grid_motivation'),
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
                this.updateMotivation(grid, e, row);
            },
        };
    },

    createMotivation: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-motivation-create');
        if (w) {
            w.hide().getEl().remove();
        }

        this.menu.record = {
            store_ids: []
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-motivation-create',
            id: 'shoplogistic-window-motivation-create',
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

    removeMotivation: function () {
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
                action: 'mgr/bonus/motivation/remove',
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

    disableMotivation: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/bonus/motivation/disable',
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

    enableMotivation: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/bonus/motivation/enable',
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

    updateMotivation: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-motivation-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-motivation-update',
            id: 'shoplogistic-window-motivation-updater',
            record: this.menu.record,
            title: this.menu.record['name'],
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
Ext.reg('shoplogistic-grid-motivation', shopLogistic.grid.Motivation);