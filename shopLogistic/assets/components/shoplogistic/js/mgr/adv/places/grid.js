shopLogistic.grid.Place = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-place';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/adv/places/getlist',
            sort: 'id',
            dir: 'desc',
            // store_id: config.record.object.id
        },
        stateful: true,
        // stateId: config.record.object.id,
    });
    shopLogistic.grid.Place.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.Place, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'pattern_id', 'name', 'active', 'key', 'description', 'actions'];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_place_create'),
            handler: this.createPlace,
            scope: this
        }];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_place_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_place_pattern_id'),
                width: 20,
                dataIndex: 'pattern_id'
            },
            {
                header: _('shoplogistic_place_name'),
                dataIndex: 'name',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_place_active'),
                dataIndex: 'active',
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 50,
            },
            {
                header: _('shoplogistic_place_description'),
                dataIndex: 'description',
                width: 100
            },
            {
                header: _('shoplogistic_place_key'),
                dataIndex: 'key',
                width: 50
            },
            {
                header: _('shoplogistic_place_actions'),
                dataIndex: 'actions',
                renderer: shopLogistic.utils.renderActions,
                sortable: false,
                width: 150,
                id: 'actions'
            }
        ];
    },


    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updatePlace(grid, e, row);
            },
        };
    },

    removePlace: function () {
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
                action: 'mgr/adv/places/remove',
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

    disablePlace: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/adv/places/disable',
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

    enablePlace: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/adv/places/enable',
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

    createPlace: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-place-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-place-create',
            id: 'shoplogistic-window-place-create',
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

    updatePlace: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-place-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-place-update',
            id: 'shoplogistic-window-place-updater',
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
Ext.reg('shoplogistic-grid-place', shopLogistic.grid.Place);