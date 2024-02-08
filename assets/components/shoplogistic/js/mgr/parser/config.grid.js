shopLogistic.grid.ParserConfigs = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-parser-configs';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/parser/configs/getlist',
            sort: 'id',
            dir: 'desc'
        },
        stateful: true
    });
    shopLogistic.grid.ParserConfigs.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.ParserConfigs, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'name', 'unique', 'key_product_field', 'description', 'base_url', 'categories_base', 'categories_base_inner', 'products_base', 'products_base_inner', 'pagination', 'pagination_selector', 'pagination_filters', 'properties', 'actions'];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_parser_config_name'),
                width: 50,
                dataIndex: 'name'
            },
            {
                header: _('shoplogistic_parser_config_base_url'),
                dataIndex: 'base_url',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_parser_config_categories_base'),
                dataIndex: 'categories_base',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_parser_config_categories_base_inner'),
                dataIndex: 'categories_base_inner',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_parser_config_products_base'),
                dataIndex: 'products_base',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_parser_config_products_base_inner'),
                dataIndex: 'products_base_inner',
                sortable: true,
                width: 100,
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
        return [{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_parser_config_create'),
            handler: this.createConfig,
            scope: this
        }];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateConfig(grid, e, row);
            },
        };
    },

    createConfig: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-parser-configs-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-parser-configs-create',
            id: 'shoplogistic-window-parser-configs-create',
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

    updateConfig: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-parser-configs-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-parser-configs-update',
            id: 'shoplogistic-window-parser-configs-updater',
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

    removeConfig: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_parser_configs_remove')
                : _('shoplogistic_parser_config_remove'),
            text: ids.length > 1
                ? _('shoplogistic_parser_configs_remove_confirm')
                : _('shoplogistic_parser_config_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/parser/configs/remove',
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
Ext.reg('shoplogistic-grid-parser-configs', shopLogistic.grid.ParserConfigs);