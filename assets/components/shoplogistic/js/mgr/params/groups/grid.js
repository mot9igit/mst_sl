shopLogistic.grid.SettingGroup = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-params-setting-group-grid';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/parameters/groups/getlist',
            sort: 'id',
            dir: 'desc',
            // store_id: config.record.object.id
        },
        stateful: true,
        // stateId: config.record.object.id,
    });
    shopLogistic.grid.SettingGroup.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.SettingGroup, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'label', 'name', 'profile_hidden', 'active', 'rank', 'description', 'actions'];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_settings_group_create'),
            handler: this.createGroup,
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
                header: _('shoplogistic_settings_group_name'),
                dataIndex: 'name',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_settings_group_active'),
                dataIndex: 'active',
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 50,
            },
            {
                header: _('shoplogistic_settings_group_profile_hidden'),
                dataIndex: 'profile_hidden',
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 50,
            },
            {
                header: _('shoplogistic_settings_group_description'),
                dataIndex: 'description',
                width: 100
            },
            {
                header: _('shoplogistic_settings_group_label'),
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
                this.updateGroup(grid, e, row);
            },
        };
    },

    removeGroup: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_settings_group_remove')
                : _('shoplogistic_settings_groups_remove'),
            text: ids.length > 1
                ? _('shoplogistic_settings_group_remove_confirm')
                : _('shoplogistic_settings_groups_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/parameters/groups/remove',
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

    disableGroup: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/parameters/groups/disable',
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

    enableGroup: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/parameters/groups/enable',
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

    createGroup: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-setting-group-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-setting-group-create',
            id: 'shoplogistic-window-setting-group-create',
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

    updateGroup: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-setting-group-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-setting-group-update',
            id: 'shoplogistic-window-setting-group-updater',
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
Ext.reg('shoplogistic-params-setting-group-grid', shopLogistic.grid.SettingGroup);