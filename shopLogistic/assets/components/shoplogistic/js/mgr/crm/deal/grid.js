shopLogistic.grid.DealFields = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-crm-grid-deal';
    }
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/crm/fields/getlist',
            field_type: 1
        },
        listeners: {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateField(grid, e, row);
            }
        },
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            autoFill: true,
            showPreview: true,
            scrollOffset: 0,
            getRowClass: function (rec) {
                return !rec.data.active
                    ? 'shoplogistic-grid-row-disabled'
                    : '';
            }
        },
        paging: true,
        remoteSort: true,
        autoHeight: true,
    });
    shopLogistic.grid.DealFields.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(shopLogistic.grid.DealFields, MODx.grid.Grid, {
    windows: {},

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = shopLogistic.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },

    updateField: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        else if (!this.menu.record) {
            return false;
        }
        var id = this.menu.record.id;

        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/crm/fields/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'shoplogistic-field-deal-window-update',
                            id: Ext.id(),
                            record: r,
                            listeners: {
                                success: {
                                    fn: function () {
                                        this.refresh();
                                    }, scope: this
                                }
                            }
                        });
                        w.reset();
                        w.setValues(r.object);
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },

    getFields: function () {
        return ['id', 'name', 'type', 'code', 'crm_id', 'field', 'properties', 'actions'];
    },

    getColumns: function () {
        return [{
            header: _('shoplogistic_id'),
            dataIndex: 'id',
            sortable: true,
            width: 70
        }, {
            header: _('shoplogistic_crm_field_name'),
            dataIndex: 'name',
            sortable: true,
            width: 200,
        },{
            header: _('shoplogistic_crm_field_code'),
            dataIndex: 'code',
            sortable: true,
            width: 200,
        },{
            header: _('shoplogistic_crm_field_type'),
            dataIndex: 'type',
            sortable: true,
            width: 200,
            renderer: function(value){
                return _('shoplogistic_crm_field_type_'+value)
            }
        }, {
            header: _('shoplogistic_crm_field_crm_id'),
            dataIndex: 'crm_id',
            sortable: false,
            width: 250,
        }, {
            header: _('shoplogistic_crm_field_field'),
            dataIndex: 'field',
            sortable: false,
            width: 250,
        }, {
            header: _('shoplogistic_crm_field_properties'),
            dataIndex: 'properties',
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

    onClick: function (e) {
        var elem = e.getTarget();
        if (elem.nodeName == 'BUTTON') {
            var row = this.getSelectionModel().getSelected();
            if (typeof(row) != 'undefined') {
                var action = elem.getAttribute('action');
                if (action == 'showMenu') {
                    var ri = this.getStore().find('id', row.id);
                    return this._showMenu(this, ri, e);
                }
                else if (typeof this[action] === 'function') {
                    this.menu.record = row.data;
                    return this[action](this, e);
                }
            }
        }
        return this.processEvent('click', e);
    },

    _getSelectedIds: function () {
        var ids = [];
        var selected = this.getSelectionModel().getSelections();

        for (var i in selected) {
            if (!selected.hasOwnProperty(i)) {
                continue;
            }
            ids.push(selected[i]['id']);
        }

        return ids;
    },

    _doSearch: function (tf) {
        this.getStore().baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
    },

    _clearSearch: function () {
        this.getStore().baseParams.query = '';
        this.getBottomToolbar().changePage(1);
    },
});
Ext.reg('shoplogistic-crm-grid-deal', shopLogistic.grid.DealFields);
