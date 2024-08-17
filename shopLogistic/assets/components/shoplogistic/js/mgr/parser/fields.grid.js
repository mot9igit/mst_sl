shopLogistic.grid.ParserConfigFields = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-parser-config-fields';
    }
    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/parser/fields/getlist',
            limit: 10,
            sort: 'id',
            dir: 'ASC',
            config_id: config.record.id,
            field_object: config.type
        },
        stateful: true
    });

    shopLogistic.grid.ParserConfigFields.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.ParserConfigFields, shopLogistic.grid.Default, {

    getFields: function () {
        return [
            'id',
            'name',
            'description',
            'config_id',
            'field_filters',
            'type',
            'element_name',
            'selector',
            'field_object',
            'field_type',
            'createdon',
            'updatedon',
            'updatedby',
            'properties',
            'index_search',
            'index',
            'subelement',
            'subindex',
            'this',
            'actions'
        ];
    },

    getColumns: function (config) {
        // console.log(config)
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_parser_config_fields_name'),
                width: 50,
                dataIndex: 'name'
            },
            {
                header: _('shoplogistic_parser_config_fields_selector'),
                width: 50,
                dataIndex: 'selector'
            },
            {
                header: _('shoplogistic_parser_config_fields_this'),
                width: 50,
                dataIndex: 'this',
                renderer: shopLogistic.utils.renderBoolean
            },
            {
                header: _('shoplogistic_parser_config_fields_type'),
                width: 50,
                dataIndex: 'type',
                renderer: shopLogistic.utils.renderParserFieldSource
            },
            {
                header: _('shoplogistic_parser_config_fields_element_name'),
                width: 50,
                dataIndex: 'element_name'
            },
            {
                header: _('shoplogistic_parser_config_fields_field_type'),
                width: 50,
                dataIndex: 'field_type',
                renderer: shopLogistic.utils.renderParserFieldType
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
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_parser_config_fields_create'),
            handler: this.createField,
            scope: this
        }];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateField(grid, e, row);
            },
        };
    },

    createField: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-parser-config-fields-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-parser-config-fields-create',
            id: 'shoplogistic-window-parser-config-fields-create',
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
            config_id: this.config.record.id,
            field_type: 1,
            field_object: this.config.type
        });
        w.show(e.target);
    },

    updateField: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-parser-config-fields-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-parser-config-fields-update',
            id: 'shoplogistic-window-parser-config-fields-updater',
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
    },

    removeField: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_parser_fields_remove')
                : _('shoplogistic_parser_field_remove'),
            text: ids.length > 1
                ? _('shoplogistic_parser_fields_remove_confirm')
                : _('shoplogistic_parser_field_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/parser/fields/remove',
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
Ext.reg('shoplogistic-grid-parser-config-fields', shopLogistic.grid.ParserConfigFields);