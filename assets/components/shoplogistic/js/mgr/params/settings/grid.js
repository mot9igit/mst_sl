shopLogistic.grid.Setting = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-params-setting-grid';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/parameters/params/getlist',
            sort: 'id',
            dir: 'desc',
            // store_id: config.record.object.id
        },
        stateful: true,
        // stateId: config.record.object.id,
    });
    shopLogistic.grid.Setting.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.Setting, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'key', 'group', 'label', 'type', 'default', 'name', 'profile_hidden', 'active', 'rank', 'properties', 'description', 'actions'];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_setting_create'),
            handler: this.createSetting,
            scope: this
        }];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_setting_key'),
                dataIndex: 'key',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_setting_name'),
                dataIndex: 'name',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_setting_type'),
                dataIndex: 'type',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_setting_active'),
                dataIndex: 'active',
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 50,
            },
            {
                header: _('shoplogistic_setting_profile_hidden'),
                dataIndex: 'profile_hidden',
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 50,
            },
            {
                header: _('shoplogistic_setting_description'),
                dataIndex: 'description',
                width: 100
            },
            {
                header: _('shoplogistic_setting_label'),
                dataIndex: 'label',
                width: 50
            },
            {
                header: _('shoplogistic_col_actions'),
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
                this.updateSetting(grid, e, row);
            },
        };
    },

    removeSetting: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_setting_remove')
                : _('shoplogistic_settings_remove'),
            text: ids.length > 1
                ? _('shoplogistic_setting_remove_confirm')
                : _('shoplogistic_settings_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/parameters/params/remove',
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

    disableSetting: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/parameters/params/disable',
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

    enableSetting: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/parameters/params/enable',
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

    createSetting: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-setting-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-setting-create',
            id: 'shoplogistic-window-setting-create',
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

    updateSetting: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-setting-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-setting-update',
            id: 'shoplogistic-window-setting-updater',
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
Ext.reg('shoplogistic-params-setting-grid', shopLogistic.grid.Setting);