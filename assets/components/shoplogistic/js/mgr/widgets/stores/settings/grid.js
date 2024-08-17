shopLogistic.grid.StoreSetting = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-params-store-setting-grid';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/store/parameters/getlist',
            sort: 'id',
            dir: 'desc',
            store_id: config.record.object.id
        },
        stateful: true,
        // stateId: config.record.object.id,
    });
    shopLogistic.grid.StoreSetting.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.StoreSetting, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'key', 'store_id','group', 'label', 'type', 'value', 'name', 'profile_hidden', 'active', 'description', 'actions'];
    },

    getTopBar: function () {
        return [{
            xtype: 'shoplogistic-field-search',
            width: 250,
            listeners: {
                search: {
                    fn: function (field) {
                        this._doSearch(field);
                    }, scope: this
                },
                clear: {
                    fn: function (field) {
                        field.setValue('');
                        this._clearSearch();
                    }, scope: this
                },
            }
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
                header: _('shoplogistic_setting_value'),
                dataIndex: 'value',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_setting_type'),
                dataIndex: 'type',
                sortable: true,
                renderer: shopLogistic.utils.renderFieldParamType,
                width: 100,
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

    updateSetting: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        var w = Ext.getCmp('shoplogistic-window-store-setting-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-store-setting-update',
            id: 'shoplogistic-window-store-setting-updater',
            record: this.menu.record,
            title: this.menu.record['name'],
            store_id: this.config.baseParams.store_id,
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
Ext.reg('shoplogistic-params-store-setting-grid', shopLogistic.grid.StoreSetting);